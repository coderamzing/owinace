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
				error: 'Platform not supported or not detected!!'
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

// Validation functions
function validateEmail(email) {
	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	return emailRegex.test(email);
}

function validatePassword(password) {
	// Password must be at least 6 characters
	return password && password.length >= 6;
}

function showFieldError(fieldId, errorMessage) {
	const field = document.getElementById(fieldId);
	const errorElement = document.getElementById(fieldId + 'Error');
	
	if (field) {
		field.classList.remove('valid');
		field.classList.add('error');
	}
	
	if (errorElement) {
		errorElement.textContent = errorMessage;
		errorElement.classList.add('show');
	}
}

function showFieldValid(fieldId) {
	const field = document.getElementById(fieldId);
	const errorElement = document.getElementById(fieldId + 'Error');
	
	if (field) {
		field.classList.remove('error');
		field.classList.add('valid');
	}
	
	if (errorElement) {
		errorElement.textContent = '';
		errorElement.classList.remove('show');
	}
}

function clearFieldValidation(fieldId) {
	const field = document.getElementById(fieldId);
	const errorElement = document.getElementById(fieldId + 'Error');
	
	if (field) {
		field.classList.remove('error', 'valid');
	}
	
	if (errorElement) {
		errorElement.textContent = '';
		errorElement.classList.remove('show');
	}
}

function validateLoginForm() {
	const email = document.getElementById('email').value.trim();
	const password = document.getElementById('password').value;
	let isValid = true;

	// Validate email
	if (!email) {
		showFieldError('email', 'Email is required');
		isValid = false;
	} else if (!validateEmail(email)) {
		showFieldError('email', 'Please enter a valid email address');
		isValid = false;
	} else {
		showFieldValid('email');
	}

	// Validate password
	if (!password) {
		showFieldError('password', 'Password is required');
		isValid = false;
	} else if (!validatePassword(password)) {
		showFieldError('password', 'Password must be at least 6 characters');
		isValid = false;
	} else {
		showFieldValid('password');
	}

	return isValid;
}

function validateNumber(value, min, max, fieldName) {
	const num = parseInt(value);
	if (isNaN(num)) {
		return { valid: false, message: `${fieldName} must be a valid number` };
	}
	if (num < min) {
		return { valid: false, message: `${fieldName} must be at least ${min}` };
	}
	if (num > max) {
		return { valid: false, message: `${fieldName} must be at most ${max}` };
	}
	return { valid: true };
}

function validateCoverLetterForm() {
	const jobDescription = document.getElementById('jobDescription').value.trim();
	const words = document.getElementById('words').value;
	const level = document.getElementById('level').value;
	let isValid = true;

	// Validate job description
	if (!jobDescription) {
		showFieldError('jobDescription', 'Job description is required');
		isValid = false;
	} else if (jobDescription.length < 50) {
		showFieldError('jobDescription', 'Job description must be at least 50 characters');
		isValid = false;
	} else {
		showFieldValid('jobDescription');
	}

	// Validate words
	const wordsValidation = validateNumber(words, 150, 1000, 'Words');
	if (!wordsValidation.valid) {
		showFieldError('words', wordsValidation.message);
		isValid = false;
	} else {
		showFieldValid('words');
	}

	// Validate level
	if (!level) {
		showFieldError('level', 'Level is required');
		isValid = false;
	} else {
		showFieldValid('level');
	}

	return isValid;
}

