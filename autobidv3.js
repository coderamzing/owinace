require('dotenv').config();

const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const { Transform } = require('stream');
const axios = require('axios');
const pino = require('pino');
const { chromium } = require('playwright');
const ProxyChain = require('proxy-chain');

// --- criticalAlert (inlined) ---
function createCriticalAlert({
    stateFile = path.join(__dirname, 'logs', 'critical-alert-state.json'),
    defaultCooldownMs = 30 * 60 * 1000,
    defaultMaxPerHour = 10,
    discordDescLimit = 1900,
} = {}) {
    function loadState() {
        try {
            if (fs.existsSync(stateFile)) {
                return JSON.parse(fs.readFileSync(stateFile, 'utf8'));
            }
        } catch {
            // corrupt state — start fresh
        }
        return { fingerprints: {}, hourWindow: { startedAt: 0, count: 0 } };
    }

    function saveState(state) {
        const dir = path.dirname(stateFile);
        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
        fs.writeFileSync(stateFile, JSON.stringify(state, null, 2));
    }

    function normalizeMessage(message) {
        return String(message || '')
            .replace(/data-ev-job-uid="[^"]+"/g, 'data-ev-job-uid="*"')
            .replace(/\b\d{10,}\b/g, '*')
            .split('\n')[0]
            .trim()
            .slice(0, 200);
    }

    function fingerprint(context = {}, message = '') {
        if (context.code) {
            return String(context.code);
        }

        const errorText =
            context.error ||
            context.err ||
            context.message ||
            message ||
            'unknown';

        const fn = context.fn || context.function || '';
        return [normalizeMessage(errorText), fn].filter(Boolean).join('|');
    }

    function cooldownMs(context = {}) {
        if (context.cooldownMs) return context.cooldownMs;
        const env = Number(process.env.CRITICAL_ALERT_COOLDOWN_MS);
        return Number.isFinite(env) && env > 0 ? env : defaultCooldownMs;
    }

    function maxPerHour() {
        const env = Number(process.env.CRITICAL_ALERT_MAX_PER_HOUR);
        return Number.isFinite(env) && env > 0 ? env : defaultMaxPerHour;
    }

    function canSendGlobally(state) {
        const now = Date.now();
        const windowMs = 60 * 60 * 1000;

        if (
            !state.hourWindow?.startedAt ||
            now - state.hourWindow.startedAt >= windowMs
        ) {
            state.hourWindow = { startedAt: now, count: 0 };
        }

        return state.hourWindow.count < maxPerHour();
    }

    function truncate(text, limit = discordDescLimit) {
        const value = String(text || '');
        return value.length <= limit
            ? value
            : `${value.slice(0, limit - 3)}...`;
    }

    function buildDiscordPayload(context, message, fp, suppressedCount) {
        const title = context.code
            ? `Critical: ${context.code}`
            : `Critical: ${normalizeMessage(context.error || message || 'upbot failure')}`;

        const lines = [
            `**Message:** ${message || context.error || 'Critical error'}`,
            context.error ? `**Error:** ${context.error}` : null,
            context.stack
                ? `**Stack:**\n\`\`\`\n${truncate(context.stack, 1200)}\n\`\`\``
                : null,
            `**Host:** ${process.env.HOSTNAME || os.hostname()}`,
            `**Fingerprint:** \`${fp}\``,
            suppressedCount > 0
                ? `**Suppressed repeats:** ${suppressedCount} since last alert`
                : null,
        ].filter(Boolean);

        return {
            embeds: [
                {
                    title: truncate(title, 250),
                    description: truncate(lines.join('\n\n')),
                    color: 0xe74c3c,
                    timestamp: new Date().toISOString(),
                },
            ],
        };
    }

    async function sendDiscord(payload) {
        const webhookUrl = process.env.DISCORD_CRITICAL_WEBHOOK_URL;
        if (!webhookUrl) {
            console.error(
                '[criticalAlert] DISCORD_CRITICAL_WEBHOOK_URL is not set'
            );
            return false;
        }

        await axios.post(webhookUrl, payload, {
            headers: { 'Content-Type': 'application/json' },
            timeout: 10000,
        });

        return true;
    }

    async function notify(context = {}, message = '') {
        const fp = fingerprint(context, message);
        const state = loadState();
        const now = Date.now();
        const entry = state.fingerprints[fp] || {
            lastNotifiedAt: 0,
            suppressedCount: 0,
        };

        entry.suppressedCount = (entry.suppressedCount || 0) + 1;

        const elapsed = now - (entry.lastNotifiedAt || 0);
        const withinCooldown =
            entry.lastNotifiedAt > 0 && elapsed < cooldownMs(context);

        if (withinCooldown) {
            state.fingerprints[fp] = entry;
            saveState(state);
            return {
                notified: false,
                suppressed: true,
                reason: 'cooldown',
                fingerprint: fp,
            };
        }

        if (!canSendGlobally(state)) {
            state.fingerprints[fp] = entry;
            saveState(state);
            return {
                notified: false,
                suppressed: true,
                reason: 'hourly_limit',
                fingerprint: fp,
            };
        }

        const suppressedCount = Math.max(0, entry.suppressedCount - 1);
        const payload = buildDiscordPayload(
            context,
            message,
            fp,
            suppressedCount
        );

        try {
            await sendDiscord(payload);
            entry.lastNotifiedAt = now;
            entry.suppressedCount = 0;
            state.fingerprints[fp] = entry;
            state.hourWindow.count += 1;
            saveState(state);
            return { notified: true, suppressed: false, fingerprint: fp };
        } catch (err) {
            console.error(
                '[criticalAlert] Discord webhook failed:',
                err.response?.data || err.message
            );
            state.fingerprints[fp] = entry;
            saveState(state);
            return {
                notified: false,
                suppressed: false,
                reason: 'webhook_failed',
                fingerprint: fp,
            };
        }
    }

    return { notify, fingerprint };
}

