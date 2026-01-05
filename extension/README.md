# Owinace Assistant - Chrome Extension

**Owinace Assistant** is a powerful Chrome extension that helps freelancers and professionals generate winning cover letters and manage leads directly from job platforms. Save time and increase your success rate with AI-powered proposal generation.

## 🚀 Features

- **🔐 Secure Authentication**: Login with your Owinace account to access all features
- **✍️ AI-Powered Cover Letters**: Generate professional cover letters from job descriptions in seconds
- **📊 Lead Management**: Create and track leads directly from job pages
- **🎯 Auto-Extraction**: Automatically extracts job descriptions, titles, and client information
- **💼 Platform Support**: Currently supports Upwork with more platforms coming soon
- **👥 Team Collaboration**: Select your team and manage proposals with your team members

## 🌐 Supported Platforms

- **Upwork** ✅ - Full support for job descriptions and cover letter application

### Coming Soon
- **Fiverr** - Extract job details and create leads
- **PeoplePerHour** - Complete platform integration
- **Freelancer** - Full feature support

## 📦 Installation

### From Chrome Web Store (Recommended)

1. Visit the Chrome Web Store
2. Search for "Owinace Assistant"
3. Click **"Add to Chrome"**
4. Click **"Add Extension"** to confirm

### Manual Installation (Development)

1. Open `chrome://extensions` in Chrome
2. Enable **Developer mode** (toggle in top-right corner)
3. Click **"Load unpacked"**
4. Select the `extension/` folder from this repository

## 🎯 Quick Start Guide

### 1. Login

1. Click the **Owinace** extension icon in your Chrome toolbar
2. Enter your email and password
3. Click **Login**

### 2. Generate Cover Letter

1. Navigate to a job posting on Upwork
2. Click the extension icon
3. Go to the **Cover Letter** tab
4. Click **"Auto-fill from Page"** to extract the job description automatically
5. Adjust settings:
   - **Words**: Set desired length (150-1000 words)
   - **Level**: Choose beginner, intermediate, or professional
6. Click **"Generate Cover Letter"**
7. Review and copy your generated cover letter

### 3. Create a Lead

1. Navigate to a job page
2. Click the extension icon
3. Go to the **Lead** tab
4. Select your team from the dropdown
5. Click **"Auto-fill from Page"** to extract lead information
6. Review and adjust:
   - **Title**: Job title (auto-filled)
   - **Description**: Job description (optional)
   - **Source**: Lead source
   - **Stage**: Current stage
   - **Expected Value**: Expected project value
7. Click **"Submit Lead"**

### 4. Manage Teams

1. Click the extension icon
2. Go to the **Team** tab
3. Select your active team from the dropdown
4. All proposals and leads will be associated with your selected team

## 🏗️ Architecture

The extension uses a modular, extensible platform interface system that makes it easy to add support for new job platforms.

### Core Components

- **BasePlatform** (`platforms/base.js`): Abstract base class defining the platform interface
- **Platform Implementations**: Individual platform classes (currently `UpworkPlatform`, with more coming soon)
- **PlatformManager** (`platforms/manager.js`): Automatically detects and manages platform instances

### Platform Interface

All platform classes implement the following methods:

- `getName()`: Returns platform identifier (e.g., 'upwork')
- `matches(hostname)`: Checks if current page matches this platform
- `findJobDescription()`: Extracts job description from the page
- `getJobTitle()`: Extracts job title from the page
- `applyCoverLetter(coverLetter)`: Applies cover letter to application form
- `findLead()`: Extracts lead information (title, description, budget, contact, etc.)

## 🔌 API Integration

The extension communicates with the Owinace backend API:

- `POST /api/extension/login` - User authentication
- `GET /api/extension/teams` - Get user's teams
- `GET /api/extension/lead-form-data/:team_id` - Get lead form data (sources, stages)
- `POST /api/extension/coverletter` - Generate cover letter
- `POST /api/extension/lead` - Create a new lead
- `POST /api/extension/logout` - User logout

## 📁 File Structure

