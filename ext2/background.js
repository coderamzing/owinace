// LeadCliq ext2 — API + Phase 1 scan orchestrator (no auto-apply)
const BACKEND_URL = 'https://leadcliq.ai/';
//const BACKEND_URL = 'http://localhost:8000/';
const SCAN_ALARM = 'leadcliq-scan-tick';
// Match upbot2/lib/engine.js: randomDelay(1*60, 5*60) between campaigns
const BETWEEN_CAMPAIGN_MIN_MS = 60_000;
const BETWEEN_CAMPAIGN_MAX_MS = 5 * 60_000;
// Match upbot2: delay(10 * 60) when no campaigns
const EMPTY_CAMPAIGNS_MS = 10 * 60_000;
const BETWEEN_JOB_MIN_MS = 8_000;
const BETWEEN_JOB_MAX_MS = 20_000;
const MAX_EXTRACTS_PER_PASS = 5;
/** Pause after Start Scan before opening Upwork */
const SCAN_START_WAIT_MS = 5_000;
/** Pause after search page load before first job action (open dialog) */
const PAGE_LOAD_SETTLE_MS = 10_000;
const IP_CHECK_URLS = [
	'https://api.ipify.org?format=json',
	'https://ipv4.nexcess.net/',
];

/** @type {null | {
 *  running: boolean,
 *  teamId: number,
 *  profileCode: string,
 *  profileTitle: string,
 *  tabId: number|null,
 *  campaignIndex: number,
 *  campaignsUntilBreak: number,
 *  campaigns: Array<{id:number,title:string,search_url:string,has_webhook:boolean}>,
 *  recentScanned: string[],
 *  recentAnalysis: Array<any>,
 *  status: string,
 *  lastMatch: null|{jobId:string,campaignId:number,title:string,at:string},
 *  stats: {scanned:number,matched:number,passes:number,errors:number},
 *  expectedIp?: string|null,
 *  browserIp?: string|null
 * }} */
let scanState = null;

/** @type {null | { username: string, password: string }} */
let activeProxyAuth = null;

/** @type {boolean} */
let proxyOwnedByExtension = false;

function randomMs(min, max) {
	return Math.round(min + Math.random() * (max - min));
}

/** Inclusive integer range — same as upbot2 randomInt */
function randomInt(min, max) {
	return Math.floor(min + Math.random() * (max - min + 1));
}

function formatWait(ms) {
	const sec = Math.round(ms / 1000);
	if (sec < 60) return `${sec}s`;
	const min = Math.floor(sec / 60);
	const rem = sec % 60;
	return rem ? `${min}m ${rem}s` : `${min}m`;
}