const criticalAlert = createCriticalAlert();

// --- logger (inlined) ---
const logDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logDir)) fs.mkdirSync(logDir, { recursive: true });

const logDestOpts = { sync: true, mkdir: true };

function formatLogLine() {
    return new Transform({
        transform(chunk, _enc, cb) {
            const lines = chunk.toString().split('\n').filter(Boolean);
            const formatted = lines.map((line) => {
                try {
                    const entry = JSON.parse(line);
                    delete entry.level;
                    delete entry.hostname;
                    if (typeof entry.time === 'number') {
                        entry.time = new Date(entry.time).toISOString();
                    }
                    return JSON.stringify(entry);
                } catch {
                    return line;
                }
            });

            cb(null, `${formatted.join('\n')}\n`);
        },
    });
}

function logDestination(filename) {
    const dest = pino.destination({
        dest: path.join(logDir, filename),
        ...logDestOpts,
    });
    const formatter = formatLogLine();
    formatter.pipe(dest);
    return formatter;
}

const baseLogger = pino(
    {
        level: process.env.LOG_LEVEL || 'info',
        base: null,
        timestamp: pino.stdTimeFunctions.isoTime,
    },
    pino.multistream([
        {
            level: 'info',
            stream: logDestination('upbot.log'),
        },
        {
            level: 'error',
            stream: logDestination('upbot-error.log'),
        },
    ])
);

function critical(context = {}, message = '') {
    const payload =
        typeof context === 'string' ? { error: context } : { ...context };

    const msg =
        typeof message === 'string' && message
            ? message
            : payload.error || 'Critical error';

    baseLogger.error({ ...payload, critical: true }, msg);

    criticalAlert.notify(payload, msg).catch((err) => {
        baseLogger.error(
            { error: err.message, stack: err.stack, criticalAlert: true },
            'Critical alert dispatch failed'
        );
    });
}

const logger = Object.assign(baseLogger, { critical });

function exitProcess(code, message) {
    if (message) {
        console.error(message);
        logger.error(message);
    }

    logger.flush(() => process.exit(code));
}

// --- Leadcliq API client (inlined) ---
class Leadcliq {
    constructor(apiKey, profileCode) {
        this.apiKey = apiKey;
        this.profileCode = profileCode;
        this.endpoint = process.env.LEADCLIQ_ENDPOINT;
    }