```
extension/
├── manifest.json          # Extension manifest (MV3)
├── popup.html            # Popup UI interface
├── popup.js              # Popup logic and event handlers
├── background.js         # Service worker (API calls)
├── content.js            # Content script (page interaction)
├── platforms/
│   ├── base.js           # Base platform interface
│   ├── upwork.js         # Upwork platform implementation (active)
│   └── manager.js        # Platform detection and management
│   # Note: Fiverr, PeoplePerHour, and Freelancer implementations coming soon
├── icons/
│   ├── icon16.png       # 16x16 icon
│   ├── icon48.png       # 48x48 icon
│   └── icon128.png      # 128x128 icon
└── README.md             # This file
```

## 🛠️ Development

### Adding a New Platform

1. Create a new platform file in `platforms/` (e.g., `platforms/newplatform.js`):

```javascript
class NewPlatformPlatform extends BasePlatform {
  getName() {
    return 'newplatform';
  }

  matches(hostname) {
    return hostname.includes('newplatform.com');
  }

  async findJobDescription() {
    const element = await this.waitForElement('.job-description');
    return element ? element.innerText.trim() : '';
  }

  async getJobTitle() {
    const text = await this.extractText('h1.job-title');
    return text || 'Untitled Job';
  }

  async applyCoverLetter(coverLetter) {
    const textarea = await this.waitForElement('textarea#proposal');
    if (textarea) {
      textarea.value = coverLetter;
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
      return true;
    }
    return false;
  }

  async findLead() {
    return {
      title: await this.getJobTitle(),
      description: await this.findJobDescription(),
      url: window.location.href,
      platform: this.getName(),
    };
  }
}

if (typeof window !== 'undefined') {
  window.NewPlatformPlatform = NewPlatformPlatform;
}
```

2. Register the platform in `platforms/manager.js`:

```javascript
function initializePlatforms() {
  if (platforms) return platforms;

  platforms = [
    new UpworkPlatform(),
    // Add new platforms here as they're implemented
    // new FiverrPlatform(),
    // new PeoplePerHourPlatform(),
    // new FreelancerPlatform(),
    new NewPlatformPlatform(), // Add here
  ];

  return platforms;
}
```

3. Add the script to `manifest.json` content_scripts array

## 🔒 Privacy & Security

- **Secure Authentication**: All API calls use HTTPS encryption
- **Token Storage**: Authentication tokens are stored securely in Chrome's local storage
- **No Data Collection**: The extension only communicates with your Owinace account
- **Privacy Policy**: [View our Privacy Policy](https://owinace.com/privacy-policy)

## 🐛 Troubleshooting

### Extension Not Working

- Ensure you're logged in to your Owinace account
- Check that you're on Upwork (currently the only supported platform)
- Refresh the page and try again

### Job Description Not Extracted

- Verify you're on a valid job posting page
- Try manually entering the job description
- Check browser console for errors (F12)

### Cover Letter Not Applied

- Ensure you're on the application page for the job
- Some platforms may require manual copy-paste
- Check that the form textarea is visible on the page

### Login Issues

- Verify your email and password are correct
- Check your internet connection
- Ensure the Owinace service is available

## 📝 Version History

### Version 0.2.0
- Initial release
- **Upwork support** - Full integration with job descriptions and cover letter application
- AI-powered cover letter generation
- Lead management integration
- Team collaboration features
- *More platforms (Fiverr, PeoplePerHour, Freelancer) coming in future updates*

## 📞 Support

For support, feature requests, or bug reports:

- **Website**: [https://owinace.com](https://owinace.com)
- **Email**: support@owinace.com
- **Privacy Policy**: [https://owinace.com/privacy-policy](https://owinace.com/privacy-policy)

## 📄 License

Copyright © 2025 Owinace. All rights reserved.

---

**Made with ❤️ for freelancers and professionals**



-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxgFv4v6AfK/kFlnt/X/X
5X4iUgLHFc/4BAksSXKetff/XbZOzRP+GoMvyt2/hul5VxvtpwnxKvXSTsrEMzFd
JktWk6n/FeQF55oD+zxzswWy2khbfGE4royOr63h8rWutgrZR/lKmJ+MBYp54oFM
lu6IScHW1shg9/jMewAW/XH6QuDP/VQAaAz4BpSorpOCtdcxp32nJH8+wGOno4Gj
174IEyGTVNJVIKSxB86zuup23rysAlSjtSs5Vt8uva08mNrsVDU14FZiir9DrLaF
48crQ+PzrevHPYOvmoc5+LMNCoLakS93Pgc74Gbvd/AHKJFleL+KQ8Zpk64JPN2a
QwIDAQAB
-----END PUBLIC KEY-----