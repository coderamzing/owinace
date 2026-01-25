/**
 * Upwork Platform Implementation
 */
class UpworkPlatform extends BasePlatform {
  getName() {
    return 'upwork';
  }

  matches(hostname) {
    return hostname.includes('upwork.com');
  }

  async findJobDescription() {
    const selectors = [
      'div.fe-job-details div.description',
    ];
    for (const selector of selectors) {
      const text = this.extractText(selector);
      if (text && text.length > 0) {
        return text;
      }
    }
    return '';
  }

  async getJobTitle() {
    const selectors = [
      'h1[data-test="job-title"]',
      'h1.job-title',
      'h1.break-words',
      'h1',
    ];

    for (const selector of selectors) {
      const text = this.extractText(selector);
      if (text && text.length > 0) {
        return text;
      }
    }

    return 'Untitled Job';
  }

  async applyCoverLetter(coverLetter) {
    // Upwork proposal textarea selectors
    const selectors = [
      '.fe-proposal-additional-details .cover-letter-area textarea.inner-textarea',
    ];
    for (const selector of selectors) {
      const textarea = await this.waitForElement(selector, 500);
      if (textarea) {
        textarea.value = coverLetter;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        textarea.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
      }
    }
    return false;
  }

  async getLeadCost() {
    const cost = await this.extractText('div.fe-job-details ul.fe-ui-job-features div[data-cy="fixed-price"] + strong');
    let cleanedCost = cost && cost.length > 0 ? cost.replace(/[^0-9.]/g, '') : '';
    return cleanedCost;
  }

  async findLead() {
    const title = await this.extractText('.fe-job-details h3');
    const contact = await this.extractText('.fe-client-info [data-qa="about-buyer-client-name"] strong');
    const url = await this.extractAttr('.fe-job-details  a[data-test="open-original-posting"]', 'href');
    const description = await this.extractText('.fe-job-details div.description');
    const source = 'upwork';
    const stage = 'open';
    const cost = await this.getLeadCost();
    return {
      title,
      contact,
      url: "https://www.upwork.com" + url.trim(),
      description,
      source,
      stage,
      platform: this.getName(),
      cost,
    };
  }
}

// Export for use in content script
if (typeof window !== 'undefined') {
  window.UpworkPlatform = UpworkPlatform;
}
