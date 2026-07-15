function showStatus(message, type = 'info') {
	const el = document.getElementById('result');
	el.textContent = message;
	el.className = `status ${type}`;
}

function hideStatus() {
	const el = document.getElementById('result');
	el.textContent = '';
	el.className = '';
}

function showFieldError(fieldId, message) {
	const field = document.getElementById(fieldId);
	const err = document.getElementById(fieldId + 'Error');
	if (field) field.classList.add('error');
	if (err) {
		err.textContent = message;
		err.classList.add('show');
	}
}

function clearFieldError(fieldId) {
	const field = document.getElementById(fieldId);
	const err = document.getElementById(fieldId + 'Error');
	if (field) field.classList.remove('error');
	if (err) {
		err.textContent = '';
		err.classList.remove('show');
	}
}

function validateLoginForm() {
	const email = document.getElementById('email').value.trim();
	const password = document.getElementById('password').value;
	let ok = true;
	clearFieldError('email');
	clearFieldError('password');
	if (!email) {
		showFieldError('email', 'Email is required');
		ok = false;
	} else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
		showFieldError('email', 'Enter a valid email');
		ok = false;
	}
	if (!password) {
		showFieldError('password', 'Password is required');
		ok = false;
	} else if (password.length < 6) {
		showFieldError('password', 'At least 6 characters');
		ok = false;
	}
	return ok;
}

const POPUP_TABS = ['dashboard', 'proxy', 'autobid', 'cover', 'account'];

function switchTab(tabName) {
	if (!POPUP_TABS.includes(tabName)) tabName = 'dashboard';
	document.querySelectorAll('.tab-content').forEach((c) => c.classList.remove('active'));
	document.querySelectorAll('.tab').forEach((t) => t.classList.remove('active'));
	document.getElementById(`tab-${tabName}`)?.classList.add('active');
	document.querySelector(`.tab[data-tab="${tabName}"]`)?.classList.add('active');
	chrome.storage.local.set({ lastPopupTab: tabName });
}

function initTabs() {
	document.querySelectorAll('.tab').forEach((tab) => {
		tab.addEventListener('click', () => switchTab(tab.getAttribute('data-tab')));
	});
}

async function restoreLastTab() {
	const { lastPopupTab } = await chrome.storage.local.get(['lastPopupTab']);
	if (lastPopupTab && POPUP_TABS.includes(lastPopupTab)) {
		switchTab(lastPopupTab);
	}
}

async function checkAuth() {
	const settings = await chrome.storage.local.get(['token', 'teams', 'teamId', 'profileCode']);
	if (settings.token) {
		document.getElementById('loginSection').classList.add('hidden');
		document.getElementById('tabsContainer').classList.remove('hidden');
		await loadTeams();
		await loadProxyTeams();
		if (!(await restoreCoverDraft())) {
			await loadCoverTeams();
		}
		await loadDashboard();
		await refreshProxyStatus();
		await refreshScanStatus();
		await restoreLastTab();
	} else {
		document.getElementById('loginSection').classList.remove('hidden');
		document.getElementById('tabsContainer').classList.add('hidden');
	}
}

async function loadTeams() {
	const settings = await chrome.storage.local.get(['teamId', 'teams', 'profileCode']);
	const teamSelect = document.getElementById('teamSelect');
	teamSelect.innerHTML = '<option value="">Select team…</option>';
	(settings.teams || []).forEach((team) => {
		const opt = new Option(team.name, team.id);
		if (String(team.id) === String(settings.teamId)) opt.selected = true;
		teamSelect.appendChild(opt);
	});
	if (settings.teamId) {
		await loadProfiles(settings.teamId, settings.profileCode || null);
	}
}

async function loadProxyTeams() {
	const settings = await chrome.storage.local.get(['teamId', 'teams', 'profileCode']);
	const teamSelect = document.getElementById('proxyTeamSelect');
	teamSelect.innerHTML = '<option value="">Select team…</option>';
	(settings.teams || []).forEach((team) => {
		const opt = new Option(team.name, team.id);
		if (String(team.id) === String(settings.teamId)) opt.selected = true;
		teamSelect.appendChild(opt);
	});
	if (settings.teamId) {
		await loadProxyProfiles(settings.teamId, settings.profileCode || null);
	}
}

