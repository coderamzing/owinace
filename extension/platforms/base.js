/**
 * Base Platform Interface
 * All platform implementations must extend this class
 */
class BasePlatform {
  constructor() {
    if (this.constructor === BasePlatform) {
      throw new Error("BasePlatform cannot be instantiated directly");
    }
  }

  /**
   * Get the platform name
   * @returns {string}
   */
  getName() {
    throw new Error("getName() must be implemented");
  }

  /**
   * Detect if current page matches this platform
   * @param {string} hostname - Current page hostname
   * @returns {boolean}
   */
  matches(hostname) {
    throw new Error("matches() must be implemented");
  }

  /**
   * Find and extract job description from the page
   * @returns {Promise<string>} Job description text
   */
  async findJobDescription() {
    throw new Error("findJobDescription() must be implemented");
  }

  /**
   * Apply cover letter to the job application form
   * @param {string} coverLetter - The cover letter text to apply
   * @returns {Promise<boolean>} Success status
   */
  async applyCoverLetter(coverLetter) {
    throw new Error("applyCoverLetter() must be implemented");
  }

  /**
   * Find and extract lead information from the page
   * @returns {Promise<Object>} Lead data object with title, description, url, etc.
   */
  async findLead() {
    throw new Error("findLead() must be implemented");
  }

  /**
   * Get job title from the page
   * @returns {Promise<string>} Job title
   */
  async getJobTitle() {
    throw new Error("getJobTitle() must be implemented");
  }

  /**
   * Get job URL
   * @returns {string} Current page URL
   */
  getJobUrl() {
    return window.location.href;
  }

  /**
   * Helper method to wait for element to appear
   * @param {string} selector - CSS selector
   * @param {number} timeout - Timeout in milliseconds
   * @returns {Promise<Element|null>}
   */
  async waitForElement(selector, timeout = 5000) {
    return new Promise((resolve) => {
      const element = document.querySelector(selector);
      if (element) {
        resolve(element);
        return;
      }

      const observer = new MutationObserver(() => {
        const el = document.querySelector(selector);
        if (el) {
          observer.disconnect();
          resolve(el);
        }
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true,
      });

      setTimeout(() => {
        observer.disconnect();
        resolve(null);
      }, timeout);
    });
  }

  /**
   * Helper method to extract text from element
   * @param {string} selector - CSS selector
   * @returns {string|null}
   */
  extractText(selector) {
    const element = document.querySelector(selector);
    return element ? element.innerText.trim() : null;
  }

  /**
   * Helper method to extract attribute from element
   * @param {string} selector - CSS selector
   * @param {string} attr - Attribute name
   * @returns {string|null}
   */
  extractAttr(selector, attr) {
    const element = document.querySelector(selector);
    return element ? element.getAttribute(attr) : null;
  }
  
}

// Export for use in other scripts
if (typeof window !== 'undefined') {
  window.BasePlatform = BasePlatform;
}
