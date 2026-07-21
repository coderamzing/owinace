/**
 * Content script — responds to background scan commands on Upwork pages.
 */
(() => {
	'use strict';

	const Scan = window.LeadCliqUpworkScan;
	if (!Scan) {
		console.error('[LeadCliq] upwork-scan.js not loaded');
		return;
	}

	let busy = false;

	chrome.runtime.onMessage.addListener((msg, _sender, sendResponse) => {
		(async () => {
			try {
				switch (msg.type) {
					case 'SCAN_PING':
						sendResponse({
							ok: true,
							url: location.href,
							loggedIn: Scan.isLoggedIn(),
							state: Scan.getPageState(),
						});
						break;

					case 'SCAN_PAGE_STATE': {
						const ready = await Scan.waitForJobsOrCloudflare(
							msg.timeoutMs || 35000,
							msg.cfHitsNeeded || 3
						);
						// Jobs always win — do not CapSolver a loaded Upwork page
						if (ready === 'jobs' || Scan.jobCount() > 0) {
							sendResponse({ ok: true, state: 'jobs', url: location.href });
							break;
						}
						if (ready === 'cloudflare' && Scan.isCloudflareChallenge()) {
							sendResponse({ ok: true, state: 'cloudflare', url: location.href });
							break;
						}
						if (!Scan.isLoggedIn()) {
							sendResponse({ ok: true, state: 'login_required', url: location.href });
							break;
						}
						sendResponse({
							ok: true,
							state: ready,
							url: location.href,
							page: Scan.getPageState(),
						});
						break;
					}

					case 'SCAN_CHALLENGE_PAYLOAD':
						sendResponse({ ok: true, payload: Scan.getChallengePayload() });
						break;

					case 'SCAN_LIST_JOBS': {
						if (!Scan.isLoggedIn()) {
							sendResponse({ ok: false, error: 'Not logged in to Upwork', code: 'LOGIN_REQUIRED' });
							break;
						}
						if (Scan.isCloudflareChallenge()) {
							sendResponse({ ok: false, error: 'Cloudflare challenge active', code: 'CLOUDFLARE' });
							break;
						}
						const jobs = Scan.listJobs();
						sendResponse({ ok: true, jobs });
						break;
					}

					case 'SCAN_EXTRACT_JOB': {
						if (busy) {
							sendResponse({ ok: false, error: 'Scanner busy' });
							break;
						}
						busy = true;
						try {
							if (!Scan.isLoggedIn()) {
								sendResponse({ ok: false, error: 'Not logged in to Upwork', code: 'LOGIN_REQUIRED' });
								break;
							}
							const details = await Scan.extractJobDetails(msg.job);
							if (!details) {
								sendResponse({ ok: false, error: 'Failed to extract job details' });
								break;
							}
							sendResponse({ ok: true, details });
						} finally {
							busy = false;
						}
						break;
					}

					case 'SCAN_MARK_TAB':
						Scan.markScanTab();
						sendResponse({ ok: true, title: document.title });
						break;

					case 'SCAN_UNMARK_TAB':
						Scan.unmarkScanTab();
						sendResponse({ ok: true, title: document.title });
						break;

					case 'SCAN_COVER_LETTER_JOB': {
						const job = Scan.extractCoverLetterJob();
						sendResponse({
							ok: true,
							job,
							loggedIn: Scan.isLoggedIn(),
						});
						break;
					}

					case 'SCAN_APPLY_COVER_LETTER': {
						const Proposal = window.LeadCliqUpworkProposal;
						if (!Proposal?.applyProposal) {
							sendResponse({ ok: false, error: 'Proposal filler not loaded' });
							break;
						}
						const result = await Proposal.applyProposal({
							coverLetter: msg.coverLetter,
							questions: msg.questions || [],
						});
						sendResponse(result);
						break;
					}

					case 'SCAN_REFRESH': {
						sendResponse({ ok: true });
						setTimeout(() => Scan.softRefresh(), 200);
						break;
					}

					default:
						sendResponse({ ok: false, error: 'Unknown content message' });
				}
			} catch (e) {
				busy = false;
				sendResponse({ ok: false, error: e.message || String(e) });
			}
		})();
		return true;
	});
})();