async function loadProxyProfiles(teamId, preferredCode = null) {
	const profileSelect = document.getElementById('proxyProfileSelect');
	const enableBtn = document.getElementById('enableProxyBtn');
	const refreshBtn = document.getElementById('refreshProxyBtn');
	profileSelect.innerHTML = '<option value="">Loading…</option>';
	profileSelect.disabled = true;
	enableBtn.disabled = true;
	refreshBtn.disabled = true;

	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_PROFILES',
			team_id: Number(teamId),
		});
		if (!resp.ok) throw new Error(resp.error);

		const profiles = resp.data || [];
		profileSelect.innerHTML = '<option value="">Select profile…</option>';
		profiles.forEach((p) => {
			const ip = p.proxy_last_ip ? ` · ${p.proxy_last_ip}` : '';
			const noProxy = p.has_proxy === false ? ' · no proxy' : '';
			profileSelect.appendChild(new Option(`${p.title} (${p.code})${ip}${noProxy}`, p.code));
		});
		profileSelect.disabled = false;

		const codeToSelect = preferredCode || '';
		if (codeToSelect && [...profileSelect.options].some((o) => o.value === String(codeToSelect))) {
			profileSelect.value = String(codeToSelect);
		}
		enableBtn.disabled = !profileSelect.value;
		refreshBtn.disabled = !profileSelect.value;
		await refreshProxyStatus();
	} catch (e) {
		profileSelect.innerHTML = '<option value="">Failed to load</option>';
		showStatus(e.message, 'error');
	}
}

const COVER_DRAFT_KEY = 'coverDraft';
let coverDraftSaveTimer = null;
/** Skip auto-save while restoring a draft into the form */
let coverDraftRestoring = false;

/** @type {string[]} Questions scraped from the job / proposal page */
let clScrapedQuestions = [];
/** @type {Array<{ question: string, answer: string }>} AI answers to fill on apply */
let clGeneratedQuestions = [];

function collectCoverDraft() {
	return {
		teamId: document.getElementById('clTeamSelect').value || null,
		profileCode: document.getElementById('clProfileSelect').value || null,
		campaignId: document.getElementById('clCampaignSelect').value || null,
		jobTitle: document.getElementById('clJobTitle').value || '',
		jobDescription: document.getElementById('clJobDescription').value || '',
		coverLetter: document.getElementById('clResult').value || '',
		scrapedQuestions: clScrapedQuestions,
		generatedQuestions: clGeneratedQuestions,
	};
}

function scheduleSaveCoverDraft() {
	if (coverDraftRestoring) return;
	clearTimeout(coverDraftSaveTimer);
	coverDraftSaveTimer = setTimeout(() => {
		chrome.storage.local.set({ [COVER_DRAFT_KEY]: collectCoverDraft() });
	}, 200);
}

async function saveCoverDraftNow() {
	if (coverDraftRestoring) return;
	clearTimeout(coverDraftSaveTimer);
	await chrome.storage.local.set({ [COVER_DRAFT_KEY]: collectCoverDraft() });
}

async function loadCoverTeams(preferredTeamId = null, preferredCode = null, preferredCampaignId = null) {
	const settings = await chrome.storage.local.get(['teamId', 'teams', 'profileCode']);
	const teamSelect = document.getElementById('clTeamSelect');
	const teamId = preferredTeamId || settings.teamId;
	const profileCode = preferredCode ?? settings.profileCode ?? null;
	teamSelect.innerHTML = '<option value="">Select team…</option>';
	(settings.teams || []).forEach((team) => {
		const opt = new Option(team.name, team.id);
		if (String(team.id) === String(teamId)) opt.selected = true;
		teamSelect.appendChild(opt);
	});
	if (teamId) {
		await loadCoverProfiles(teamId, profileCode, preferredCampaignId);
	}
}

async function restoreCoverDraft() {
	const stored = await chrome.storage.local.get([COVER_DRAFT_KEY]);
	const draft = stored[COVER_DRAFT_KEY];
	if (!draft || typeof draft !== 'object') return false;

	coverDraftRestoring = true;
	try {
		await loadCoverTeams(draft.teamId || null, draft.profileCode || null, draft.campaignId || null);

		document.getElementById('clJobTitle').value = draft.jobTitle || '';
		document.getElementById('clJobDescription').value = draft.jobDescription || '';
		clScrapedQuestions = Array.isArray(draft.scrapedQuestions) ? draft.scrapedQuestions : [];
		setCoverResult(draft.coverLetter || '', draft.generatedQuestions || []);
		return true;
	} finally {
		coverDraftRestoring = false;
	}
}