function sleep(ms) {
	return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Bypass API / local hosts so LeadCliq backend calls stay direct while
 * all browser tabs (including Upwork) go through the profile proxy.
 */
function proxyBypassList() {
	const hosts = ['localhost', '127.0.0.1', '::1', '<local>'];
	try {
		const apiHost = new URL(BACKEND_URL).hostname;
		if (apiHost && !hosts.includes(apiHost)) hosts.push(apiHost);
	} catch (_) {}
	return hosts;
}

function proxyScheme(protocol) {
	const p = String(protocol || 'http').toLowerCase();
	if (p === 'socks5' || p === 'socks') return 'socks5';
	if (p === 'socks4') return 'socks4';
	if (p === 'https') return 'https';
	return 'http';
}

function parseEgressIp(text, contentType) {
	const raw = String(text || '').trim();
	if (!raw) return '';
	if ((contentType || '').includes('application/json') || raw.startsWith('{')) {
		try {
			const data = JSON.parse(raw);
			return String(data?.ip || '').trim();
		} catch (_) {}
	}
	const match = raw.match(/\b(\d{1,3}(?:\.\d{1,3}){3})\b/);
	return match ? match[1] : '';
}

async function fetchBrowserEgressIp() {
	let lastError = null;
	for (const url of IP_CHECK_URLS) {
		const controller = new AbortController();
		const timer = setTimeout(() => controller.abort(), 20000);
		try {
			const res = await fetch(url, {
				method: 'GET',
				cache: 'no-store',
				signal: controller.signal,
			});
			const text = await res.text();
			if (!res.ok) {
				throw new Error(`IP check HTTP ${res.status}`);
			}
			const ip = parseEgressIp(text, res.headers.get('content-type') || '');
			if (!ip) throw new Error('Could not parse browser egress IP');
			return ip;
		} catch (e) {
			lastError = e;
			const msg = String(e?.message || e);
			if (msg.includes('407') || msg.includes('Proxy') || msg.includes('CONNECT')) {
				throw new Error(
					'Proxy auth failed (HTTP 407). Check proxy username/password on the profile, then reload the extension and retry.'
				);
			}
		} finally {
			clearTimeout(timer);
		}
	}
	throw new Error(
		`Could not read browser egress IP: ${lastError?.message || lastError || 'unknown error'}`
	);
}

async function setActiveProxyAuth(auth) {
	activeProxyAuth = auth;
	if (auth) {
		await chrome.storage.local.set({ proxyAuth: auth });
	} else {
		await chrome.storage.local.remove('proxyAuth');
	}
}

async function restoreActiveProxyAuth() {
	if (activeProxyAuth) return activeProxyAuth;
	const stored = await chrome.storage.local.get('proxyAuth');
	if (stored.proxyAuth?.username != null) {
		activeProxyAuth = {
			username: String(stored.proxyAuth.username),
			password: String(stored.proxyAuth.password ?? ''),
		};
	}
	return activeProxyAuth;
}

function getProxySettings() {
	return new Promise((resolve) => {
		chrome.proxy.settings.get({}, resolve);
	});
}

async function applyProfileProxy(proxy) {
	if (!proxy?.host || !proxy?.port) {
		throw new Error('Profile proxy host/port missing');
	}

	const username = proxy.username != null ? String(proxy.username) : '';
	const password = proxy.password != null ? String(proxy.password) : '';
	if (!username) {
		throw new Error('Profile proxy username is required (Chrome cannot authenticate empty proxy login)');
	}
	await setActiveProxyAuth({ username, password });

	const scheme = proxyScheme(proxy.protocol);
	const config = {
		mode: 'fixed_servers',
		rules: {
			singleProxy: {
				scheme,
				host: String(proxy.host).trim(),
				port: Number(proxy.port),
			},
			bypassList: proxyBypassList(),
		},
	};

	await chrome.proxy.settings.set({ value: config, scope: 'regular' });
	proxyOwnedByExtension = true;
	await chrome.storage.local.set({ proxyOwnedByExtension: true });

	const current = await getProxySettings();
	const control = current?.levelOfControl || '';
	if (control === 'not_controllable' || control === 'controlled_by_other_extensions') {
		throw new Error(
			`Chrome proxy is not controlled by LeadCliq (${control}). Disable other proxy extensions/policies and retry.`
		);
	}
	if (current?.value?.mode !== 'fixed_servers') {
		throw new Error('Failed to apply Chrome proxy settings');
	}
}

async function clearProfileProxy({ force = false } = {}) {
	await setActiveProxyAuth(null);
	if (!force && !proxyOwnedByExtension) {
		const stored = await chrome.storage.local.get('proxyOwnedByExtension');
		if (!stored.proxyOwnedByExtension) return;
	}
	try {
		await chrome.proxy.settings.clear({ scope: 'regular' });
	} catch (_) {}
	proxyOwnedByExtension = false;
	await chrome.storage.local.set({ proxyOwnedByExtension: false });
}

async function setActiveProxyProfile(profile) {
	if (!profile) {
		await chrome.storage.local.remove('activeProxyProfile');
		return;
	}
	await chrome.storage.local.set({ activeProxyProfile: profile });
}

async function getActiveProxyProfile() {
	const stored = await chrome.storage.local.get('activeProxyProfile');
	return stored.activeProxyProfile || null;
}

async function ensureProxyMatchesProfile(teamId, profileCode, { clearOnFail = true } = {}) {
	const data = await apiFetch('api/bot/proxy', {
		method: 'POST',
		body: { team_id: Number(teamId), code: profileCode, refresh: true },
	});
	const payload = data.data;
	if (!payload?.proxy || !payload?.expected_ip) {
		throw new Error('Profile proxy or expected IP is missing');
	}

	await applyProfileProxy(payload.proxy);
	// Auth listener + proxy settings need a beat before CONNECT
	await sleep(500);
	await restoreActiveProxyAuth();

	const browserIp = await fetchBrowserEgressIp();
	const expectedIp = String(payload.expected_ip).trim();

	if (browserIp !== expectedIp) {
		if (clearOnFail) {
			await clearProfileProxy({ force: true });
			await setActiveProxyProfile(null);
		}
		throw new Error(
			`Proxy IP mismatch. Browser: ${browserIp} · Profile: ${expectedIp}. Check the profile proxy, then try again.`
		);
	}

	const profile = {
		enabled: true,
		teamId: Number(teamId),
		code: String(profileCode),
		title: payload.title || profileCode,
		expectedIp,
		browserIp,
		at: new Date().toISOString(),
	};
	await setActiveProxyProfile(profile);

	return { browserIp, expectedIp, title: payload.title, profile };
}

async function enableProxyForProfile(teamId, profileCode) {
	return ensureProxyMatchesProfile(teamId, profileCode, { clearOnFail: true });
}

async function disableProxy() {
	await clearProfileProxy({ force: true });
	await setActiveProxyProfile(null);
}

async function getProxyStatusPayload() {
	const active = await getActiveProxyProfile();
	return {
		enabled: !!active?.enabled && proxyOwnedByExtension,
		teamId: active?.teamId || null,
		code: active?.code || null,
		title: active?.title || null,
		browserIp: active?.browserIp || null,
		expectedIp: active?.expectedIp || null,
		matched: !!(active?.browserIp && active?.expectedIp && active.browserIp === active.expectedIp),
	};
}

/**
 * Ensure the selected profile proxy is active before/during scan.
 * Does not clear a working sticky proxy on unrelated failures.
 */
async function ensureScanProxy(teamId, profileCode) {
	const active = await getActiveProxyProfile();
	if (
		active?.enabled &&
		String(active.code) === String(profileCode) &&
		Number(active.teamId) === Number(teamId) &&
		active.expectedIp
	) {
		await restoreActiveProxyAuth();
		const browserIp = await fetchBrowserEgressIp();
		if (browserIp === active.expectedIp) {
			active.browserIp = browserIp;
			await setActiveProxyProfile(active);
			return {
				browserIp,
				expectedIp: active.expectedIp,
				title: active.title,
			};
		}
	}
	return enableProxyForProfile(teamId, profileCode);
}

// MV3: must use asyncBlocking + callback (returning credentials with "blocking" often yields 407)
if (chrome.webRequest?.onAuthRequired) {
	chrome.webRequest.onAuthRequired.addListener(
		(details, asyncCallback) => {
			const finish = (auth) => {
				if (auth?.username != null && details.isProxy) {
					asyncCallback({
						authCredentials: {
							username: auth.username,
							password: auth.password ?? '',
						},
					});
					return;
				}
				asyncCallback({});
			};

			if (!details.isProxy) {
				finish(null);
				return;
			}

			if (activeProxyAuth) {
				finish(activeProxyAuth);
				return;
			}

			chrome.storage.local.get('proxyAuth', (stored) => {
				if (stored.proxyAuth?.username != null) {
					activeProxyAuth = {
						username: String(stored.proxyAuth.username),
						password: String(stored.proxyAuth.password ?? ''),
					};
					finish(activeProxyAuth);
					return;
				}
				finish(null);
			});
		},
		{ urls: ['<all_urls>'] },
		['asyncBlocking']
	);
}

// Restore auth credentials as soon as the service worker starts
chrome.storage.local.get(['proxyAuth', 'proxyOwnedByExtension'], (data) => {
	if (data.proxyAuth?.username != null) {
		activeProxyAuth = {
			username: String(data.proxyAuth.username),
			password: String(data.proxyAuth.password ?? ''),
		};
	}
	proxyOwnedByExtension = !!data.proxyOwnedByExtension;
});

async function getSettings() {
	return new Promise((resolve) => {
		chrome.storage.local.get(['token', 'teamId', 'teams', 'scanState'], (data) => {
			resolve({
				baseUrl: BACKEND_URL.replace(/\/?$/, '/'),
				token: data.token || '',
				teamId: data.teamId || null,
				teams: data.teams || [],
				persistedScan: data.scanState || null,
			});
		});
	});
}

async function setAuth({ token, teamId, teams }) {
	return new Promise((resolve) => {
		chrome.storage.local.set({ token, teamId, teams }, resolve);
	});
}

async function clearAuth() {
	return new Promise((resolve) => {
		chrome.storage.local.remove(['token', 'teamId', 'teams', 'profileCode', 'coverDraft'], resolve);
	});
}

async function persistScanSnapshot() {
	if (!scanState) {
		await chrome.storage.local.remove('scanState');
		return;
	}
	const snapshot = {
		running: scanState.running,
		teamId: scanState.teamId,
		profileCode: scanState.profileCode,
		profileTitle: scanState.profileTitle,
		tabId: scanState.tabId,
		campaignIndex: scanState.campaignIndex,
		campaignsUntilBreak: scanState.campaignsUntilBreak,
		campaigns: scanState.campaigns,
		status: scanState.status,
		lastMatch: scanState.lastMatch,
		stats: scanState.stats,
	};
	await chrome.storage.local.set({ scanState: snapshot });
}

async function apiFetch(path, { method = 'GET', body, auth = true } = {}) {
	const { baseUrl, token } = await getSettings();
	const headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
	if (auth && token) headers['Authorization'] = `Bearer ${token}`;

	const res = await fetch(baseUrl + path.replace(/^\//, ''), {
		method,
		headers,
		body: body ? JSON.stringify(body) : undefined,
	});

	const data = await res.json().catch(() => ({}));
	if (!res.ok) {
		if (data && typeof data === 'object' && res.status === 400) {
			const errorFields = Object.entries(data)
				.filter(([k]) => k !== 'success')
				.map(([key, value]) => {
					if (Array.isArray(value)) return `${key}: ${value.join(', ')}`;
					return `${key}: ${value}`;
				});
			const formatted = errorFields.length ? errorFields.join('\n') : 'Bad request';
			throw new Error(data.error || formatted);
		}
		throw new Error(data.error || data.message || `HTTP ${res.status}`);
	}
	return data;
}

async function sendToTab(tabId, message) {
	try {
		return await chrome.tabs.sendMessage(tabId, message);
	} catch (e) {
		// Content script may not be injected yet — inject and retry
		await chrome.scripting.executeScript({
			target: { tabId },
			files: ['lib/upwork-scan.js', 'lib/upwork-proposal.js', 'content.js'],
		});
		await sleep(400);
		return await chrome.tabs.sendMessage(tabId, message);
	}
}

async function waitTabComplete(tabId, timeoutMs = 90000) {
	const start = Date.now();
	while (Date.now() - start < timeoutMs) {
		const tab = await chrome.tabs.get(tabId).catch(() => null);
		if (!tab) throw new Error('Scan tab was closed');
		if (tab.status === 'complete') return tab;
		await sleep(400);
	}
	throw new Error('Tab load timeout');
}

function notifyMatch({ title, jobUrl, campaignTitle, profileTitle }) {
	const id = `match-${Date.now()}`;
	const profile = profileTitle || scanState?.profileTitle || scanState?.profileCode || '';
	const header = profile ? `${profile} · ${campaignTitle}` : campaignTitle;
	chrome.notifications.create(id, {
		type: 'basic',
		iconUrl: 'icons/icon128.png',
		title: 'LeadCliq — Job matched',
		message: `${header}\n${title || jobUrl}`,
		priority: 2,
	});
}

async function broadcastStatus() {
	await persistScanSnapshot();
	const payload = scanState
		? {
				running: scanState.running,
				status: scanState.status,
				profileTitle: scanState.profileTitle,
				profileCode: scanState.profileCode,
				teamId: scanState.teamId,
				tabId: scanState.tabId,
				stats: scanState.stats,
				lastMatch: scanState.lastMatch,
				campaign: scanState.campaigns[scanState.campaignIndex] || null,
				campaignCount: scanState.campaigns.length,
			}
		: { running: false, status: 'Idle', tabId: null };

	chrome.runtime.sendMessage({ type: 'SCAN_STATUS', data: payload }).catch(() => {});
}

async function refreshMeta(teamId, code) {
	// Same endpoints as upbot2 Leadcliq — shared BotController
	const [recentRes, analysisRes, campaignsRes] = await Promise.all([
		apiFetch('api/bot/recent', { method: 'POST', body: { team_id: teamId } }),
		apiFetch('api/bot/analysis/recent', {
			method: 'POST',
			body: { team_id: teamId, code },
		}),
		apiFetch('api/bot/campaigns', {
			method: 'POST',
			body: { team_id: teamId, code },
		}),
	]);

	const recentList = Array.isArray(recentRes) ? recentRes : recentRes?.data || [];
	const analysisList = Array.isArray(analysisRes) ? analysisRes : analysisRes?.data || [];

	return {
		recentScanned: recentList.map(String),
		recentAnalysis: analysisList,
		campaigns: campaignsRes.data || [],
	};
}

async function findReusableScanTab() {
	if (scanState?.tabId) {
		const existing = await chrome.tabs.get(scanState.tabId).catch(() => null);
		if (existing) return existing.id;
	}

	const { persistedScan } = await getSettings();
	if (persistedScan?.tabId) {
		const existing = await chrome.tabs.get(persistedScan.tabId).catch(() => null);
		if (existing) return existing.id;
	}

	const tabs = await chrome.tabs.query({ url: 'https://www.upwork.com/*' });
	const marked = tabs.find((t) => (t.title || '').startsWith('[LeadCliq]'));
	if (marked?.id) return marked.id;

	return null;
}

/**
 * CapSolver may return cookies as an object map ({cf_clearance: "…"}) OR as an
 * array of cookie objects ([{name, value, domain, …}]). Normalize both to a
 * flat [{name, value}] list so cf_clearance is never silently dropped.
 */
function normalizeCapSolverCookies(raw) {
	const out = [];
	if (!raw) return out;

	if (Array.isArray(raw)) {
		for (const c of raw) {
			if (c && typeof c === 'object' && c.name) {
				out.push({ name: String(c.name), value: String(c.value ?? '') });
			}
		}
		return out;
	}

	if (typeof raw === 'object') {
		for (const [name, value] of Object.entries(raw)) {
			// Guard against nested cookie objects keyed by index/name
			if (value && typeof value === 'object' && value.name) {
				out.push({ name: String(value.name), value: String(value.value ?? '') });
			} else {
				out.push({ name: String(name), value: String(value ?? '') });
			}
		}
	}
	return out;
}

async function applyCapSolverCookies(solution, websiteURL) {
	const list = normalizeCapSolverCookies(solution?.cookies);
	const hasClearance = list.some((c) => c.name === 'cf_clearance');
	if (!hasClearance && solution?.token) {
		list.push({ name: 'cf_clearance', value: String(solution.token) });
	}
	if (!list.length) return false;

	let apex;
	try {
		apex = new URL(websiteURL).hostname.replace(/^www\./, '');
	} catch {
		apex = 'upwork.com';
	}

	// Set on both the apex domain and the exact www host so cf_clearance is
	// matched regardless of which host the challenge/search page is served on.
	const targets = [
		{ url: `https://www.${apex}/`, domain: `.${apex}` },
		{ url: `https://${apex}/`, domain: `.${apex}` },
	];

	let applied = 0;
	for (const cookie of list) {
		for (const t of targets) {
			const ok = await chrome.cookies.set({
				url: t.url,
				name: cookie.name,
				value: cookie.value,
				domain: t.domain,
				path: '/',
				secure: true,
				httpOnly: cookie.name === 'cf_clearance',
				sameSite: 'no_restriction',
			}).then((c) => !!c).catch(() => false);
			if (ok) applied += 1;
		}
	}

	const names = list.map((c) => c.name);
	console.log(
		`[LeadCliq] CapSolver cookies applied: ${names.join(', ') || '(none)'} ` +
			`(cf_clearance=${names.includes('cf_clearance')}, sets=${applied})`
	);
	return applied > 0;
}

async function solveCloudflareOnTab(tabId, campaign) {
	scanState.status = 'Cloudflare detected — solving via CapSolver…';
	await broadcastStatus();

	await sleep(1000);
	const payloadRes = await sendToTab(tabId, { type: 'SCAN_CHALLENGE_PAYLOAD' });
	if (!payloadRes?.ok || !payloadRes.payload) {
		throw new Error('Failed to read Cloudflare challenge page');
	}

	const { websiteURL, userAgent, html } = payloadRes.payload;
	const solved = await apiFetch('api/bot/capsolver', {
		method: 'POST',
		body: {
			team_id: scanState.teamId,
			code: scanState.profileCode,
			websiteURL,
			userAgent,
			html,
		},
	});

	const solution = solved.data;
	const diag = solved.diagnostics || {};
	scanState.lastCapSolverDiag = diag;

	const clearanceCookies = normalizeCapSolverCookies(solution?.cookies);
	const hasClearance =
		clearanceCookies.some((c) => c.name === 'cf_clearance') || !!solution?.token;
	if (!hasClearance) {
		// CapSolver answered but returned only a Turnstile token / no clearance
		// cookie — a cf_clearance is required to pass an interstitial challenge.
		scanState.status =
			'CapSolver returned no cf_clearance cookie (wrong challenge type / IP mismatch)';
		await broadcastStatus();
		throw new Error('CapSolver returned no cf_clearance cookie');
	}

	if (diag.userAgentMatches === false) {
		console.warn(
			`[LeadCliq] CapSolver UA mismatch — solution UA differs from browser UA. cf_clearance will be rejected on reload.`
		);
	}

	const applied = await applyCapSolverCookies(solution, websiteURL);
	if (!applied) {
		throw new Error('CapSolver returned no cookies');
	}

	scanState.status = `CapSolver solved (cf_clearance ${diag.hasCfClearance ? 'ok' : 'via token'}) — reloading…`;
	await broadcastStatus();

	const targetUrl = campaign?.search_url || websiteURL;
	await chrome.tabs.update(tabId, { url: targetUrl });
	await waitTabComplete(tabId);

	// Give the browser time to run Cloudflare's JS challenge with the fresh
	// cookies. A managed challenge often clears itself here (and mints a valid
	// cf_clearance for the browser's own exit IP) even if CapSolver's cookie
	// was tied to a slightly different session.
	scanState.status = 'CapSolver solved — verifying challenge cleared…';
	await broadcastStatus();
	const post = await sendToTab(tabId, {
		type: 'SCAN_PAGE_STATE',
		timeoutMs: 30000,
		cfHitsNeeded: 12,
	}).catch(() => null);
	return post?.state === 'jobs';
}

async function notifyLoginRequired(campaign) {
	chrome.notifications.create(`login-${Date.now()}`, {
		type: 'basic',
		iconUrl: 'icons/icon128.png',
		title: 'LeadCliq — Upwork logged out',
		message: 'Auto Bid stopped. Log into Upwork, then start scan again.',
		priority: 2,
	});

	try {
		await apiFetch('api/bot/alert', {
			method: 'POST',
			body: {
				team_id: scanState.teamId,
				code: scanState.profileCode,
				campaignId: campaign?.id || null,
				type: 'upwork_logged_out',
			},
		});
	} catch (_) {
		// webhook may be missing; still stop the scan
	}

	await stopScan('Stopped — Upwork logged out. Log in, then press Start Scan.');
}

/**
 * Wait for jobs list; solve Cloudflare via server CapSolver if needed.
 */
async function ensureJobsReady(tabId, campaign, { maxSolveAttempts = 3 } = {}) {
	for (let attempt = 0; attempt <= maxSolveAttempts; attempt++) {
		if (!scanState?.running) return false;

		// After a solve attempt, be patient: let the browser clear the managed
		// challenge on its own before deciding to spend another CapSolver task.
		const isRetry = attempt > 0;
		const stateRes = await sendToTab(tabId, {
			type: 'SCAN_PAGE_STATE',
			timeoutMs: isRetry ? 45000 : 35000,
			cfHitsNeeded: isRetry ? 12 : 3,
		});
		const state = stateRes?.state;

		if (state === 'jobs') {
			if (scanState?.running) {
				await sendToTab(tabId, { type: 'SCAN_MARK_TAB' }).catch(() => {});
			}
			return true;
		}

		if (state === 'login_required') {
			await notifyLoginRequired(campaign);
			return false;
		}

		if (state !== 'cloudflare') {
			scanState.status = `Jobs not ready (${state || 'timeout'})`;
			await broadcastStatus();
			return false;
		}

		if (attempt >= maxSolveAttempts) {
			const d = scanState.lastCapSolverDiag || {};
			let reason = '';
			if (d.userAgentMatches === false) {
				reason = ' — UA mismatch (CapSolver used a different User-Agent)';
			} else if (d.hasCfClearance === false) {
				reason = ' — no cf_clearance returned';
			} else if (d.expectedIp) {
				reason = ` — cookie rejected (likely proxy exit-IP ≠ ${d.expectedIp})`;
			}
			scanState.status = `Cloudflare persisted after CapSolver attempts${reason}`;
			await broadcastStatus();
			return false;
		}

		scanState.status = `Cloudflare challenge — CapSolver attempt ${attempt + 1}/${maxSolveAttempts}…`;
		await broadcastStatus();
		const cleared = await solveCloudflareOnTab(tabId, campaign);
		if (cleared) {
			if (scanState?.running) {
				await sendToTab(tabId, { type: 'SCAN_MARK_TAB' }).catch(() => {});
			}
			return true;
		}
	}
	return false;
}

async function settleAfterCampaignNavigation(tabId) {
	// Fixed settle so the SPA finishes rendering before first job dialog open
	scanState.status = `Page loaded — waiting ${formatWait(PAGE_LOAD_SETTLE_MS)} before first action…`;
	await broadcastStatus();
	await sleep(PAGE_LOAD_SETTLE_MS);
	if (scanState?.running) {
		await sendToTab(tabId, { type: 'SCAN_MARK_TAB' }).catch(() => {});
	}
}

async function ensureScanTab(searchUrl) {
	const reusableId = await findReusableScanTab();
	if (reusableId) {
		scanState.tabId = reusableId;
		await chrome.tabs.update(reusableId, {
			url: searchUrl,
			active: false,
			pinned: true,
		}).catch(async () => {
			await chrome.tabs.update(reusableId, { url: searchUrl, active: false });
		});
		await persistScanSnapshot();
		await waitTabComplete(reusableId);
		await settleAfterCampaignNavigation(reusableId);
		await broadcastStatus();
		return reusableId;
	}

	const tab = await chrome.tabs.create({ url: searchUrl, active: false, pinned: true });
	scanState.tabId = tab.id;
	await persistScanSnapshot();
	await waitTabComplete(tab.id);
	await settleAfterCampaignNavigation(tab.id);
	await broadcastStatus();
	return tab.id;
}

async function focusScanTab() {
	const tabId = scanState?.tabId || (await findReusableScanTab());
	if (!tabId) throw new Error('No scan tab yet — start a scan first');
	const tab = await chrome.tabs.get(tabId);
	await chrome.tabs.update(tabId, { active: true });
	if (tab.windowId) {
		await chrome.windows.update(tab.windowId, { focused: true });
	}
	return tabId;
}

async function processCampaignPass(campaign) {
	scanState.status = `Opening: ${campaign.title}`;
	await broadcastStatus();

	const tabId = await ensureScanTab(campaign.search_url);

	scanState.status = `Preparing jobs — ${campaign.title} (tab #${tabId})`;
	await broadcastStatus();

	const ready = await ensureJobsReady(tabId, campaign);
	if (!ready) {
		// Stopped for logout (or scan halted) — do not retry as a generic error
		if (!scanState?.running) return;
		throw new Error(scanState.status || 'Jobs page not ready');
	}

	scanState.status = `Listing jobs — ${campaign.title}`;
	await broadcastStatus();

	const listRes = await sendToTab(tabId, { type: 'SCAN_LIST_JOBS' });
	if (!listRes?.ok) {
		if (listRes?.code === 'LOGIN_REQUIRED') {
			await notifyLoginRequired(campaign);
			return;
		}
		throw new Error(listRes?.error || 'Failed to list jobs');
	}

	const jobs = (listRes.jobs || [])
		.map((j) => ({ ...j, jobId: String(j.jobId || '').trim() }))
		.filter((j) => j.jobId);

	if (jobs.length === 0) {
		scanState.status = `No jobs listed on page — ${campaign.title}`;
		await broadcastStatus();
		return;
	}

	const recentSet = new Set((scanState.recentScanned || []).map(String));
	const newJobs = jobs.filter((j) => !j.isApplied && !recentSet.has(j.jobId));
	const knownJobs = jobs.filter((j) => !j.isApplied && recentSet.has(j.jobId));

	scanState.status = `Found ${jobs.length} jobs (${newJobs.length} new, ${knownJobs.length} in DB) — ${campaign.title}`;
	await broadcastStatus();

	let extracts = 0;

	// 1) Jobs already in DB: run campaign analysis if missing (no re-extract)
	for (const job of knownJobs) {
		if (!scanState?.running) return;

		let existing = scanState.recentAnalysis.find(
			(a) => String(a.job_uid) === job.jobId && Number(a.campaign_id) === Number(campaign.id)
		);

		if (!existing) {
			scanState.status = `Analyzing known job ${job.jobId} — ${campaign.title}`;
			await broadcastStatus();
			try {
				const analysis = await apiFetch('api/bot/analysis', {
					method: 'POST',
					body: {
						team_id: scanState.teamId,
						code: scanState.profileCode,
						jobId: job.jobId,
						campaignId: campaign.id,
					},
				});
				existing = {
					job_uid: job.jobId,
					campaign_id: campaign.id,
					is_matched: !!(analysis && analysis.is_matched),
					is_applied: !!(analysis && analysis.is_applied),
				};
				scanState.recentAnalysis.unshift(existing);
				scanState.stats.scanned += 1;
			} catch (e) {
				scanState.stats.errors += 1;
				scanState.status = `Analyze failed ${job.jobId}: ${e.message || e}`;
				await broadcastStatus();
				continue;
			}
		}

		if (existing?.is_matched && !existing?.is_applied) {
			scanState.lastMatch = {
				jobId: job.jobId,
				campaignId: campaign.id,
				title: campaign.title,
				at: new Date().toISOString(),
				url: job.url,
			};
			scanState.stats.matched += 1;
			notifyMatch({
				title: job.url,
				jobUrl: job.url,
				campaignTitle: campaign.title,
				profileTitle: scanState.profileTitle,
			});
			scanState.status = `Match (in DB): ${job.jobId}`;
			await broadcastStatus();
		}
	}

	// 2) New jobs: open details → push to backend → analyze
	for (const job of newJobs) {
		if (!scanState?.running) return;
		if (extracts >= MAX_EXTRACTS_PER_PASS) {
			scanState.status = `Pass limit ${MAX_EXTRACTS_PER_PASS} extracts — will continue next round`;
			await broadcastStatus();
			break;
		}

		scanState.status = `Extracting new job ${job.jobId} (${extracts + 1}/${Math.min(MAX_EXTRACTS_PER_PASS, newJobs.length)}) — ${campaign.title}`;
		await broadcastStatus();

		const extractRes = await sendToTab(tabId, { type: 'SCAN_EXTRACT_JOB', job });
		if (!extractRes?.ok || !extractRes.details) {
			if (extractRes?.code === 'LOGIN_REQUIRED') {
				await notifyLoginRequired(campaign);
				return;
			}
			scanState.stats.errors += 1;
			scanState.status = `Extract miss: ${job.jobId}${extractRes?.error ? ` (${extractRes.error})` : ''}`;
			await broadcastStatus();
			continue;
		}

		const details = {
			...extractRes.details,
			id: String(extractRes.details.id || job.jobId),
			url: extractRes.details.url || job.url,
		};

		scanState.status = `Pushing job ${details.id} to backend…`;
		await broadcastStatus();

		try {
			// Same as Leadcliq.sendJob → BotController::job → upwork_jobs
			await apiFetch('api/bot/job', {
				method: 'POST',
				body: {
					...details,
					team_id: scanState.teamId,
					code: scanState.profileCode,
				},
			});
		} catch (e) {
			scanState.stats.errors += 1;
			scanState.status = `Push failed ${details.id}: ${e.message || e}`;
			await broadcastStatus();
			continue;
		}

		scanState.recentScanned.push(details.id);
		recentSet.add(details.id);

		let analysis = { is_matched: false };
		try {
			analysis = await apiFetch('api/bot/analysis', {
				method: 'POST',
				body: {
					team_id: scanState.teamId,
					code: scanState.profileCode,
					jobId: details.id,
					campaignId: campaign.id,
				},
			});
		} catch (e) {
			scanState.stats.errors += 1;
			scanState.status = `Pushed ${details.id}, analyze failed: ${e.message || e}`;
			await broadcastStatus();
			extracts += 1;
			await sleep(randomMs(BETWEEN_JOB_MIN_MS, BETWEEN_JOB_MAX_MS));
			continue;
		}

		scanState.recentAnalysis.unshift({
			job_uid: details.id,
			campaign_id: campaign.id,
			is_matched: !!analysis.is_matched,
			is_applied: !!analysis.is_applied,
		});
		scanState.stats.scanned += 1;
		extracts += 1;

		if (analysis.is_matched) {
			scanState.stats.matched += 1;
			scanState.lastMatch = {
				jobId: details.id,
				campaignId: campaign.id,
				title: details.rawText?.split('\n')?.[0] || job.url,
				at: new Date().toISOString(),
				url: job.url,
			};
			notifyMatch({
				title: scanState.lastMatch.title,
				jobUrl: job.url,
				campaignTitle: campaign.title,
				profileTitle: scanState.profileTitle,
			});
			scanState.status = `Matched ${details.id} — webhook fired if configured`;
		} else {
			scanState.status = `Pushed + analyzed ${details.id} (no match)`;
		}
		await broadcastStatus();

		// Phase 1: do NOT apply — stay on list and continue
		await sleep(randomMs(BETWEEN_JOB_MIN_MS, BETWEEN_JOB_MAX_MS));
	}

	if (extracts === 0 && newJobs.length > 0) {
		scanState.status = `Listed ${jobs.length} jobs, ${newJobs.length} new — all extracts missed`;
		await broadcastStatus();
	} else if (newJobs.length === 0) {
		scanState.status = `Listed ${jobs.length} jobs — all already in DB for this pass`;
		await broadcastStatus();
	}
}

async function runScanLoop() {
	if (!scanState?.running) return;

	try {
		// Re-verify egress IP still matches profile proxy
		if (scanState.expectedIp) {
			try {
				const browserIp = await fetchBrowserEgressIp();
				scanState.browserIp = browserIp;
				if (browserIp !== scanState.expectedIp) {
					await stopScan(
						`Stopped — proxy IP changed (browser ${browserIp} ≠ profile ${scanState.expectedIp})`
					);
					return;
				}
			} catch (ipErr) {
				await stopScan(`Stopped — could not verify proxy IP: ${ipErr.message || ipErr}`);
				return;
			}
		}

		scanState.status = 'Refreshing campaigns…';
		await broadcastStatus();

		const meta = await refreshMeta(scanState.teamId, scanState.profileCode);
		scanState.recentScanned = meta.recentScanned;
		scanState.recentAnalysis = meta.recentAnalysis;
		scanState.campaigns = meta.campaigns;

		if (!scanState.campaigns.length) {
			scanState.status = `No active campaigns — waiting ${formatWait(EMPTY_CAMPAIGNS_MS)}…`;
			await broadcastStatus();
			await scheduleNextTick(EMPTY_CAMPAIGNS_MS);
			return;
		}

		if (scanState.campaignIndex >= scanState.campaigns.length) {
			scanState.campaignIndex = 0;
			scanState.stats.passes += 1;
		}

		const campaign = scanState.campaigns[scanState.campaignIndex];
		await processCampaignPass(campaign);

		if (!scanState?.running) return;

		scanState.campaignIndex += 1;

		// upbot2 engine: randomDelay(1*60, 5*60) after every campaign
		let waitMs = randomMs(BETWEEN_CAMPAIGN_MIN_MS, BETWEEN_CAMPAIGN_MAX_MS);

		// upbot2: extra randomDelay(1*60, 5*60) every 2–5 campaigns
		if (typeof scanState.campaignsUntilBreak !== 'number') {
			scanState.campaignsUntilBreak = randomInt(2, 5);
		}
		scanState.campaignsUntilBreak -= 1;
		let longBreak = false;
		if (scanState.campaignsUntilBreak <= 0) {
			waitMs += randomMs(BETWEEN_CAMPAIGN_MIN_MS, BETWEEN_CAMPAIGN_MAX_MS);
			scanState.campaignsUntilBreak = randomInt(2, 5);
			longBreak = true;
		}

		scanState.status = longBreak
			? `Long break before next campaign (${formatWait(waitMs)})…`
			: `Waiting before next campaign (${formatWait(waitMs)})…`;
		await broadcastStatus();
		await persistScanSnapshot();
		await scheduleNextTick(waitMs);
	} catch (e) {
		if (scanState) {
			scanState.stats.errors += 1;
			scanState.status = `Error: ${e.message || e}`;
			await broadcastStatus();
			if (scanState.running) {
				// Match upbot2 retry pacing (~1–5 min) when a campaign fails
				await scheduleNextTick(randomMs(BETWEEN_CAMPAIGN_MIN_MS, BETWEEN_CAMPAIGN_MAX_MS));
			}
		}
	}
}

async function scheduleNextTick(delayMs) {
	await chrome.alarms.clear(SCAN_ALARM);
	chrome.alarms.create(SCAN_ALARM, { when: Date.now() + delayMs });
}

async function startScan({ teamId, profileCode, profileTitle }) {
	if (scanState?.running) {
		throw new Error('Scan already running');
	}

	const reusableTabId = await findReusableScanTab();

	await chrome.storage.local.set({
		teamId: Number(teamId),
		profileCode,
	});

	scanState = {
		running: true,
		teamId: Number(teamId),
		profileCode,
		profileTitle: profileTitle || profileCode,
		tabId: reusableTabId,
		campaignIndex: 0,
		campaignsUntilBreak: randomInt(2, 5),
		campaigns: [],
		recentScanned: [],
		recentAnalysis: [],
		status: 'Enabling profile proxy…',
		lastMatch: null,
		stats: { scanned: 0, matched: 0, passes: 0, errors: 0 },
		expectedIp: null,
		browserIp: null,
	};
	await broadcastStatus();

	try {
		const match = await ensureScanProxy(teamId, profileCode);
		scanState.expectedIp = match.expectedIp;
		scanState.browserIp = match.browserIp;
		scanState.status = `Proxy OK (${match.browserIp}) — waiting ${formatWait(SCAN_START_WAIT_MS)}…`;
		await broadcastStatus();
	} catch (e) {
		// Keep sticky proxy selection; only fail the scan start
		scanState.running = false;
		scanState.status = e.message || String(e);
		await broadcastStatus();
		await persistScanSnapshot();
		throw e;
	}

	await sleep(SCAN_START_WAIT_MS);
	if (!scanState?.running) return;

	scanState.status = 'Starting scan…';
	await broadcastStatus();
	await runScanLoop();
}

async function stopScan(statusMessage = 'Stopped') {
	await chrome.alarms.clear(SCAN_ALARM);
	const tabId = scanState?.tabId || null;
	// Proxy stays active — managed separately via Enable/Disable Proxy
	if (scanState) {
		scanState.running = false;
		scanState.status = statusMessage;
		await broadcastStatus();
		await persistScanSnapshot();
	} else {
		await chrome.storage.local.remove('scanState');
	}
	// Restore normal Upwork tab title when auto bid is off
	if (tabId) {
		await sendToTab(tabId, { type: 'SCAN_UNMARK_TAB' }).catch(() => {});
	}
}

chrome.alarms.onAlarm.addListener((alarm) => {
	if (alarm.name !== SCAN_ALARM) return;
	if (!scanState?.running) return;
	runScanLoop();
});

chrome.runtime.onMessage.addListener((msg, _sender, sendResponse) => {
	(async () => {
		try {
			switch (msg.type) {
				case 'EXT_LOGIN': {
					const data = await apiFetch('api/bot/login', {
						method: 'POST',
						auth: false,
						body: { email: msg.email, password: msg.password },
					});
					const teams = data.data || [];
					const defaultTeamId = teams.length > 0 ? teams[0].id : null;
					await setAuth({ token: data.token, teamId: defaultTeamId, teams });
					sendResponse({ ok: true, data: { ...data, teams, team_id: defaultTeamId } });
					break;
				}
				case 'EXT_LOGOUT': {
					await stopScan();
					await disableProxy();
					try {
						await apiFetch('api/bot/logout', { method: 'POST', auth: false });
					} catch (_) {}
					await clearAuth();
					sendResponse({ ok: true });
					break;
				}
				case 'EXT_GET_TEAMS': {
					const storage = await getSettings();
					sendResponse({ ok: true, data: { teams: storage.teams || [] } });
					break;
				}
				case 'EXT_SWITCH_TEAM': {
					await new Promise((resolve) => {
						chrome.storage.local.set({ teamId: msg.teamId }, resolve);
					});
					sendResponse({ ok: true });
					break;
				}
				case 'EXT_DASHBOARD': {
					const storage = await getSettings();
					const data = await apiFetch('api/bot/dashboard', {
						method: 'POST',
						body: { team_id: storage.teamId || undefined },
					});
					sendResponse({ ok: true, data: data.data });
					break;
				}
				case 'EXT_PROFILES': {
					const data = await apiFetch('api/bot/profiles', {
						method: 'POST',
						body: { team_id: msg.team_id },
					});
					sendResponse({ ok: true, data: data.data });
					break;
				}
				case 'EXT_CAMPAIGNS': {
					const data = await apiFetch('api/bot/campaigns', {
						method: 'POST',
						body: { team_id: msg.team_id, code: msg.code },
					});
					sendResponse({ ok: true, data: data.data });
					break;
				}
				case 'EXT_CAMPAIGN_COVERLETTER': {
					const data = await apiFetch('api/bot/coverletter', {
						method: 'POST',
						body: {
							team_id: msg.team_id,
							campaign_id: msg.campaign_id,
							job_description: msg.job_description,
							title: msg.title || undefined,
							client_name: msg.client_name || undefined,
							questions: msg.questions || undefined,
						},
					});
					sendResponse({ ok: true, data });
					break;
				}
				case 'EXT_SCRAPE_COVER_LETTER_JOB': {
					const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
					if (!tab?.id) {
						sendResponse({ ok: false, error: 'No active tab' });
						break;
					}
					if (!tab.url || !tab.url.includes('upwork.com')) {
						sendResponse({ ok: false, error: 'Open an Upwork job page first' });
						break;
					}
					const res = await sendToTab(tab.id, { type: 'SCAN_COVER_LETTER_JOB' });
					if (!res?.ok) {
						sendResponse({ ok: false, error: res?.error || 'Could not read job from page' });
						break;
					}
					sendResponse({ ok: true, data: res.job });
					break;
				}
				case 'EXT_APPLY_COVER_LETTER': {
					const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
					if (!tab?.id) {
						sendResponse({ ok: false, error: 'No active tab' });
						break;
					}
					const res = await sendToTab(tab.id, {
						type: 'SCAN_APPLY_COVER_LETTER',
						coverLetter: msg.coverLetter,
						questions: msg.questions || [],
					});
					if (!res?.ok) {
						sendResponse({ ok: false, error: res?.error || 'Could not fill cover letter' });
						break;
					}
					sendResponse({
						ok: true,
						questionsFilled: res.questionsFilled || 0,
						terms: res.terms || null,
					});
					break;
				}
				case 'SCAN_START': {
					if (scanState?.running) {
						sendResponse({ ok: false, error: 'Scan already running' });
						break;
					}
					startScan({
						teamId: msg.team_id,
						profileCode: msg.code,
						profileTitle: msg.profile_title,
					}).catch((err) => {
						if (scanState) {
							scanState.running = false;
							scanState.status = `Error: ${err.message || err}`;
							broadcastStatus();
						}
					});
					sendResponse({ ok: true });
					break;
				}
				case 'SCAN_STOP': {
					await stopScan();
					sendResponse({ ok: true });
					break;
				}
				case 'SCAN_GET_STATUS': {
					const proxy = await getProxyStatusPayload();
					const payload = scanState
						? {
								running: scanState.running,
								status: scanState.status,
								profileTitle: scanState.profileTitle,
								profileCode: scanState.profileCode,
								teamId: scanState.teamId,
								tabId: scanState.tabId,
								stats: scanState.stats,
								lastMatch: scanState.lastMatch,
								campaign: scanState.campaigns[scanState.campaignIndex] || null,
								campaignCount: scanState.campaigns.length,
								browserIp: proxy.browserIp || scanState.browserIp || null,
								expectedIp: proxy.expectedIp || scanState.expectedIp || null,
								proxyOk: proxy.matched,
								proxyEnabled: proxy.enabled,
								proxyProfileCode: proxy.code,
								proxyProfileTitle: proxy.title,
							}
						: {
								running: false,
								status: 'Idle',
								tabId: null,
								proxyOk: proxy.matched,
								proxyEnabled: proxy.enabled,
								browserIp: proxy.browserIp,
								expectedIp: proxy.expectedIp,
								proxyProfileCode: proxy.code,
								proxyProfileTitle: proxy.title,
							};
					sendResponse({ ok: true, data: payload });
					break;
				}
				case 'EXT_ENABLE_PROXY': {
					const teamId = msg.team_id;
					const code = msg.code;
					if (!teamId || !code) {
						sendResponse({ ok: false, error: 'Select a team and profile first' });
						break;
					}
					const match = await enableProxyForProfile(teamId, code);
					sendResponse({
						ok: true,
						data: {
							browserIp: match.browserIp,
							expectedIp: match.expectedIp,
							matched: true,
							enabled: true,
							title: match.title,
							code,
						},
					});
					break;
				}
				case 'EXT_DISABLE_PROXY': {
					await disableProxy();
					sendResponse({ ok: true, data: { enabled: false } });
					break;
				}
				case 'EXT_GET_PROXY_STATUS': {
					sendResponse({ ok: true, data: await getProxyStatusPayload() });
					break;
				}
				case 'EXT_CHECK_PROXY': {
					// Alias: enable + verify sticky profile proxy
					const teamId = msg.team_id;
					const code = msg.code;
					if (!teamId || !code) {
						sendResponse({ ok: false, error: 'team_id and code required' });
						break;
					}
					try {
						const match = await enableProxyForProfile(teamId, code);
						sendResponse({
							ok: true,
							data: {
								browserIp: match.browserIp,
								expectedIp: match.expectedIp,
								matched: true,
								enabled: true,
								title: match.title,
							},
						});
					} catch (e) {
						sendResponse({ ok: false, error: e.message || String(e) });
					}
					break;
				}
				case 'SCAN_FOCUS_TAB': {
					const tabId = await focusScanTab();
					sendResponse({ ok: true, data: { tabId } });
					break;
				}
				default:
					sendResponse({ ok: false, error: 'Unknown message type' });
			}
		} catch (e) {
			sendResponse({ ok: false, error: e.message || String(e) });
		}
	})();
	return true;
});

// Restore sticky proxy + running scan after SW wake
chrome.storage.local.get(
	['scanState', 'proxyOwnedByExtension', 'proxyAuth', 'activeProxyProfile'],
	async (data) => {
		proxyOwnedByExtension = !!data.proxyOwnedByExtension;
		if (data.proxyAuth?.username != null) {
			activeProxyAuth = {
				username: String(data.proxyAuth.username),
				password: String(data.proxyAuth.password ?? ''),
			};
		}

		const sticky = data.activeProxyProfile;
		if (sticky?.enabled && sticky.teamId && sticky.code) {
			try {
				await enableProxyForProfile(sticky.teamId, sticky.code);
			} catch (e) {
				console.warn('[LeadCliq] Failed to restore sticky proxy:', e.message || e);
			}
		}

		if (data.scanState?.running && !scanState) {
			scanState = {
				...data.scanState,
				recentScanned: [],
				recentAnalysis: [],
				campaigns: data.scanState.campaigns || [],
			};
			try {
				if (scanState.teamId && scanState.profileCode) {
					const match = await ensureScanProxy(scanState.teamId, scanState.profileCode);
					scanState.expectedIp = match.expectedIp;
					scanState.browserIp = match.browserIp;
				}
				scheduleNextTick(3000);
			} catch (e) {
				await stopScan(`Stopped — proxy check failed after restart: ${e.message || e}`);
			}
		}
	}
);

chrome.tabs.onRemoved.addListener((tabId) => {
	if (scanState?.tabId === tabId) {
		scanState.tabId = null;
		scanState.status = 'Scan tab closed';
		broadcastStatus();
	}
});
