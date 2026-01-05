/**
 * Platform Manager
 * Detects and instantiates the correct platform class
 */

let platforms = null;

function initializePlatforms() {
	if (platforms) return platforms;

	platforms = [
		new UpworkPlatform()
	];

	return platforms;
}

/**
 * Detect platform from current page
 * @returns {BasePlatform|null}
 */
function detectPlatform() {
	const hostname = window.location.hostname.toLowerCase();
	const platformClasses = initializePlatforms();
	for (const platform of platformClasses) {
		if (platform.matches(hostname)) {
			return platform;
		}
	}
	return null;
}

/**
 * Get platform instance by name
 * @param {string} name - Platform name
 * @returns {BasePlatform|null}
 */
function getPlatform(name) {
	const platformClasses = initializePlatforms();
	return platformClasses.find(p => p.getName() === name) || null;
}

// Export for use in content script
if (typeof window !== 'undefined') {
	window.PlatformManager = {
		detectPlatform,
		getPlatform,
		initializePlatforms,
	};
}