function setCoverResult(coverLetter, questions) {
	const resultEl = document.getElementById('clResult');
	const qaBox = document.getElementById('clQaBox');
	resultEl.value = coverLetter || '';
	document.getElementById('clCopyBtn').disabled = !coverLetter;
	document.getElementById('clApplyBtn').disabled = !coverLetter;

	const rows = Array.isArray(questions) ? questions : [];
	clGeneratedQuestions = rows.filter((row) => row && (row.question || row.answer));

	if (!rows.length) {
		qaBox.classList.add('hidden');
		qaBox.textContent = '';
	} else {
		qaBox.classList.remove('hidden');
		qaBox.textContent = rows
			.map((row) => {
				const q = row?.question || '';
				const a = row?.answer || '';
				return `Q: ${q}\nA: ${a}`;
			})
			.join('\n\n');
	}

	scheduleSaveCoverDraft();
}

async function loadCoverProfiles(teamId, preferredCode = null, preferredCampaignId = null) {
	const profileSelect = document.getElementById('clProfileSelect');
	const campaignSelect = document.getElementById('clCampaignSelect');
	profileSelect.innerHTML = '<option value="">Loading…</option>';
	profileSelect.disabled = true;
	campaignSelect.innerHTML = '<option value="">Select profile first…</option>';
	campaignSelect.disabled = true;

	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_PROFILES',
			team_id: Number(teamId),
		});
		if (!resp.ok) throw new Error(resp.error);

		const profiles = resp.data || [];
		profileSelect.innerHTML = '<option value="">Select profile…</option>';
		profiles.forEach((p) => {
			profileSelect.appendChild(new Option(`${p.title} (${p.code})`, p.code));
		});
		profileSelect.disabled = false;

		const codeToSelect = preferredCode || '';
		if (codeToSelect && [...profileSelect.options].some((o) => o.value === String(codeToSelect))) {
			profileSelect.value = String(codeToSelect);
			await loadCoverCampaigns(teamId, codeToSelect, preferredCampaignId);
		}
	} catch (e) {
		profileSelect.innerHTML = '<option value="">Failed to load</option>';
		showStatus(e.message, 'error');
	}
}

async function loadCoverCampaigns(teamId, code, preferredCampaignId = null) {
	const campaignSelect = document.getElementById('clCampaignSelect');
	campaignSelect.innerHTML = '<option value="">Loading…</option>';
	campaignSelect.disabled = true;

	if (!teamId || !code) {
		campaignSelect.innerHTML = '<option value="">Select profile first…</option>';
		return;
	}

	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_CAMPAIGNS',
			team_id: Number(teamId),
			code,
		});
		if (!resp.ok) throw new Error(resp.error);
		const list = resp.data || [];
		campaignSelect.innerHTML = '<option value="">Select campaign…</option>';
		list.forEach((c) => {
			campaignSelect.appendChild(new Option(c.title, c.id));
		});
		campaignSelect.disabled = list.length === 0;
		if (!list.length) {
			campaignSelect.innerHTML = '<option value="">No campaigns on this profile</option>';
		} else if (
			preferredCampaignId &&
			[...campaignSelect.options].some((o) => o.value === String(preferredCampaignId))
		) {
			campaignSelect.value = String(preferredCampaignId);
		}
	} catch (e) {
		campaignSelect.innerHTML = '<option value="">Failed to load</option>';
		showStatus(e.message, 'error');
	}
}

async function loadDashboard() {
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'EXT_DASHBOARD' });
		if (!resp.ok) throw new Error(resp.error);
		document.getElementById('statTeams').textContent = resp.data.teams ?? '0';
		document.getElementById('statCampaigns').textContent = resp.data.campaigns ?? '0';
		document.getElementById('statProfiles').textContent = resp.data.profiles ?? '0';
	} catch (e) {
		console.error(e);
		showStatus('Dashboard: ' + e.message, 'error');
	}
}

