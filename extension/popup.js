// Helper functions

// INSERT_YOUR_CODE
/**
 * Detects the platform using the window.PlatformManager
 * Returns an object with platform instance and its name, or null if not detected
 */
async function detectPlatform() {
	if (window.PlatformManager && typeof window.PlatformManager.detectPlatform === 'function') {
		return window.PlatformManager.detectPlatform();
	}
	return null
}

async function applyCoverLetter(content) {
	const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
	if (!tab || !tab.id) throw new Error('No active tab');
	
	const result = await chrome.scripting.executeScript({
		target: { tabId: tab.id },
		func: async (coverLetterText) => {
			// Get platform by matching domain using PlatformManager
			let platform = null;
			
			if (window.PlatformManager && typeof window.PlatformManager.detectPlatform === 'function') {
				platform = window.PlatformManager.detectPlatform();
			}
			
			if (!platform) {
				throw new Error('Platform not detected');
			}
			
			console.log('Apply cover letter - Platform:', platform.getName());
			
			// Call applyCoverLetter on the platform
			if (platform && typeof platform.applyCoverLetter === 'function') {
				try {
					const success = await platform.applyCoverLetter(coverLetterText);
					return {
						success: success,
						platform: platform.getName()
					};
				} catch (err) {
					return {
						success: false,
						error: err.message,
						platform: platform.getName()
					};
				}
			}
			
			return {
				success: false,
				error: 'applyCoverLetter method not available on platform'
			};
		},
		args: [content]
	});
	
	if (!result || !result[0] || !result[0].result) {
		throw new Error('Failed to apply cover letter');
	}
	
	return result[0].result;
}

async function scrapeJobDescription() {
	const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
	if (!tab || !tab.id) throw new Error('No active tab');

	// Inject script to scrape job description using platform classes
	const result = await chrome.scripting.executeScript({
		target: { tabId: tab.id },
		func: async () => {
			// Get platform by matching domain using PlatformManager
			let platform = null;
			
			if (window.PlatformManager && typeof window.PlatformManager.detectPlatform === 'function') {
				platform = window.PlatformManager.detectPlatform();
			}

			console.log(platform)
			
			// If platform found, call findJobDescription
			if (platform && typeof platform.findJobDescription === 'function') {
				try {
					const description = await platform.findJobDescription();
					return {
						description: description || '',
						platform: platform.getName()
					};
				} catch (err) {
					return {
						description: '',
						platform: '',
						error: err.message
					};
				}
			}
			
			// Fallback: return empty
			return {
				description: '',
				platform: '',
				error: 'Platform not supported or not detected'
			};
		}
	});

	if (!result || !result[0] || !result[0].result) {
		throw new Error('Failed to scrape page');
	}

	// Return the result (Chrome handles the promise automatically)
	return result[0].result;
}

async function scrapeLead() {
	const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
	if (!tab || !tab.id) throw new Error('No active tab');
	const result = await chrome.scripting.executeScript({
		target: { tabId: tab.id },
		func: async () => {
			let platform = null;
			if (window.PlatformManager && typeof window.PlatformManager.detectPlatform === 'function') {
				platform = window.PlatformManager.detectPlatform();
			}
			// If platform found, call findJobDescription
			if (platform && typeof platform.findLead === 'function') {
				try{
					console.log("SAdasd")
					const lead = await platform.findLead();
					return {
						lead: lead || {},
					};
				}catch(err){
					console.log(err)
					return {};
				}
			}
			throw new Error('Platform not supported or not detected');
		}
	});
	if (!result || !result[0] || !result[0].result) {
		throw new Error('Failed to scrape lead');
	}
	return result[0].result;
}