    _withProfileCode(data = {}) {
        return { ...data, code: this.profileCode };
    }

    async request(url, data = {}) {
        const fullUrl = `${this.endpoint}${url}`;
        const response = await fetch(fullUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Api-Token': this.apiKey,
            },
            body: JSON.stringify(data),
        });

        const raw = await response.text();
        let result = null;
        try {
            result = raw ? JSON.parse(raw) : null;
        } catch {
            result = { _raw: raw?.slice(0, 500) };
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${raw}`);
        }

        return result;
    }

    async sendJob(jobdetail) {
        return await this.request('api/bot/job', jobdetail);
    }

    async campaigns() {
        return await this.request('api/bot/campaign', this._withProfileCode());
    }

    async writer(jobId, campaignId) {
        return await this.request('api/bot/writer', { jobId, campaignId });
    }

    async analysis(jobId, campaignId) {
        return await this.request(
            'api/bot/analysis',
            this._withProfileCode({ jobId, campaignId })
        );
    }

    async recentAnalysis() {
        return await this.request(
            'api/bot/analysis/recent',
            this._withProfileCode()
        );
    }

    async getRecentScannedJobs() {
        return await this.request('api/bot/recent');
    }

    async apply(jobId, campaignId) {
        return await this.request('api/bot/apply', { jobId, campaignId });
    }

    async updateJobStat(jobId, campaignId, note) {
        return await this.request('api/bot/job-stat', {
            jobId,
            campaignId,
            note,
        });
    }

    async validateProfile() {
        return await this.request('api/bot/profile/validate', {
            code: this.profileCode,
        });
    }
}

// --- browser (inlined) ---
const PROFILES_DIR = path.join(__dirname, 'profiles');

/** @type {string|null} */
let activeLocalProxyUrl = null;

function getArg(name) {
    const prefix = `--${name}=`;
    const hit = process.argv.find((a) => a.startsWith(prefix));
    return hit ? hit.slice(prefix.length) : null;
}

function cdpPortForProfile(profileCode) {
    let hash = 0;
    for (const ch of profileCode) {
        hash = (hash * 31 + ch.charCodeAt(0)) >>> 0;
    }
    return 9222 + (hash % 78);
}

function resolveProfileUserDataDir(profileCode, explicitDir = null) {
    if (explicitDir) {
        return explicitDir;
    }
    if (!profileCode) {
        throw new Error('Profile code is required to resolve user-data-dir');
    }
    return path.join(PROFILES_DIR, profileCode);
}

function resolveCdpHttp(profileCode, explicitCdp = null) {
    if (explicitCdp) {
        return explicitCdp.replace(/\/$/, '');
    }
    if (!profileCode) {
        return null;
    }
    const port = cdpPortForProfile(profileCode);
    return `http://127.0.0.1:${port}`;
}

async function getCdpWsUrl(cdpHttp) {
    const res = await fetch(`${cdpHttp.replace(/\/$/, '')}/json/version`);
    if (!res.ok) {
        throw new Error(`Chrome is not reachable at ${cdpHttp}`);
    }
    const { webSocketDebuggerUrl } = await res.json();
    return webSocketDebuggerUrl;
}

async function isCdpAvailable(cdpHttp) {
    try {
        const res = await fetch(`${cdpHttp.replace(/\/$/, '')}/json/version`);
        return res.ok;
    } catch {
        return false;
    }
}

function parseCdpPort(cdpHttp) {
    const url = new URL(cdpHttp);
    return url.port || (url.protocol === 'https:' ? '443' : '80');
}

function buildUpstreamProxyUrl(proxy) {
    if (!proxy?.host || !proxy?.port) {
        return null;
    }
    const scheme = proxy.protocol === 'socks5' ? 'socks5' : 'http';
    if (proxy.username && proxy.password) {
        const user = encodeURIComponent(proxy.username);
        const pass = encodeURIComponent(proxy.password);
        return `${scheme}://${user}:${pass}@${proxy.host}:${proxy.port}`;
    }
    return `${scheme}://${proxy.host}:${proxy.port}`;
}