async function loadProfiles(teamId, preferredCode = null) {
	const profileSelect = document.getElementById('profileSelect');
	const startBtn = document.getElementById('startScanBtn');
	profileSelect.innerHTML = '<option value="">Loading…</option>';
	profileSelect.disabled = true;
	startBtn.disabled = true;
	document.getElementById('campaignHint').textContent = 'Loading profiles…';

	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_PROFILES',
			team_id: Number(teamId),
		});
		if (!resp.ok) throw new Error(resp.error);

		const profiles = resp.data || [];
		profileSelect.innerHTML = '<option value="">Select profile…</option>';
		profiles.forEach((p) => {
			const ip = p.proxy_last_ip ? ` · ${p.proxy_last_ip}` : '';
			profileSelect.appendChild(new Option(`${p.title} (${p.code})${ip}`, p.code));
		});
		profileSelect.disabled = false;

		const codeToSelect = preferredCode || profileSelect.value;
		if (codeToSelect && [...profileSelect.options].some((o) => o.value === String(codeToSelect))) {
			profileSelect.value = String(codeToSelect);
			await loadCampaignHint(teamId, codeToSelect);
		} else {
			document.getElementById('campaignHint').textContent =
				profiles.length === 0
					? 'No active profiles for this team.'
					: 'Select a profile, then start scan (proxy must be on via Proxy tab).';
		}
	} catch (e) {
		profileSelect.innerHTML = '<option value="">Failed to load</option>';
		document.getElementById('campaignHint').textContent = e.message;
		showStatus(e.message, 'error');
	}
}

async function loadCampaignHint(teamId, code) {
	const hint = document.getElementById('campaignHint');
	const startBtn = document.getElementById('startScanBtn');
	if (!teamId || !code) {
		startBtn.disabled = true;
		hint.textContent = 'Select a profile to load its campaigns.';
		return;
	}
	hint.textContent = 'Loading campaigns…';
	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_CAMPAIGNS',
			team_id: Number(teamId),
			code,
		});
		if (!resp.ok) throw new Error(resp.error);
		const list = resp.data || [];
		if (!list.length) {
			startBtn.disabled = true;
			hint.textContent = 'No active campaigns with a search URL on this profile.';
			return;
		}
		startBtn.disabled = false;
		const withHook = list.filter((c) => c.has_webhook).length;
		hint.textContent = `${list.length} campaign(s) ready · ${withHook} with webhook`;
	} catch (e) {
		startBtn.disabled = true;
		hint.textContent = e.message;
	}
}

function setBadge(el, text, on) {
	if (!el) return;
	el.textContent = text;
	el.className = `badge ${on ? 'on' : 'off'}`;
}

function renderProxyStatus({
	browserIp,
	expectedIp,
	matched,
	enabled,
	title,
	code,
	error,
} = {}) {
	const label = title || code || '—';
	document.getElementById('proxyBrowserIp').textContent = browserIp || '—';
	document.getElementById('proxyExpectedIp').textContent = expectedIp || '—';
	document.getElementById('proxyActiveProfile').textContent = label;

	const dashIp = document.getElementById('dashProxyIp');
	const dashProfile = document.getElementById('dashProxyProfile');
	const abProfile = document.getElementById('autobidProxyProfile');
	if (dashIp) dashIp.textContent = browserIp || '—';
	if (dashProfile) dashProfile.textContent = label;
	if (abProfile) abProfile.textContent = label;

	const enableBtn = document.getElementById('enableProxyBtn');
	const disableBtn = document.getElementById('disableProxyBtn');
	const refreshBtn = document.getElementById('refreshProxyBtn');
	const hasProfile = !!document.getElementById('proxyProfileSelect')?.value;
	// Enable only when a profile is selected and proxy is not already on
	enableBtn.disabled = !hasProfile || !!enabled;
	refreshBtn.disabled = !hasProfile;
	disableBtn.disabled = !enabled;

	let badgeText = 'Off';
	let badgeOn = false;
	if (error) {
		badgeText = 'Failed';
	} else if (enabled && matched) {
		badgeText = 'On · Matched';
		badgeOn = true;
	} else if (enabled) {
		badgeText = 'On';
		badgeOn = true;
	} else if (matched === false) {
		badgeText = 'Mismatch';
	}

	setBadge(document.getElementById('proxyMatchBadge'), badgeText, badgeOn);
	setBadge(document.getElementById('dashProxyBadge'), badgeText, badgeOn);
	setBadge(document.getElementById('autobidProxyBadge'), badgeText, badgeOn);
}