function setResult(text, isError = false) {
	const el = document.getElementById('result');
	el.textContent = text;
	el.className = isError ? 'status error' : 'status info';
	el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function showStatus(message, type = 'info') {
	const el = document.getElementById('result');
	el.textContent = message;
	el.className = `status ${type}`;
	el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hideStatus() {
	const el = document.getElementById('result');
	el.textContent = '';
	el.className = '';
}

// Tab switching functionality
function switchTab(tabName) {
	// Hide all tab contents
	document.querySelectorAll('.tab-content').forEach(content => {
		content.classList.remove('active');
	});
	
	// Remove active class from all tabs
	document.querySelectorAll('.tab').forEach(tab => {
		tab.classList.remove('active');
	});
	
	// Show selected tab content
	const selectedTabContent = document.getElementById(`tab-${tabName}`);
	if (selectedTabContent) {
		selectedTabContent.classList.add('active');
	}
	
	// Add active class to selected tab button
	const selectedTab = document.querySelector(`.tab[data-tab="${tabName}"]`);
	if (selectedTab) {
		selectedTab.classList.add('active');
	}
}

// Initialize tab switching
function initTabs() {
	document.querySelectorAll('.tab').forEach(tab => {
		tab.addEventListener('click', () => {
			const tabName = tab.getAttribute('data-tab');
			switchTab(tabName);
		});
	});
}

// Check authentication status on load
async function checkAuth() {
	const settings = await new Promise((resolve) => {
		chrome.storage.local.get(['token', 'teams'], resolve);
	});

	if (settings.token) {
		// Hide login form, show tabs container
		document.getElementById('loginForm').classList.add('hidden');
		document.getElementById('loginSection').classList.add('hidden');
		document.getElementById('tabsContainer').classList.remove('hidden');
		
		await loadTeams();
		// Set default team if not set
		if (settings.teams && settings.teams.length > 0 && !settings.teamId) {
			await switchTeam(settings.teams[0].id);
		}
		await loadLeadFormData();
	} else {
		// Show login form, hide tabs container
		document.getElementById('loginForm').classList.remove('hidden');
		document.getElementById('loginSection').classList.remove('hidden');
		document.getElementById('tabsContainer').classList.add('hidden');
	}
}

// Load teams for dropdowns
async function loadTeams() {
	try {
		const settings = await new Promise((resolve) => {
			chrome.storage.local.get(['teamId','teams'], resolve);
		});
		const currentTeamId = settings.teamId;

		const teamSelect = document.getElementById('teamSelect');

		teamSelect.innerHTML = '<option value="">Select team...</option>';

		settings.teams.forEach(team => {
			const option1 = new Option(team.name, team.id);
			if (team.id == currentTeamId) {
				option1.selected = true;
			}
			teamSelect.appendChild(option1);
		});
	} catch (e) {
		console.error('Failed to load teams:', e);
	}
}

// Switch active team
async function switchTeam(teamId) {
	try {
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_SWITCH_TEAM',
			teamId: teamId
		});
		if (!resp.ok) throw new Error(resp.error);
		// Reload form data for new team
		await loadLeadFormData();
		return true;
	} catch (e) {
		console.error('Failed to switch team:', e);
		return false;
	}
}

// Load lead form data (sources, stages) from stored teams data
async function loadLeadFormData() {
	try {
		const teamId = document.getElementById('teamSelect').value;
		if (!teamId) {
			// Clear dropdowns if no team selected
			document.getElementById('leadSource').innerHTML = '<option value="">Select source...</option>';
			document.getElementById('leadStage').innerHTML = '<option value="">Select stage...</option>';
			return;
		}

		// Get teams from storage
		const storage = await new Promise((resolve) => {
			chrome.storage.local.get(['teams'], resolve);
		});

		const teams = storage.teams || [];
		const selectedTeam = teams.find(t => t.id == teamId);

		if (!selectedTeam) {
			console.error('Team not found in stored data');
			return;
		}

		// Populate sources
		const sourceSelect = document.getElementById('leadSource');
		sourceSelect.innerHTML = '<option value="">Select source...</option>';
		if (selectedTeam.sources) {
			selectedTeam.sources.forEach(source => {
				if (source.is_active) {
					sourceSelect.appendChild(new Option(source.name, source.id));
				}
			});
		}

		// Populate stages
		const stageSelect = document.getElementById('leadStage');
		stageSelect.innerHTML = '<option value="">Select stage...</option>';
		if (selectedTeam.stages) {
			selectedTeam.stages.forEach(stage => {
				if (stage.is_active) {
					stageSelect.appendChild(new Option(stage.name, stage.id));
				}
			});
		}
	} catch (e) {
		console.error('Failed to load lead form data:', e);
	}
}

// Event Listeners
document.getElementById('loginBtn').addEventListener('click', async () => {
	try {
		hideStatus();
		const email = document.getElementById('email').value.trim();
		const password = document.getElementById('password').value;

		if (!email || !password) {
			showStatus('Please enter email and password', 'error');
			return;
		}

		const resp = await chrome.runtime.sendMessage({ type: 'EXT_LOGIN', email, password });
		if (!resp.ok) throw new Error(resp.error);

		showStatus('Logged in successfully!', 'success');
		// Hide login form and show tabs
		document.getElementById('loginForm').classList.add('hidden');
		document.getElementById('loginSection').classList.add('hidden');
		document.getElementById('tabsContainer').classList.remove('hidden');
		
		await checkAuth();
		// Reload teams after login
		await loadTeams();
		// Switch to team tab after login
		switchTab('team');
	} catch (e) {
		showStatus('Login error: ' + e.message, 'error');
	}
});

