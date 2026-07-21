/**
 * Upwork job-list scrapers (Phase 1: stay on search page, open slider only).
 * Ported from upbot2/lib/upwork.js getJobs + extractJobDetails + CapSolver detection.
 */
(function (global) {
	'use strict';

	function sleep(ms) {
		return new Promise((resolve) => setTimeout(resolve, ms));
	}

	function randomBetween(min, max) {
		return Math.round(min + Math.random() * (max - min));
	}

	function parsePostedAt(details) {
		if (!details) return null;
		const number = Number(details.match(/(\d+)/)?.[1] || 1);
		const now = Date.now();
		if (details.includes('just now')) return new Date(now).toISOString();
		if (details.includes('minute')) return new Date(now - number * 60 * 1000).toISOString();
		if (details.includes('hour')) return new Date(now - number * 60 * 60 * 1000).toISOString();
		if (details.includes('yesterday')) return new Date(now - 24 * 60 * 60 * 1000).toISOString();
		if (details.includes('day')) return new Date(now - number * 24 * 60 * 60 * 1000).toISOString();
		if (details.includes('week')) return new Date(now - number * 7 * 24 * 60 * 60 * 1000).toISOString();
		if (details.includes('month')) return new Date(now - number * 30 * 24 * 60 * 60 * 1000).toISOString();
		return new Date(now).toISOString();
	}

	function isLoggedIn() {
		const headerText = document.querySelector('header')?.innerText || '';
		const bodyText = document.body?.innerText || '';
		if (headerText.includes('Log in') || bodyText.includes('Log in to Upwork')) {
			return false;
		}
		return true;
	}

	function isVisible(el) {
		if (!el) return false;
		const style = window.getComputedStyle(el);
		if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
			return false;
		}
		const rect = el.getBoundingClientRect();
		return rect.width > 0 && rect.height > 0;
	}

	/**
	 * True only for an active Cloudflare interstitial — not normal Upwork pages
	 * that still embed challenge-platform / turnstile script tags in HTML.
	 */
	function isCloudflareChallenge() {
		// Real job list = never a challenge
		if (jobCount() > 0) return false;

		// Strip our own scan-tab prefix so "[LeadCliq] Just a moment…" still matches
		const title = (document.title || '').replace(/^\[LeadCliq\]\s*/i, '').trim();
		if (
			/just a moment/i.test(title) ||
			/^attention required/i.test(title) ||
			/verify(?:ing)? you are human/i.test(title) ||
			/checking (?:if the site connection is secure|your browser)/i.test(title)
		) {
			return true;
		}

		// Cloudflare sets this global on any challenge/interstitial page
		try {
			if (global._cf_chl_opt || global.__CF$cv$params) return true;
		} catch (_) {}

		// Visible challenge UI (not buried CF scripts on a normal SPA)
		const visibleChallenge =
			document.querySelector('#challenge-form') ||
			document.querySelector('#challenge-stage') ||
			document.querySelector('#cf-challenge-running') ||
			document.querySelector('#cf-chl-widget') ||
			document.querySelector('.cf-browser-verification') ||
			document.querySelector('iframe[src*="challenges.cloudflare.com"]') ||
			document.querySelector('iframe[src*="turnstile"]');

		if (isVisible(visibleChallenge)) return true;

		// Upwork chrome already present → page loaded past CF
		if (
			document.querySelector('header') ||
			document.querySelector('[data-test="job-tile-list"]') ||
			document.querySelector('[data-test="JobsList"]') ||
			document.querySelector('nav')
		) {
			return false;
		}

		// Sparse interstitial body (CF holding page before Upwork mounts)
		const bodyText = (document.body?.innerText || '').replace(/\s+/g, ' ').trim().slice(0, 400);
		if (
			/checking your browser|verify(?:ing)? you are human|just a moment|enable javascript and cookies|needs to review the security of your connection/i.test(
				bodyText
			)
		) {
			return true;
		}

		return false;
	}

	/** Same as upbot2: only top-level job cards */
	function findJobCards() {
		return [...document.querySelectorAll('article[data-ev-job-uid]')];
	}

	function jobCount() {
		return findJobCards().length;
	}

	/**
	 * Poll until jobs appear, a sustained Cloudflare challenge is seen, or timeout.
	 * Requires challenge to stick across several polls so brief CF HTML flicker
	 * (or normal pages with CF scripts) does not create CapSolver tasks.
	 * @returns {'jobs'|'cloudflare'|'timeout'}
	 */
	async function waitForJobsOrCloudflare(timeoutMs = 35000) {
		const start = Date.now();
		let cfHits = 0;
		while (Date.now() - start < timeoutMs) {
			if (jobCount() > 0) return 'jobs';
			if (isCloudflareChallenge()) {
				cfHits += 1;
				// ~4.5s of sustained challenge before calling CapSolver
				if (cfHits >= 3) return 'cloudflare';
			} else {
				cfHits = 0;
			}
			await sleep(1500);
		}
		if (jobCount() > 0) return 'jobs';
		if (cfHits >= 2 && isCloudflareChallenge()) return 'cloudflare';
		return 'timeout';
	}

	function getChallengePayload() {
		return {
			websiteURL: location.href,
			userAgent: navigator.userAgent,
			html: document.documentElement?.outerHTML || '',
		};
	}

	function getPageState() {
		if (!isLoggedIn() && !isCloudflareChallenge()) {
			return {
				state: 'login_required',
				url: location.href,
				loggedIn: false,
				jobCount: jobCount(),
			};
		}
		if (isCloudflareChallenge()) {
			return {
				state: 'cloudflare',
				url: location.href,
				loggedIn: isLoggedIn(),
				jobCount: 0,
			};
		}
		if (jobCount() > 0) {
			return {
				state: 'jobs',
				url: location.href,
				loggedIn: isLoggedIn(),
				jobCount: jobCount(),
			};
		}
		return {
			state: 'loading',
			url: location.href,
			loggedIn: isLoggedIn(),
			jobCount: 0,
		};
	}

	/** Same as upbot2 getJobs evaluate() */
	function listJobs() {
		return findJobCards().map((card) => {
			const jobId = card.getAttribute('data-ev-job-uid');
			return {
				isApplied: card
					.querySelector('[data-test*="badges"]')
					?.innerText.match(/^applied/i),
				type: card
					.querySelector('[data-test="JobInfo"]')
					?.innerText.match(/^fixed/i)
					? 'fixed'
					: 'hourly',
				jobId,
				url: `https://www.upwork.com/jobs/~02${jobId}`,
				applyUrl: `https://www.upwork.com/nx/proposals/job/~02${jobId}/apply/`,
			};
		}).filter((j) => j.jobId);
	}

	async function closeJobSlider() {
		// Same as upbot2: JobDetailsSliderHeader close button
		const closeBtn = document.querySelector(
			'[data-test="JobDetailsSliderHeader"] button'
		);
		if (closeBtn) closeBtn.click();

		const start = Date.now();
		while (Date.now() - start < 15000) {
			const slider = document.querySelector('[slidername*="job-details"]');
			if (!slider || getComputedStyle(slider).display === 'none' || slider.offsetParent === null) {
				break;
			}
			await sleep(200);
		}
		await sleep(randomBetween(400, 900));
	}

	/**
	 * Same as upbot2 extractJobDetails:
	 * click article[data-ev-job-uid="{id}"] a → wait [slidername*="job-details"] → read → close
	 */
	async function extractJobDetails(job) {
		const jobId = String(job.jobId || job.id || '').trim();
		const url = job.url || `https://www.upwork.com/jobs/~02${jobId}`;
		if (!jobId) return null;

		const card = document.querySelector(`article[data-ev-job-uid="${jobId}"]`);
		if (!card) return null;

		// upbot2: article[data-ev-job-uid="{id}"] a  (first anchor in the card)
		const link = card.querySelector('a');
		if (!link) return null;

		link.click();

		const loaded = await new Promise((resolve) => {
			const start = Date.now();
			const tick = () => {
				const slider = document.querySelector('[slidername*="job-details"]');
				if (slider && isVisible(slider) && (slider.innerText || '').length > 80) {
					resolve(true);
					return;
				}
				if (Date.now() - start > 45000) {
					resolve(false);
					return;
				}
				setTimeout(tick, 300);
			};
			tick();
		});

		if (!loaded) {
			await closeJobSlider();
			return null;
		}

		// upbot2 waits ~15s for full slider content to render
		await sleep(randomBetween(12_000, 16_000));

		const details =
			document.querySelector('[slidername*="job-details"]')?.innerText || null;

		await closeJobSlider();

		if (!details) return null;

		const postedAtString = details.match(/Posted\s+(.*)/i)?.[1];
		const postedAt = parsePostedAt(postedAtString);

		const skillsMatch = details.match(
			/Mandatory skills\s*([\s\S]*?)(?:Preferred qualifications|Activity on this job)/i
		);
		let skills = [];
		if (skillsMatch) {
			skills = skillsMatch[1]
				.split('\n')
				.map((x) => x.trim())
				.filter(Boolean);
		}

		return { id: jobId, url, rawText: details, postedAt, skills };
	}

	function markScanTab() {
		try {
			// Never stamp our prefix onto a Cloudflare interstitial — it would
			// corrupt the "Just a moment…" title CapSolver detection relies on.
			if (isCloudflareChallenge()) return;
			if (!document.title.startsWith('[LeadCliq]')) {
				document.title = `[LeadCliq] ${document.title}`;
			}
		} catch (_) {}
	}

	function unmarkScanTab() {
		try {
			if (document.title.startsWith('[LeadCliq]')) {
				document.title = document.title.replace(/^\[LeadCliq\]\s*/, '');
			}
		} catch (_) {}
	}

	async function softRefresh() {
		window.location.reload();
	}

	/**
	 * Pull job title/description/questions from the current Upwork job page or open slider.
	 * @returns {{ title: string, description: string, client_name: string, questions: string[], url: string }}
	 */
	function extractCoverLetterJob() {
		const root =
			document.querySelector('[slidername*="job-details"]') ||
			document.querySelector('.fe-job-details') ||
			document.querySelector('[data-test="JobDetails"]') ||
			document.body;

		const title =
			textOf(
				root.querySelector('h1[data-test="job-title"]') ||
					root.querySelector('h3.mb-0.h5') ||
					root.querySelector('h1') ||
					document.querySelector('h1[data-test="job-title"]') ||
					document.querySelector('h1')
			) || document.title.replace(/^\[LeadCliq\]\s*/, '').trim();

		const descriptionEl =
			root.querySelector('div.fe-job-details div.description') ||
			root.querySelector('[data-test="Description"]') ||
			root.querySelector('.job-description') ||
			root.querySelector('[data-test="JobDescription"]') ||
			root.querySelector('.description');

		let description = textOf(descriptionEl);

		// Fallback: scrape from full details text between Summary and Skills / Activity
		if (!description || description.length < 40) {
			const blob = (root.innerText || '').trim();
			const m = blob.match(
				/(?:Summary|Job Description)\s*([\s\S]*?)(?:Mandatory skills|Preferred qualifications|Skills and Expertise|Activity on this job|About the client|$)/i
			);
			if (m) {
				description = m[1].trim();
			}
		}

		const client_name =
			textOf(
				document.querySelector('[data-qa="about-buyer-client-name"] strong') ||
					document.querySelector('[data-test="AboutClientUserName"]') ||
					root.querySelector('[data-qa="client-name"]')
			) || '';

		const questions = [];
		const seen = new Set();
		const pushQ = (q) => {
			const t = (q || '').replace(/\s+/g, ' ').trim();
			if (!t || t.length < 5) return;
			if (/cover letter/i.test(t)) return;
			const key = t.toLowerCase();
			if (seen.has(key)) return;
			seen.add(key);
			questions.push(t);
		};

		const questionNodes = root.querySelectorAll(
			'[data-test="Questions"] li, [data-test="ScreeningQuestions"] li, .fe-job-questions li, [data-test="Question"]'
		);
		questionNodes.forEach((node) => pushQ(textOf(node)));

		// Proposal apply page: questions are labels next to answer textareas
		const Proposal = global.LeadCliqUpworkProposal;
		if (Proposal?.extractFormQuestions) {
			Proposal.extractFormQuestions().forEach(pushQ);
		}

		return {
			title: title || 'Upwork job',
			description: description || '',
			client_name,
			questions,
			url: location.href,
		};
	}

	function textOf(el) {
		if (!el) return '';
		return (el.innerText || el.textContent || '').replace(/\u00a0/g, ' ').trim();
	}

	global.LeadCliqUpworkScan = {
		sleep,
		randomBetween,
		isLoggedIn,
		isCloudflareChallenge,
		waitForJobsOrCloudflare,
		getChallengePayload,
		getPageState,
		jobCount,
		listJobs,
		extractJobDetails,
		extractCoverLetterJob,
		closeJobSlider,
		markScanTab,
		unmarkScanTab,
		softRefresh,
	};
})(typeof window !== 'undefined' ? window : self);