async function refreshProxyStatus() {
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'EXT_GET_PROXY_STATUS' });
		if (!resp?.ok) return;
		renderProxyStatus({
			browserIp: resp.data.browserIp,
			expectedIp: resp.data.expectedIp,
			matched: resp.data.matched,
			enabled: resp.data.enabled,
			title: resp.data.title,
			code: resp.data.code,
		});

		// Keep Proxy tab selects in sync with active proxy profile
		if (resp.data.enabled && resp.data.teamId && resp.data.code) {
			const teamSelect = document.getElementById('proxyTeamSelect');
			const profileSelect = document.getElementById('proxyProfileSelect');
			if (
				teamSelect &&
				String(teamSelect.value) !== String(resp.data.teamId) &&
				[...teamSelect.options].some((o) => String(o.value) === String(resp.data.teamId))
			) {
				teamSelect.value = String(resp.data.teamId);
				await loadProxyProfiles(resp.data.teamId, resp.data.code);
			} else if (
				profileSelect &&
				resp.data.code &&
				[...profileSelect.options].some((o) => o.value === resp.data.code)
			) {
				profileSelect.value = resp.data.code;
			}
		}
	} catch (_) {}
}

function renderScanStatus(data) {
	const running = !!data?.running;
	const badge = document.getElementById('scanRunningBadge');
	badge.textContent = running ? 'Running' : 'Idle';
	badge.className = `badge ${running ? 'on' : 'off'}`;

	const tabId = data?.tabId || null;
	document.getElementById('scanTabId').textContent = tabId ? `#${tabId}` : '—';
	document.getElementById('focusScanTabBtn').disabled = !tabId;

	document.getElementById('scanStatusText').textContent = data?.status || '—';
	document.getElementById('scanStatScanned').textContent = data?.stats?.scanned ?? 0;
	document.getElementById('scanStatMatched').textContent = data?.stats?.matched ?? 0;
	document.getElementById('scanStatPasses').textContent = data?.stats?.passes ?? 0;

	const lm = data?.lastMatch;
	document.getElementById('scanLastMatch').textContent = lm
		? `${lm.jobId} · ${new Date(lm.at).toLocaleTimeString()}`
		: '—';

	renderProxyStatus({
		browserIp: data?.browserIp,
		expectedIp: data?.expectedIp,
		matched: data?.proxyOk,
		enabled: data?.proxyEnabled,
		title: data?.proxyProfileTitle,
		code: data?.proxyProfileCode,
	});

	// Restore team/profile from active scan (popup is destroyed on close)
	const teamSelect = document.getElementById('teamSelect');
	const profileSelect = document.getElementById('profileSelect');
	if (data?.teamId && String(teamSelect.value) !== String(data.teamId)) {
		if ([...teamSelect.options].some((o) => String(o.value) === String(data.teamId))) {
			teamSelect.value = String(data.teamId);
		}
	}
	if (data?.profileCode) {
		const hasOption = [...profileSelect.options].some((o) => o.value === data.profileCode);
		if (hasOption && profileSelect.value !== data.profileCode) {
			profileSelect.value = data.profileCode;
		} else if (!hasOption && data.profileTitle) {
			const stub = new Option(data.profileTitle, data.profileCode, true, true);
			profileSelect.appendChild(stub);
		}
	}

	teamSelect.disabled = running;
	profileSelect.disabled = running || profileSelect.options.length <= 1;

	document.getElementById('startScanBtn').disabled = running || !profileSelect.value;
	document.getElementById('stopScanBtn').disabled = !running;
}

async function refreshScanStatus() {
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'SCAN_GET_STATUS' });
		if (!resp.ok) return;
		const data = resp.data;

		// If profiles list is empty/stub and we know the scan profile, reload list then re-render
		const profileSelect = document.getElementById('profileSelect');
		const teamId = data?.teamId || document.getElementById('teamSelect').value;
		if (teamId && data?.profileCode) {
			const hasRealOptions = [...profileSelect.options].some(
				(o) => o.value && o.value !== data.profileCode
			) || [...profileSelect.options].some((o) => o.value === data.profileCode && !o.text.includes('Loading'));
			const onlyStubOrEmpty =
				profileSelect.options.length <= 1 ||
				(profileSelect.options.length === 2 && !profileSelect.value);

			if (onlyStubOrEmpty || !hasRealOptions) {
				await loadProfiles(teamId, data.profileCode);
			}
		}

		renderScanStatus(data);
	} catch (_) {}
}