document.getElementById('logoutBtn').addEventListener('click', async () => {
	try {
		const resp = await chrome.runtime.sendMessage({ type: 'EXT_LOGOUT' });
		if (!resp.ok) throw new Error(resp.error || 'Logout failed');
		showStatus('Logged out successfully', 'success');
		// Hide tabs and show login after logout
		document.getElementById('tabsContainer').classList.add('hidden');
		document.getElementById('loginSection').classList.remove('hidden');
		document.getElementById('loginForm').classList.remove('hidden');
		await checkAuth();
	} catch (e) {
		showStatus('Logout error: ' + e.message, 'error');
	}
});

// Auto-fill job description from page
document.getElementById('fillJobDescBtn').addEventListener('click', async () => {
	try {
		hideStatus();
		showStatus('Extracting job description from page...', 'info');
		const data = await scrapeJobDescription();
		console.log(data)
		document.getElementById('jobDescription').value = data.description || '';
		hideStatus();
		if (data.description) {
			showStatus('Job description filled successfully', 'success');
			setTimeout(hideStatus, 2000);
		} else {
			showStatus('Could not extract job description. Please enter manually.', 'error');
		}
	} catch (e) {
		showStatus('Error: ' + e.message, 'error');
	}
});

// Generate cover letter
document.getElementById('generateCoverBtn').addEventListener('click', async () => {
	try {
		hideStatus();
		const teamId = document.getElementById('teamSelect').value;
		const jobDescription = document.getElementById('jobDescription').value.trim();
		const words = document.getElementById('words').value;
		const level = document.getElementById('level').value;

		if (!teamId) {
			showStatus('Please select a team', 'error');
			return;
		}

		if (!jobDescription) {
			showStatus('Please enter or auto-fill job description', 'error');
			return;
		}

		showStatus('Generating cover letter...', 'info');
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_COVERLETTER',
			team_id: teamId,
			job_description: jobDescription,
			words,
			level
		});

		if (!resp.ok) throw new Error(resp.error);

		const result = await applyCoverLetter(resp.data.content);
		if (result && result.success) {
			showStatus('Cover letter applied successfully', 'success');
			setTimeout(hideStatus, 2000);
		} else {
			const errorMsg = result?.error || 'Failed to apply cover letter';
			showStatus('Cover letter error: ' + errorMsg, 'error');
		}

	} catch (e) {
		showStatus('Cover letter error: ' + e.message, 'error');
	}
});

//Auto-fill lead from page
document.getElementById('fillLeadBtn').addEventListener('click', async () => {
	try {
		hideStatus();
		showStatus('Extracting lead information from page...', 'info');
		const data = await scrapeLead();

		console.log(data)

		if (data.lead) {
			document.getElementById('leadTitle').value = data.lead.title || '';
			document.getElementById('leadUrl').value = data.lead.url || '';
			document.getElementById('leadContact').value = data.lead.contact || '';
			hideStatus();
			showStatus('Lead information filled successfully', 'success');
			setTimeout(hideStatus, 2000);
		} else {
			hideStatus();
			showStatus('Could not extract lead information. Please enter manually.', 'error');
		}
	} catch (e) {
		showStatus('Error: ' + e.message, 'error');
	}
});


// Switch team when proposal team changes
document.getElementById('teamSelect').addEventListener('change', async function () {
	const teamId = this.value;
	if (teamId) {
		await switchTeam(teamId);
	}
});

// Submit lead
document.getElementById('submitLeadBtn').addEventListener('click', async () => {
	try {
		hideStatus();
		const teamId = document.getElementById('teamSelect').value;
		const title = document.getElementById('leadTitle').value;
		const description = document.getElementById('leadDescription').value;
		const sourceId = document.getElementById('leadSource').value;
		const stageId = document.getElementById('leadStage').value;
		const contact = document.getElementById('leadContact').value;
		const leadUrl = document.getElementById('leadUrl').value;
		const expectedValue = parseFloat(document.getElementById('expectedValue').value) || 0;

		showStatus('Submitting lead...', 'info');
		const resp = await chrome.runtime.sendMessage({
			type: 'EXT_LEAD_CREATE',
			team_id: teamId,
			title,
			description,
			url: leadUrl,
			contact: contact,
			source_id: sourceId || null,
			stage_id: stageId || null,
			expected_value: expectedValue
		});

		if (!resp.ok) throw new Error(resp.error);

		showStatus(`Lead created successfully! Lead ID: ${resp.data.lead_id}`, 'success');

		// Clear form
		document.getElementById('leadTitle').value = '';
		document.getElementById('leadDescription').value = '';
		//document.getElementById('assignedMember').value = '';
		document.getElementById('leadSource').value = '';
		document.getElementById('leadStage').value = '';
		document.getElementById('expectedValue').value = '';
	} catch (e) {
		showStatus('Lead submission error: ' + e.message, 'error');
	}
});

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
	initTabs();
	checkAuth();
});
