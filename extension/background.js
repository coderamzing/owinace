// Backend URL - preset, user cannot change it
const BACKEND_URL = "http://127.0.0.1:8000/";

async function getSettings() {
	return new Promise((resolve) => {
		chrome.storage.local.get(['token', 'teamId'], (data) => {
			resolve({
				baseUrl: BACKEND_URL.replace(/\/$/, '/'),
				token: data.token || '',
				teamId: data.teamId || null,
			});
		});
	});
}

async function setAuth({ token, teams }) {
	return new Promise((resolve) => {
		chrome.storage.local.set({ token, teams }, resolve);
	});
}

async function clearAuth() {
	return new Promise((resolve) => {
		chrome.storage.local.remove(['token', 'teamId', 'teams'], resolve);
	});
}

async function apiFetch(path, { method = 'GET', body, auth = true } = {}) {
	const { baseUrl, token } = await getSettings();
	const headers = { 'Content-Type': 'application/json' };
	if (auth && token) headers['Authorization'] = `Bearer ${token}`;
	const res = await fetch(baseUrl + path.replace(/^\//, ''), {
		method,
		headers,
		body: body ? JSON.stringify(body) : undefined,
	});
	const data = await res.json().catch(() => ({}));

	if (!res.ok) {
		// If error is an object with validation error keys, format as string
		if (data && typeof data === "object" && res.status === 400) {
			const errorFields = Object.entries(data)
				.map(([key, value]) => {
					if (Array.isArray(value)) {
						return `${key}: ${value.join(', ')}`;
					}
					return `${key}: ${value}`;
				});
			const formatted = errorFields.length ? errorFields.join('\n') : 'Bad request';
			throw new Error(formatted);
		}
		throw new Error(data.error || data.message || `HTTP ${res.status}`);
	}
	return data;
}

chrome.runtime.onMessage.addListener((msg, sender, sendResponse) => {
	(async () => {
		try {
			switch (msg.type) {
				case 'EXT_LOGIN': {
					const data = await apiFetch('api/extension/login', {
						method: 'POST',
						auth: false,
						body: { email: msg.email, password: msg.password },
					});
					// Store teams data from response
					const teams = data.data || [];
					const defaultTeamId = teams.length > 0 ? teams[0].id : null;
					await setAuth({ token: data.token, teamId: defaultTeamId, teams: teams });
					console.log({ token: data.token, teamId: defaultTeamId, teams: teams })
					sendResponse({ ok: true, data: { ...data, teams: teams, team_id: defaultTeamId } });
					break;
				}
				case 'EXT_LOGOUT': {
					try {
						await apiFetch('api/extension/logout', { method: 'POST' });
					} catch (_) { }
					await clearAuth();
					sendResponse({ ok: true });
					break;
				}
				case 'EXT_GET_TEAMS': {
					// Get teams from storage (set during login)
					const storage = await new Promise((resolve) => {
						chrome.storage.local.get(['teams'], resolve);
					});
					sendResponse({ ok: true, data: { teams: storage.teams || [] } });
					break;
				}
				case 'EXT_SWITCH_TEAM': {
					const { teamId } = msg;
					await new Promise((resolve) => {
						chrome.storage.local.set({ teamId }, resolve);
					});
					sendResponse({ ok: true });
					break;
				}
				case 'EXT_GET_LEAD_FORM_DATA': {
					const data = await apiFetch(`api/extension/lead-form-data/${msg.team_id}`, {
						method: 'GET',
					});
					sendResponse({ ok: true, data });
					break;
				}
				case 'EXT_COVERLETTER': {
					const { team_id, job_description, words, level } = msg;
					const data = await apiFetch('api/extension/coverletter', {
						method: 'POST',
						body: { team_id, job_description, words, type: level },
					});
					sendResponse({ ok: true, data });
					break;
				}
				case 'EXT_PROPOSAL': {
					const { team_id, job_description, words, level, title, platform, create_lead, lead_data } = msg;
					const data = await apiFetch('api/extension/proposal', {
						method: 'POST',
						body: {
							team_id,
							job_description,
							words,
							type: level,
							title,
							platform,
							create_lead,
							lead_data
						},
					});
					sendResponse({ ok: true, data });
					break;
				}
				case 'EXT_LEAD_CREATE': {
					const { team_id, title, description, url, source_id, stage_id, expected_value, contact } = msg;
					const data = await apiFetch('api/extension/lead', {
						method: 'POST',
						body: {
							team_id,
							title,
							description,
							url,
							source_id,
							stage_id,
							expected_value,
							contact
						},
					});
					sendResponse({ ok: true, data });
					break;
				}
				default:
					sendResponse({ ok: false, error: 'Unknown message type' });
			}
		} catch (e) {
			sendResponse({ ok: false, error: e.message || String(e) });
		}
	})();
	return true; // keep channel open
});