document.getElementById('loginForm').addEventListener('submit', async (e) => {
	e.preventDefault();
	hideStatus();
	if (!validateLoginForm()) {
		showStatus('Please fix the form errors', 'error');
		return;
	}
	const email = document.getElementById('email').value.trim();
	const password = document.getElementById('password').value;
	showStatus('Logging in…', 'info');
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'EXT_LOGIN', email, password });
		if (!resp.ok) throw new Error(resp.error);
		showStatus('Logged in', 'success');
		await checkAuth();
		setTimeout(hideStatus, 1500);
	} catch (err) {
		showStatus('Login error: ' + err.message, 'error');
	}
});

document.getElementById('logoutBtn').addEventListener('click', async () => {
	if (!confirm('Log out of LeadCliq? This stops scanning and clears the proxy.')) return;
	try {
		await chrome.runtime.sendMessage({ type: 'SCAN_STOP' });
		const resp = await chrome.runtime.sendMessage({ type: 'EXT_LOGOUT' });
		if (!resp.ok) throw new Error(resp.error || 'Logout failed');
		showStatus('Logged out', 'success');
		await checkAuth();
	} catch (e) {
		showStatus(e.message, 'error');
	}
});

document.getElementById('goAutobidBtn').addEventListener('click', () => switchTab('autobid'));
document.getElementById('goProxyBtn')?.addEventListener('click', () => switchTab('proxy'));
document.getElementById('goProxyFromAutobid')?.addEventListener('click', () => switchTab('proxy'));

document.getElementById('proxyTeamSelect').addEventListener('change', async function () {
	const teamId = this.value;
	if (!teamId) {
		document.getElementById('proxyProfileSelect').innerHTML = '<option value="">Select team first…</option>';
		document.getElementById('proxyProfileSelect').disabled = true;
		document.getElementById('enableProxyBtn').disabled = true;
		document.getElementById('refreshProxyBtn').disabled = true;
		return;
	}
	await chrome.runtime.sendMessage({ type: 'EXT_SWITCH_TEAM', teamId: Number(teamId) });
	await loadProxyProfiles(teamId);
});

document.getElementById('proxyProfileSelect').addEventListener('change', async function () {
	const code = this.value;
	await chrome.storage.local.set({ profileCode: code || null });
	document.getElementById('refreshProxyBtn').disabled = !code;
	await refreshProxyStatus();
});

document.getElementById('clTeamSelect').addEventListener('change', async function () {
	const teamId = this.value;
	if (!teamId) {
		document.getElementById('clProfileSelect').innerHTML = '<option value="">Select team first…</option>';
		document.getElementById('clProfileSelect').disabled = true;
		document.getElementById('clCampaignSelect').innerHTML = '<option value="">Select profile first…</option>';
		document.getElementById('clCampaignSelect').disabled = true;
		scheduleSaveCoverDraft();
		return;
	}
	await loadCoverProfiles(teamId);
	scheduleSaveCoverDraft();
});

document.getElementById('clProfileSelect').addEventListener('change', async function () {
	const teamId = document.getElementById('clTeamSelect').value;
	await loadCoverCampaigns(teamId, this.value);
	scheduleSaveCoverDraft();
});

document.getElementById('clCampaignSelect').addEventListener('change', () => scheduleSaveCoverDraft());
document.getElementById('clJobTitle').addEventListener('input', () => scheduleSaveCoverDraft());
document.getElementById('clJobDescription').addEventListener('input', () => scheduleSaveCoverDraft());
document.getElementById('clResult').addEventListener('input', () => {
	const text = document.getElementById('clResult').value;
	document.getElementById('clCopyBtn').disabled = !text;
	document.getElementById('clApplyBtn').disabled = !text;
	scheduleSaveCoverDraft();
});