async function resolveChromeProxyServer(proxy) {
    const upstream = buildUpstreamProxyUrl(proxy);
    if (!upstream) {
        return null;
    }

    if (!proxy.username || !proxy.password) {
        const url = new URL(upstream);
        return `${url.hostname}:${url.port}`;
    }

    const localUrl = await ProxyChain.anonymizeProxy(upstream);
    activeLocalProxyUrl = localUrl;
    const local = new URL(localUrl);
    logger.info('Local proxy relay:', `${local.hostname}:${local.port}`);
    return `${local.hostname}:${local.port}`;
}

async function closeLocalProxy() {
    if (!activeLocalProxyUrl) {
        return;
    }
    try {
        await ProxyChain.closeAnonymizedProxy(activeLocalProxyUrl, true);
    } catch {
        // already closed
    }
    activeLocalProxyUrl = null;
}

function resolveChromeExecutable() {
    return process.env.CHROME_PATH || process.env.CHROME_BIN || 'google-chrome';
}

async function waitForCdp(cdpHttp, timeoutMs = 45_000) {
    const started = Date.now();
    while (Date.now() - started < timeoutMs) {
        if (await isCdpAvailable(cdpHttp)) {
            return true;
        }
        await new Promise((resolve) => setTimeout(resolve, 500));
    }
    return false;
}

async function launchChromeNative(cdpHttp, proxy, userDataDir) {
    const port = parseCdpPort(cdpHttp);
    const proxyServer = await resolveChromeProxyServer(proxy);
    const args = [
        `--remote-debugging-port=${port}`,
        `--user-data-dir=${userDataDir}`,
        '--no-first-run',
        '--no-default-browser-check',
    ];

    if (proxyServer) {
        args.push(`--proxy-server=${proxyServer}`);
        logger.info('Launching Chrome with proxy:', proxyServer);
    }

    logger.info('Launching Chrome:', resolveChromeExecutable());
    logger.info('User data dir:', userDataDir);

    const child = spawn(resolveChromeExecutable(), args, {
        detached: true,
        stdio: 'ignore',
    });
    child.unref();

    const ready = await waitForCdp(cdpHttp);
    if (!ready) {
        throw new Error(`Chrome did not open on ${cdpHttp}`);
    }

    return attachExistingChrome(cdpHttp);
}

async function attachExistingChrome(cdpHttp) {
    const browser = await chromium.connectOverCDP(await getCdpWsUrl(cdpHttp));
    const context = browser.contexts()[0];
    const page = context.pages()[0] ?? (await context.newPage());
    return { browser, context, page };
}

async function connectBrowser({
    profileCode,
    cdpHttp = null,
    proxy = null,
    userDataDir = null,
}) {
    const resolvedCdp = resolveCdpHttp(profileCode, cdpHttp);
    const resolvedUserDataDir = resolveProfileUserDataDir(
        profileCode,
        userDataDir
    );

    fs.mkdirSync(resolvedUserDataDir, { recursive: true });

    logger.info(
        {
            profile: profileCode,
            cdp: resolvedCdp,
            userDataDir: resolvedUserDataDir,
        },
        'Browser profile'
    );

    if (await isCdpAvailable(resolvedCdp)) {
        logger.info('Attaching to running Chrome on', resolvedCdp);
        return {
            ...(await attachExistingChrome(resolvedCdp)),
            cdpHttp: resolvedCdp,
            userDataDir: resolvedUserDataDir,
        };
    }

    logger.info('Chrome not running, launching...');

    try {
        const session = await launchChromeNative(
            resolvedCdp,
            proxy,
            resolvedUserDataDir
        );
        return {
            ...session,
            cdpHttp: resolvedCdp,
            userDataDir: resolvedUserDataDir,
        };
    } catch (error) {
        if (
            /profile|in use|already running|singleton/i.test(
                String(error.message)
            )
        ) {
            const err = new Error(
                `Chrome profile is in use (${resolvedUserDataDir}). Close that Chrome window, then re-run.`
            );
            err.code = 'PROFILE_IN_USE';
            throw err;
        }
        throw error;
    }
}

const profileCode = getArg('profile');
const CDP_HTTP = getArg('cdp');
const userDataDir = getArg('user-data-dir');

