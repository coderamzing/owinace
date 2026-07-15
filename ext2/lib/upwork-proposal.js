/**
 * Fill Upwork proposal form: terms, cover letter, and screening Q&A.
 * Mirrors the Playwright apply flow in upbot2/lib/upwork.js.
 */
(function (global) {
	'use strict';

	function textOf(el) {
		if (!el) return '';
		return (el.innerText || el.textContent || '').replace(/\u00a0/g, ' ').trim();
	}

	function normalize(str) {
		return String(str || '')
			.replace(/\s+/g, ' ')
			.trim()
			.toLowerCase();
	}

	function sleep(ms) {
		return new Promise((resolve) => setTimeout(resolve, ms));
	}

	/** Set value so Angular/React controlled inputs pick it up. */
	function setInputValue(el, value) {
		if (!el) return false;
		el.focus();
		const proto = el instanceof HTMLTextAreaElement ? HTMLTextAreaElement.prototype : HTMLInputElement.prototype;
		const setter = Object.getOwnPropertyDescriptor(proto, 'value')?.set;
		if (setter) {
			setter.call(el, value);
		} else {
			el.value = value;
		}
		el.dispatchEvent(new Event('input', { bubbles: true }));
		el.dispatchEvent(new Event('change', { bubbles: true }));
		return true;
	}

	function clickEl(el) {
		if (!el) return false;
		el.scrollIntoView({ block: 'center', inline: 'nearest' });
		el.click();
		return true;
	}

	/**
	 * Find the first visible element whose normalized text equals `needle`.
	 * @param {string} needle
	 * @param {{ exact?: boolean, last?: boolean }} [opts]
	 */
	function findByText(needle, opts = {}) {
		const exact = opts.exact !== false;
		const want = normalize(needle);
		const nodes = Array.from(document.querySelectorAll('button, label, span, div, li, a, p'));
		const matches = nodes.filter((el) => {
			const t = normalize(textOf(el));
			if (!t) return false;
			if (exact) return t === want;
			return t.includes(want);
		});
		if (!matches.length) return null;
		return opts.last ? matches[matches.length - 1] : matches[0];
	}

	/**
	 * Select a radio/option inside a section that contains `sectionText`.
	 * e.g. "How do you want to be paid?" → "By project"
	 */
	function selectRadio(sectionText, optionText) {
		const section =
			Array.from(document.querySelectorAll('section, fieldset, [data-test], .air3-card, form'))
				.find((el) => normalize(textOf(el)).includes(normalize(sectionText))) || null;

		if (!section) {
			const fallback = findByText(optionText, { exact: false });
			return clickEl(fallback);
		}

		const labels = Array.from(section.querySelectorAll('label, button, [role="radio"]'));
		const match = labels.find((el) => normalize(textOf(el)).includes(normalize(optionText)));
		return clickEl(match || null);
	}

	/**
	 * Open a custom dropdown by placeholder text, then pick an option.
	 * e.g. "Select a duration" → "Less than 1 month"
	 */
	async function selectDropdown(placeholder, option) {
		const trigger =
			findByText(placeholder, { exact: true }) ||
			document.querySelector(`[placeholder="${placeholder}"]`) ||
			document.querySelector(`button[aria-label*="${placeholder}"]`);

		if (!trigger) return false;
		clickEl(trigger);
		await sleep(250);

		const opt =
			findByText(option, { exact: true, last: true }) ||
			findByText(option, { exact: false, last: true });

		if (!opt) return false;
		clickEl(opt);
		await sleep(150);
		return true;
	}

	function fillCoverLetter(text) {
		const selectors = [
			'.fe-proposal-additional-details .cover-letter-area textarea.inner-textarea',
			'textarea[aria-labelledby*="cover-letter"]',
			'textarea[data-test="cover-letter"]',
			'.cover-letter-area textarea',
		];
		for (const sel of selectors) {
			const el = document.querySelector(sel);
			if (el && setInputValue(el, text)) {
				return true;
			}
		}
		// Label-based fallback
		const labels = Array.from(document.querySelectorAll('label'));
		for (const label of labels) {
			if (!/cover letter/i.test(textOf(label))) continue;
			const id = label.getAttribute('for');
			const el =
				(id && document.getElementById(id)) ||
				label.closest('.form-group, .cover-letter-area, div')?.querySelector('textarea') ||
				null;
			if (el && setInputValue(el, text)) return true;
		}
		// Last resort: first textarea whose aria-label mentions cover letter
		const byAria = Array.from(document.querySelectorAll('textarea')).find((el) =>
			/cover letter/i.test(el.getAttribute('aria-label') || '')
		);
		return byAria ? setInputValue(byAria, text) : false;
	}

	/**
	 * Questions shown on the proposal apply form (labels paired with textareas).
	 * @returns {string[]}
	 */
	function extractFormQuestions() {
		const questions = [];
		const groups = document.querySelectorAll(
			'.fe-proposal-additional-details .form-group, [data-test="Questions"] .form-group, .form-group'
		);
		groups.forEach((group) => {
			const label = group.querySelector('label');
			const textarea = group.querySelector('textarea');
			if (!label || !textarea) return;
			const q = textOf(label);
			if (!q || q.length < 5) return;
			if (/cover letter/i.test(q)) return;
			questions.push(q);
		});
		return questions;
	}

	/**
	 * Match AI answers onto form textareas by normalized question text.
	 * @param {Array<{ question?: string, answer?: string }>} qaList
	 * @returns {{ filled: number, skipped: string[] }}
	 */
	function fillQuestionAnswers(qaList) {
		const rows = Array.isArray(qaList) ? qaList : [];
		const skipped = [];
		if (!rows.length) return { filled: 0, skipped };

		const questionMap = new Map(
			rows
				.filter((r) => r && (r.question || r.answer))
				.map((r) => [normalize(r.question), String(r.answer || '').trim()])
		);

		const groups = document.querySelectorAll('.form-group');
		let filled = 0;

		groups.forEach((group) => {
			const label = group.querySelector('label');
			const textarea = group.querySelector('textarea');
			if (!label || !textarea) return;

			const questionText = normalize(textOf(label));
			if (!questionText || /cover letter/i.test(questionText)) return;

			let answer = questionMap.get(questionText);
			if (!answer) {
				// Soft match: either side contains the other
				for (const [q, a] of questionMap.entries()) {
					if (!q || !a) continue;
					if (questionText.includes(q) || q.includes(questionText)) {
						answer = a;
						break;
					}
				}
			}

			if (!answer) {
				skipped.push(textOf(label));
				return;
			}

			if (setInputValue(textarea, answer)) {
				filled += 1;
			}
		});

		return { filled, skipped };
	}

	/**
	 * Fixed-price terms: By project + Less than 1 month (no-op if controls absent).
	 * Hourly: leave alone aside from optional frequency (out of scope for manual fill).
	 */
	async function fillProposalTerms() {
		const body = normalize(document.body?.innerText || '');
		const result = { paidByProject: false, duration: false };

		if (body.includes(normalize('How do you want to be paid?'))) {
			result.paidByProject = selectRadio('How do you want to be paid?', 'By project');
			await sleep(200);
		}

		if (
			body.includes(normalize('Select a duration')) ||
			body.includes(normalize('How long will this project take?'))
		) {
			result.duration = await selectDropdown('Select a duration', 'Less than 1 month');
		}

		return result;
	}

	/**
	 * Full proposal fill used by the extension popup "Fill on Upwork" button.
	 * @param {{ coverLetter: string, questions?: Array<{ question: string, answer: string }> }} opts
	 */
	async function applyProposal(opts) {
		const coverLetter = String(opts?.coverLetter || '').trim();
		if (!coverLetter) {
			return { ok: false, error: 'Cover letter is empty' };
		}

		const terms = await fillProposalTerms();

		const coverOk = fillCoverLetter(coverLetter);
		if (!coverOk) {
			return { ok: false, error: 'Cover letter field not found on this page' };
		}

		const qa = fillQuestionAnswers(opts?.questions || []);

		return {
			ok: true,
			coverLetter: true,
			terms,
			questionsFilled: qa.filled,
			questionsSkipped: qa.skipped,
		};
	}

	global.LeadCliqUpworkProposal = {
		extractFormQuestions,
		fillCoverLetter,
		fillQuestionAnswers,
		fillProposalTerms,
		applyProposal,
	};
})(typeof window !== 'undefined' ? window : self);