document.getElementById('clFillBtn').addEventListener('click', async () => {
	hideStatus();
	showStatus('Reading job from Upwork…', 'info');
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'EXT_SCRAPE_COVER_LETTER_JOB' });
		if (!resp.ok) throw new Error(resp.error);
		const job = resp.data || {};
		document.getElementById('clJobTitle').value = job.title || '';
		document.getElementById('clJobDescription').value = job.description || '';
		clScrapedQuestions = Array.isArray(job.questions) ? job.questions : [];
		await saveCoverDraftNow();
		if (job.description && job.description.length >= 40) {
			const qn = clScrapedQuestions.length;
			showStatus(
				qn ? `Job filled (${qn} question${qn === 1 ? '' : 's'})` : 'Job description filled',
				'success'
			);
			setTimeout(hideStatus, 1800);
		} else {
			showStatus('Could not find a full job description — paste it manually.', 'error');
		}
	} catch (e) {
		showStatus(e.message, 'error');
	}
});

document.getElementById('clGenerateBtn').addEventListener('click', async () => {
	hideStatus();
	const teamId = document.getElementById('clTeamSelect').value;
	const campaignId = document.getElementById('clCampaignSelect').value;
	const description = document.getElementById('clJobDescription').value.trim();
	const title = document.getElementById('clJobTitle').value.trim();

	if (!teamId) {
		showStatus('Select a team', 'error');
		return;
	}
	if (!campaignId) {
		showStatus('Select a campaign', 'error');
		return;
	}
	if (description.length < 50) {
		showStatus('Job description must be at least 50 characters', 'error');
		return;
	}

	const btn = document.getElementById('clGenerateBtn');
	btn.disabled = true;
	showStatus('Generating cover letter…', 'info');
	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_CAMPAIGN_COVERLETTER',
			team_id: Number(teamId),
			campaign_id: Number(campaignId),
			job_description: description,
			title: title || undefined,
			questions: clScrapedQuestions.length ? clScrapedQuestions : undefined,
		});
		if (!resp.ok) throw new Error(resp.error);
		const data = resp.data || {};
		setCoverResult(data.cover_letter || '', data.questions || []);
		await saveCoverDraftNow();
		showStatus('Cover letter ready', 'success');
		setTimeout(hideStatus, 2000);
	} catch (e) {
		showStatus(e.message, 'error');
	} finally {
		btn.disabled = false;
	}
});

document.getElementById('clCopyBtn').addEventListener('click', async () => {
	const text = document.getElementById('clResult').value;
	if (!text) return;
	try {
		await navigator.clipboard.writeText(text);
		showStatus('Copied to clipboard', 'success');
		setTimeout(hideStatus, 1500);
	} catch (_) {
		document.getElementById('clResult').select();
		document.execCommand('copy');
		showStatus('Copied to clipboard', 'success');
		setTimeout(hideStatus, 1500);
	}
});

document.getElementById('clApplyBtn').addEventListener('click', async () => {
	const text = document.getElementById('clResult').value;
	if (!text) return;
	hideStatus();
	showStatus('Filling proposal on Upwork…', 'info');
	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_APPLY_COVER_LETTER',
			coverLetter: text,
			questions: clGeneratedQuestions,
		});
		if (!resp.ok) throw new Error(resp.error);
		const qaN = Number(resp.questionsFilled || 0);
		const parts = ['Cover letter filled'];
		if (qaN > 0) parts.push(`${qaN} answer${qaN === 1 ? '' : 's'}`);
		if (resp.terms?.paidByProject) parts.push('By project');
		if (resp.terms?.duration) parts.push('duration');
		showStatus(parts.join(' · '), 'success');
		setTimeout(hideStatus, 2500);
	} catch (e) {
		showStatus(e.message, 'error');
	}
});

document.getElementById('teamSelect').addEventListener('change', async function () {
	const teamId = this.value;
	if (!teamId) {
		document.getElementById('profileSelect').innerHTML = '<option value="">Select team first…</option>';
		document.getElementById('profileSelect').disabled = true;
		document.getElementById('startScanBtn').disabled = true;
		return;
	}
	await chrome.runtime.sendMessage({ type: 'EXT_SWITCH_TEAM', teamId: Number(teamId) });
	await loadProfiles(teamId);
});