const USAGE = 'Usage: npm run autobidv3 -- --profile=CODE';

async function main() {
    if (!profileCode) {
        exitProcess(1, `Profile code is required. ${USAGE}`);
        return;
    }

    let campaigns = [];
    let recentScannedJobs = [];
    let recentAnalysis = [];
    // const { Stagehand } = await import('@browserbasehq/stagehand');
    // const upwork = new Upwork();
    const leadcliq = new Leadcliq(process.env.LEADCLIQ_API_KEY, profileCode);

    //validate profile code
    const profileRes = await leadcliq.validateProfile();
    if (!profileRes?.valid || !profileRes?.profile) {
        exitProcess(1, 'Profile code is invalid');
        return;
    }
    const profile = profileRes.profile;

    let browser;
    let page;
    try {
        ({ browser, page } = await connectBrowser({
            profileCode,
            cdpHttp: CDP_HTTP,
            proxy: profile.proxy,
            userDataDir,
        }));
    } catch (error) {
        if (error.code === 'PROFILE_IN_USE') {
            exitProcess(1, error.message);
            return;
        }
        throw error;
    }

    async function fetch() {
        campaigns = await leadcliq.campaigns();
        recentScannedJobs = await leadcliq.getRecentScannedJobs();
        recentAnalysis = await leadcliq.recentAnalysis();
    }

    let lastMouseX = null;
    let lastMouseY = null;

    function getViewport() {
        return page.viewportSize() || { width: 1280, height: 720 };
    }

    async function humanPause(minMs, maxMs) {
        const waitMs = Math.round(minMs + Math.random() * (maxMs - minMs));
        return new Promise((resolve) => setTimeout(resolve, waitMs));
    }

    async function randomBuffer() {
        await humanPause(800, 2500);
    }

    async function mouseMove() {
        const { width, height } = getViewport();
        const padding = 40;
        const maxX = Math.max(padding + 1, width - padding);
        const maxY = Math.max(padding + 1, height - padding);

        if (lastMouseX === null) {
            lastMouseX = padding + Math.random() * (maxX - padding);
            lastMouseY = padding + Math.random() * (maxY - padding);
        }

        const count = Math.floor(Math.random() * 4) + 2;
        for (let i = 0; i < count; i++) {
            const targetX = padding + Math.random() * (maxX - padding);
            const targetY = padding + Math.random() * (maxY - padding);
            const distance = Math.hypot(
                targetX - lastMouseX,
                targetY - lastMouseY
            );
            const steps = Math.max(
                15,
                Math.min(
                    40,
                    Math.floor(distance / 8) + Math.floor(Math.random() * 10)
                )
            );
            await page.mouse.move(targetX, targetY, { steps });
            lastMouseX = targetX;
            lastMouseY = targetY;
            await humanPause(50, 200);
        }

        if (Math.random() < 0.35) {
            const scrollY =
                (Math.random() < 0.5 ? 1 : -1) *
                (Math.floor(Math.random() * 250) + 80);
            await page.mouse.wheel(0, scrollY);
            await humanPause(100, 300);
        }
    }

    async function humanClick(locator, options = {}) {
        await locator.hover();
        await humanPause(150, 450);
        await locator.click(options);
        await humanPause(80, 220);
    }

    async function humanType(locator, text) {
        for (let i = 0; i < text.length; i++) {
            await locator.pressSequentially(text[i], { delay: 0 });
            if (i < text.length - 1) {
                const charDelay = typingDelayMs();
                await humanPause(charDelay, charDelay);
            }
        }
    }

    async function randomDelay(minSec, maxSec) {
        const seconds = minSec + Math.random() * (maxSec - minSec);
        return delay(seconds);
    }

    async function getJobs(campaign) {
        await page.goto(campaign.search_url, {
            waitUntil: 'domcontentloaded',
            timeout: 150_000,
        });
        const isJobsLoaded = await page
            .waitForSelector('article[data-ev-job-uid]', {
                timeout: 150_000,
            })
            .then(() => true)
            .catch(() => false);

        if (!isJobsLoaded) {
            logger.error('Failed to load jobs');
            return [];
        }

        const jobs = await page.evaluate(() =>
            [...document.querySelectorAll('article[data-ev-job-uid]')].map(
                (card) => ({
                    isApplied: card
                        .querySelector('[data-test*="badges"]')
                        ?.innerText.match(/^applied/i),
                    type: card
                        .querySelector('[data-test="JobInfo"]')
                        ?.innerText.match(/^fixed/i)
                        ? 'fixed'
                        : 'hourly',
                    url: `https://www.upwork.com/jobs/~02${card.getAttribute('data-ev-job-uid')}`,
                    jobId: card.getAttribute('data-ev-job-uid'),
                    applyUrl: `https://www.upwork.com/nx/proposals/job/~02${card.getAttribute('data-ev-job-uid')}/apply/`,
                })
            )
        );
        return jobs;
    }

    async function parsePostedAt(details) {
        if (!details) {
            return null;
        }

        const number = Number(details.match(/(\d+)/)?.[1], 1);
        if (details.includes('just now')) {
            return new Date().toISOString();
        }
        if (details.includes('minute')) {
            return new Date(Date.now() - number * 60 * 1000).toISOString();
        }
        if (details.includes('hour')) {
            return new Date(Date.now() - number * 60 * 60 * 1000).toISOString();
        }
        if (details.includes('yesterday')) {
            return new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString();
        }
        if (details.includes('day')) {
            return new Date(
                Date.now() - number * 24 * 60 * 60 * 1000
            ).toISOString();
        }
        if (details.includes('week')) {
            return new Date(
                Date.now() - number * 7 * 24 * 60 * 60 * 1000
            ).toISOString();
        }
        if (details.includes('month')) {
            return new Date(
                Date.now() - number * 30 * 24 * 60 * 60 * 1000
            ).toISOString();
        }
        return null;
    }

    async function extractJobDetails(job) {
        await mouseMove();

        const { jobId, url } = job;
        await humanClick(page.locator(`article[data-ev-job-uid="${jobId}"] a`));

        const isJobDetailsLoaded = await page
            .locator('[slidername*="job-details"]')
            .waitFor({ state: 'visible', timeout: 150_000 })
            .then(() => true)
            .catch(() => false);

        if (!isJobDetailsLoaded) {
            logger.error('Failed to load job details');
            return null;
        }

        await randomBuffer();

        await delay(15);

        const details = await page
            .locator('[slidername*="job-details"]')
            .innerText()
            .catch(() => null);
        await mouseMove();
        await humanDelay(2);
        await humanClick(
            page.locator('[data-test="JobDetailsSliderHeader"] button'),
            { force: true }
        ).catch(() => null);
        await page
            .locator('[slidername*="job-details"]')
            .waitFor({ state: 'hidden', timeout: 150_000 })
            .catch(() => null);
        await randomBuffer();

        //test in console document.querySelector('[slidername="job-details"]')?.innerText?.match(/Posted\s+(.*)/i)?.[1];

        const postedAtString = details.match(/Posted\s+(.*)/i)?.[1];
        const postedAt = await parsePostedAt(postedAtString);

        const skillsString = details.match(
            /Mandatory skills\s*([\s\S]*?)(?:Preferred qualifications|Activity on this job)/i
        );
        let skills = [];
        if (skillsString) {
            skills = skillsString[1]
                .split('\n')
                .map((x) => x.trim())
                .filter(Boolean);
        }

        if (!postedAt || skills.length === 0) {
            console.log(jobId, postedAt, skills, details);
        }

        return { id: jobId, url, rawText: details, postedAt, skills };
    }

    async function humanDelay(delayLevel) {
        const minSec = 1000 * delayLevel; // e.g. 1=1s, 10=10s
        const maxSec = 1000 * delayLevel * 2; // 1=2s, 10=20s
        return new Promise((resolve) => {
            setTimeout(resolve, Math.random() * (maxSec - minSec) + minSec);
        });
    }

    function typingDelayMs() {
        return 35 + Math.floor(Math.random() * 55);
    }

    async function applyToJob(job, campaign) {
        await mouseMove();
        await humanDelay(3);

        logger.info({ jobId: job.jobId, campaignId: campaign.id }, 'APPLY_JOB');

        const writer = await leadcliq.writer(job.jobId, campaign.id);
        await page.goto(job.applyUrl, {
            waitUntil: 'domcontentloaded',
            timeout: 150_000,
        });
        await page
            .getByLabel('Cover Letter')
            .waitFor({ state: 'visible', timeout: 150_000 });
        await randomBuffer();

        //check if there is any warning before applying
        const bodyText = await page.locator('body').innerText();
        if (bodyText.includes('You do not meet all')) {
            await leadcliq.updateJobStat(
                job.jobId,
                campaign.id,
                'Does not meet qualifications'
            );
            logger.info(
                { jobId: job.jobId, campaignId: campaign.id },
                'WARNING_FOUND'
            );
            return false;
        }

        const selectDropdown = async (page, placeholder, option) => {
            try {
                await humanClick(
                    page.getByText(placeholder, {
                        exact: true,
                    })
                );

                await humanClick(
                    page
                        .getByText(option, {
                            exact: true,
                        })
                        .last()
                );
            } catch (error) {
                logger.error(`Element not found: ${placeholder} ${option}`);
            }
        };
        const selectRadio = async (page, question, option) => {
            const section = await page.locator('section').filter({
                hasText: question,
            });
            await humanClick(
                section.locator('label').filter({
                    hasText: option,
                })
            );
        };

        await mouseMove();
        await humanDelay(3);

        if (job.type === 'hourly') {
            await humanDelay(2);
            await selectDropdown(page, 'Select a frequency', 'Never');
        } else {
            // await page
            //     .locator(`input[name="milestoneMode"][value="default"]`)
            //     .click();
            await selectRadio(
                page,
                'How do you want to be paid?',
                'By project'
            );
            await selectDropdown(
                page,
                'Select a duration',
                'Less than 1 month'
            );
        }

        await humanClick(page.getByLabel('Cover Letter'));
        await humanType(page.getByLabel('Cover Letter'), writer.cover_letter);
        await humanDelay(10);

        if (writer.questions.length > 0) {
            const qaList = writer.questions;
            const normalize = (str = '') =>
                str.replace(/\s+/g, ' ').trim().toLowerCase();
            const questionMap = new Map(
                qaList.map((item) => [normalize(item.question), item.answer])
            );
            const groups = page.locator('.form-group');
            const count = await groups.count();
            let answered = 0;
            for (let i = 0; i < count; i++) {
                const group = groups.nth(i);
                const label = group.locator('label').first();
                const textarea = group.locator('textarea').first();
                if (!(await label.count()) || !(await textarea.count())) {
                    continue;
                }
                const questionText = normalize(await label.innerText());
                const answer = questionMap.get(questionText);
                if (!answer) {
                    logger.warn(
                        { ...campaign, question: questionText },
                        'Apply: no answer for question'
                    );
                    continue;
                }
                await humanClick(textarea);
                await textarea.fill('');
                await humanType(textarea, answer);
                answered++;
                await mouseMove();
                await humanDelay(6);
            }
        }
        await humanClick(
            page.locator('div.fe-apply-footer-controls button:first-child')
        );

        if (job.type === 'fixed') {
            await mouseMove();
            await humanDelay(8);
            await humanClick(
                page.locator('label').filter({
                    hasText: 'Yes, I understand.',
                })
            );
            const continueBtn = page.getByRole('button', {
                name: /continue/i,
            });
            await mouseMove();
            await humanDelay(8);
            await continueBtn.waitFor({ state: 'visible' });
            await humanClick(continueBtn);
        }
        await leadcliq.apply(job.jobId, campaign.id);
        return true;
    }

    async function mainengine(campaign) {
        const jobs = await getJobs(campaign);

        if (jobs.length === 0) {
            logger.info({ campaignId: campaign.id }, 'No jobs found');
            return;
        }

        const jobDetailMissedCount = 0;
        let extractCount = 0;
        let maxExtractCount = Math.min(2, jobs.length - 2);

        for (const job of jobs) {
            try {
                if (job.isApplied) {
                    continue;
                }
                if (recentScannedJobs.includes(job.jobId) && !job.isApplied) {
                    let isAnalysisExist = recentAnalysis.find(
                        (analysis) =>
                            analysis.job_uid == job.jobId &&
                            analysis.campaign_id == campaign.id
                    );
                    if (!isAnalysisExist) {
                        const analysis = await leadcliq.analysis(
                            job.jobId,
                            campaign.id
                        );
                        isAnalysisExist = analysis;
                    }
                    if (
                        isAnalysisExist.is_matched &&
                        !isAnalysisExist.is_applied
                    ) {
                        await applyToJob(job, campaign);
                        return; //break the loop
                    }
                    continue;
                }

                //to make sure not extract all jobs details at once
                if (extractCount >= maxExtractCount) {
                    break;
                }

                await mouseMove();
                await humanDelay(3);

                const details = await extractJobDetails(job);

                // human Behavior
                await mouseMove();
                await humanDelay(5);

                if (!details) {
                    jobDetailMissedCount++;
                    logger.error(
                        {
                            jobId: job.id,
                            error: 'Job detail not found',
                        },
                        'Job detail not found'
                    );
                    continue;
                }
                const res = await leadcliq.sendJob(details);
                const analysis = await leadcliq.analysis(
                    job.jobId,
                    campaign.id
                );
                if (!analysis.is_matched || analysis.is_applied) {
                    continue;
                } else if (analysis.is_matched && !job.isApplied) {
                    await applyToJob(job, campaign);
                    return; //break the loop
                }
                extractCount++;
            } catch (error) {
                logger.error(
                    {
                        jobId: job.id,
                        error: error.message,
                        stack: error.stack,
                    },
                    'Job processing error'
                );
                continue;
            }
            await randomDelay(15, 45);
        }
        if (jobDetailMissedCount > 5) {
            logger.critical(
                {
                    error: 'Job details seemed got get scrapped or extracted',
                    fn: 'extractJobDetails',
                },
                'Job processing error'
            );
        }
    }
    async function delay(seconds) {
        return new Promise((resolve) => setTimeout(resolve, seconds * 1000));
    }

    function randomInt(min, max) {
        return Math.floor(min + Math.random() * (max - min + 1));
    }

    while (true) {
        await fetch();
        if (campaigns.length === 0) {
            await delay(10 * 60);
            continue;
        }

        let campaignsUntilBreak = randomInt(2, 5);

        for (const campaign of campaigns) {
            const startTime = Date.now();

            await mouseMove();
            await humanDelay(5);

            try {
                await mainengine(campaign);
            } catch (error) {
                logger.error(
                    {
                        campaignId: campaign.id,
                        error: error.message,
                        stack: error.stack,
                    },
                    'Campaign processing error'
                );
            }

            await randomDelay(1 * 60, 5 * 60);

            campaignsUntilBreak--;
            if (campaignsUntilBreak <= 0) {
                await randomDelay(1 * 60, 5 * 60);
                campaignsUntilBreak = randomInt(2, 5);
            }

            const endTime = Date.now();
            const duration = endTime - startTime;
            logger.info(
                {
                    campaignId: campaign.id,
                    duration: (duration / 1000 / 60).toFixed(2),
                },
                'CAMPAIGN_DURATION'
            );
        }
    }
    //await stagehand.close();
}

main().catch(async (err) => {
    console.error(err);
    await closeLocalProxy();
    exitProcess(1);
});

for (const signal of ['SIGINT', 'SIGTERM']) {
    process.on(signal, () => {
        void closeLocalProxy().finally(() => exitProcess(0));
    });
}

//init stagehand
// const stagehand = new Stagehand({
//     env: 'LOCAL',
//     page: page,
//     model: {
//         modelName: MODEL,
//         apiKey: process.env.OPENAI_API_KEY,
//         temperature: 0,
//         allowSystemInMessages: true,
//     },
//     localBrowserLaunchOptions: {
//         cdpUrl: await getCdpWsUrl(),
//     },
//     domSettleTimeout: 30_000,
// });
// await stagehand.init();

// const page =
//     stagehand.context.pages()[0] ?? (await stagehand.context.newPage());