function validateLeadForm() {
	const title = document.getElementById('leadTitle').value.trim();
	const expectedValue = document.getElementById('expectedValue').value;
	let isValid = true;

	// Validate title
	if (!title) {
		showFieldError('leadTitle', 'Title is required');
		isValid = false;
	} else if (title.length < 3) {
		showFieldError('leadTitle', 'Title must be at least 3 characters');
		isValid = false;
	} else {
		showFieldValid('leadTitle');
	}

	// Validate expected value (if provided)
	if (expectedValue) {
		const valueNum = parseFloat(expectedValue);
		if (isNaN(valueNum)) {
			showFieldError('expectedValue', 'Expected value must be a valid number');
			isValid = false;
		} else if (valueNum < 0) {
			showFieldError('expectedValue', 'Expected value cannot be negative');
			isValid = false;
		} else {
			showFieldValid('expectedValue');
		}
	} else {
		clearFieldValidation('expectedValue');
	}

	return isValid;
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
		await loadCoverLetterTypes();
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
		await loadCoverLetterTypes();
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

// Load cover letter types from stored teams data
async function loadCoverLetterTypes() {
	try {
		const teamId = document.getElementById('teamSelect').value;
		if (!teamId) {
			// Clear dropdown if no team selected
			document.getElementById('level').innerHTML = '<option value="">Select team first...</option>';
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
			document.getElementById('level').innerHTML = '<option value="">Team not found...</option>';
			return;
		}

		// Populate cover letter types
		const levelSelect = document.getElementById('level');
		levelSelect.innerHTML = '<option value="">Select level...</option>';
		if (selectedTeam.coverletter_types && Array.isArray(selectedTeam.coverletter_types)) {
			selectedTeam.coverletter_types.forEach(type => {
				const option = new Option(type.charAt(0).toUpperCase() + type.slice(1), type);
				levelSelect.appendChild(option);
			});
		}
	} catch (e) {
		console.error('Failed to load cover letter types:', e);
		document.getElementById('level').innerHTML = '<option value="">Error loading types...</option>';
	}
}

// Event Listeners - Login Form Submission
document.getElementById('loginForm').addEventListener('submit', async (e) => {
	e.preventDefault(); // Prevent default form submission
	
	try {
		hideStatus();
		
		// Validate form before submission
		if (!validateLoginForm()) {
			showStatus('Please fix the errors in the form', 'error');
			return;
		}

		const email = document.getElementById('email').value.trim();
		const password = document.getElementById('password').value;

		showStatus('Logging in...', 'info');
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
		console.log("returnd data", data)
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

// Generate cover letter - Form submission handler
document.getElementById('coverLetterForm').addEventListener('submit', async (e) => {
	e.preventDefault(); // Prevent default form submission
	
	try {
		hideStatus();
		
		// Validate form before submission
		if (!validateCoverLetterForm()) {
			showStatus('Please fix the errors in the form', 'error');
			return;
		}
		
		const teamId = document.getElementById('teamSelect').value;
		const jobDescription = document.getElementById('jobDescription').value.trim();
		const words = document.getElementById('words').value;
		const level = document.getElementById('level').value;

		if (!teamId) {
			showStatus('Please select a team', 'error');
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

// Helper function to select option by matching text (case-insensitive)
function selectOptionByText(selectElement, searchText) {
	if (!selectElement || !searchText) return false;
	
	const options = Array.from(selectElement.options);
	const matchingOption = options.find(option => 
		option.text.toLowerCase().trim() === searchText.toLowerCase().trim()
	);
	
	if (matchingOption) {
		selectElement.value = matchingOption.value;
		return true;
	}
	
	return false;
}

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
			document.getElementById('leadDescription').value = data.lead.description ? data.lead.description.substring(0, 200) : '';
			document.getElementById('expectedValue').value = data.lead.cost || '';
			
			// Select source by matching text
			if (data.lead.source) {
				const sourceSelect = document.getElementById('leadSource');
				selectOptionByText(sourceSelect, data.lead.source);
			}

			// Select stage by matching text
			if (data.lead.stage) {
				const stageSelect = document.getElementById('leadStage');
				selectOptionByText(stageSelect, data.lead.stage);
			}
			
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

// Submit lead - Form submission handler
document.getElementById('leadForm').addEventListener('submit', async (e) => {
	e.preventDefault(); // Prevent default form submission
	
	try {
		hideStatus();
		
		// Validate form before submission
		if (!validateLeadForm()) {
			showStatus('Please fix the errors in the form', 'error');
			return;
		}
		
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

		// Reset form after successful submission
		document.getElementById('leadForm').reset();
		// Clear hidden fields manually as reset() might not clear them
		document.getElementById('leadUrl').value = '';
		document.getElementById('leadContact').value = '';
	} catch (e) {
		showStatus('Lead submission error: ' + e.message, 'error');
	}
});

// Real-time validation for login form inputs
document.addEventListener('DOMContentLoaded', () => {
	initTabs();
	checkAuth();

	// Email validation on input
	const emailInput = document.getElementById('email');
	if (emailInput) {
		emailInput.addEventListener('blur', () => {
			const email = emailInput.value.trim();
			if (email) {
				if (!validateEmail(email)) {
					showFieldError('email', 'Please enter a valid email address');
				} else {
					showFieldValid('email');
				}
			} else {
				clearFieldValidation('email');
			}
		});

		emailInput.addEventListener('input', () => {
			const email = emailInput.value.trim();
			if (email && validateEmail(email)) {
				showFieldValid('email');
			} else if (emailInput.classList.contains('error')) {
				// Keep error if already showing
			}
		});

		// Move to password field on Enter key press
		emailInput.addEventListener('keypress', (e) => {
			if (e.key === 'Enter') {
				e.preventDefault();
				const passwordInput = document.getElementById('password');
				if (passwordInput) {
					passwordInput.focus();
				}
			}
		});
	}

	// Password validation on input
	const passwordInput = document.getElementById('password');
	if (passwordInput) {
		passwordInput.addEventListener('blur', () => {
			const password = passwordInput.value;
			if (password) {
				if (!validatePassword(password)) {
					showFieldError('password', 'Password must be at least 6 characters');
				} else {
					showFieldValid('password');
				}
			} else {
				clearFieldValidation('password');
			}
		});

		passwordInput.addEventListener('input', () => {
			const password = passwordInput.value;
			if (password && validatePassword(password)) {
				showFieldValid('password');
			} else if (passwordInput.classList.contains('error')) {
				// Keep error if already showing
			}
		});
		// Note: Form will handle Enter key submission naturally via the form's submit event
	}

	// Real-time validation for cover letter form
	const jobDescriptionInput = document.getElementById('jobDescription');
	if (jobDescriptionInput) {
		jobDescriptionInput.addEventListener('blur', () => {
			const jobDescription = jobDescriptionInput.value.trim();
			if (jobDescription) {
				if (jobDescription.length < 50) {
					showFieldError('jobDescription', 'Job description must be at least 50 characters');
				} else {
					showFieldValid('jobDescription');
				}
			} else {
				clearFieldValidation('jobDescription');
			}
		});

		jobDescriptionInput.addEventListener('input', () => {
			const jobDescription = jobDescriptionInput.value.trim();
			if (jobDescription && jobDescription.length >= 50) {
				showFieldValid('jobDescription');
			}
		});
	}

	const wordsInput = document.getElementById('words');
	if (wordsInput) {
		wordsInput.addEventListener('blur', () => {
			const words = wordsInput.value;
			if (words) {
				const validation = validateNumber(words, 150, 1000, 'Words');
				if (!validation.valid) {
					showFieldError('words', validation.message);
				} else {
					showFieldValid('words');
				}
			} else {
				clearFieldValidation('words');
			}
		});

		wordsInput.addEventListener('input', () => {
			const words = wordsInput.value;
			if (words) {
				const validation = validateNumber(words, 150, 1000, 'Words');
				if (validation.valid) {
					showFieldValid('words');
				}
			}
		});
	}

	const levelSelect = document.getElementById('level');
	if (levelSelect) {
		levelSelect.addEventListener('change', () => {
			const level = levelSelect.value;
			if (level) {
				showFieldValid('level');
			} else {
				clearFieldValidation('level');
			}
		});
	}

	// Real-time validation for lead form
	const leadTitleInput = document.getElementById('leadTitle');
	if (leadTitleInput) {
		leadTitleInput.addEventListener('blur', () => {
			const title = leadTitleInput.value.trim();
			if (title) {
				if (title.length < 3) {
					showFieldError('leadTitle', 'Title must be at least 3 characters');
				} else {
					showFieldValid('leadTitle');
				}
			} else {
				clearFieldValidation('leadTitle');
			}
		});

		leadTitleInput.addEventListener('input', () => {
			const title = leadTitleInput.value.trim();
			if (title && title.length >= 3) {
				showFieldValid('leadTitle');
			}
		});
	}

	const expectedValueInput = document.getElementById('expectedValue');
	if (expectedValueInput) {
		expectedValueInput.addEventListener('blur', () => {
			const expectedValue = expectedValueInput.value;
			if (expectedValue) {
				const valueNum = parseFloat(expectedValue);
				if (isNaN(valueNum)) {
					showFieldError('expectedValue', 'Expected value must be a valid number');
				} else if (valueNum < 0) {
					showFieldError('expectedValue', 'Expected value cannot be negative');
				} else {
					showFieldValid('expectedValue');
				}
			} else {
				clearFieldValidation('expectedValue');
			}
		});

		expectedValueInput.addEventListener('input', () => {
			const expectedValue = expectedValueInput.value;
			if (expectedValue) {
				const valueNum = parseFloat(expectedValue);
				if (!isNaN(valueNum) && valueNum >= 0) {
					showFieldValid('expectedValue');
				}
			}
		});
	}
});