document.getElementById('profileSelect').addEventListener('change', async function () {
	const teamId = document.getElementById('teamSelect').value;
	const code = this.value;
	await chrome.storage.local.set({ profileCode: code || null });
	await loadCampaignHint(teamId, code);
	await refreshScanStatus();
});

async function enableSelectedProxy({ recheck = false } = {}) {
	hideStatus();
	const teamId = document.getElementById('proxyTeamSelect').value;
	const code = document.getElementById('proxyProfileSelect').value;
	if (!teamId || !code) {
		showStatus('Select team and profile on the Proxy tab', 'error');
		return;
	}
	const enableBtn = document.getElementById('enableProxyBtn');
	const refreshBtn = document.getElementById('refreshProxyBtn');
	enableBtn.disabled = true;
	refreshBtn.disabled = true;
	showStatus(recheck ? 'Rechecking proxy IP…' : 'Enabling profile proxy…', 'info');
	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_ENABLE_PROXY',
			team_id: Number(teamId),
			code,
		});
		if (!resp.ok) {
			renderProxyStatus({ error: true, enabled: false });
			throw new Error(resp.error);
		}
		renderProxyStatus({
			browserIp: resp.data.browserIp,
			expectedIp: resp.data.expectedIp,
			matched: true,
			enabled: true,
			title: resp.data.title,
			code,
		});
		showStatus(`Proxy on · ${resp.data.browserIp}`, 'success');
		setTimeout(hideStatus, 2000);
	} catch (e) {
		showStatus(e.message, 'error');
	} finally {
		await refreshProxyStatus();
	}
}

document.getElementById('enableProxyBtn').addEventListener('click', () => enableSelectedProxy());
document.getElementById('refreshProxyBtn').addEventListener('click', () =>
	enableSelectedProxy({ recheck: true })
);

document.getElementById('disableProxyBtn').addEventListener('click', async () => {
	hideStatus();
	const btn = document.getElementById('disableProxyBtn');
	btn.disabled = true;
	showStatus('Disabling proxy…', 'info');
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'EXT_DISABLE_PROXY' });
		if (!resp.ok) throw new Error(resp.error);
		renderProxyStatus({ enabled: false });
		showStatus('Proxy disabled', 'info');
		setTimeout(hideStatus, 1500);
	} catch (e) {
		showStatus(e.message, 'error');
	} finally {
		await refreshProxyStatus();
	}
});

document.getElementById('startScanBtn').addEventListener('click', async () => {
	hideStatus();
	const teamId = document.getElementById('teamSelect').value;
	const profileSelect = document.getElementById('profileSelect');
	const code = profileSelect.value;
	const profileTitle = profileSelect.selectedOptions[0]?.text || code;

	if (!teamId || !code) {
		showStatus('Select team and profile', 'error');
		return;
	}

	await chrome.storage.local.set({ profileCode: code, teamId: Number(teamId) });

	showStatus('Starting scan…', 'info');
	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'SCAN_START',
			team_id: Number(teamId),
			code,
			profile_title: profileTitle,
		});
		if (!resp.ok) throw new Error(resp.error);
		showStatus('Scan started — keep Chrome open', 'success');
		await refreshScanStatus();
		setTimeout(hideStatus, 2000);
	} catch (e) {
		showStatus(e.message, 'error');
	}
});

document.getElementById('stopScanBtn').addEventListener('click', async () => {
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'SCAN_STOP' });
		if (!resp.ok) throw new Error(resp.error);
		showStatus('Scan stopped', 'info');
		await refreshScanStatus();
	} catch (e) {
		showStatus(e.message, 'error');
	}
});

document.getElementById('focusScanTabBtn').addEventListener('click', async () => {
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'SCAN_FOCUS_TAB' });
		if (!resp.ok) throw new Error(resp.error);
	} catch (e) {
		showStatus(e.message, 'error');
	}
});

chrome.runtime.onMessage.addListener((msg) => {
	if (msg.type === 'SCAN_STATUS') {
		const profileSelect = document.getElementById('profileSelect');
		const data = msg.data;
		// If profile list not ready yet, restore from scan then render
		if (data?.profileCode && profileSelect && !profileSelect.value) {
			refreshScanStatus();
			return;
		}
		renderScanStatus(data);
	}
});

document.addEventListener('DOMContentLoaded', () => {
	initTabs();
	checkAuth();
});
