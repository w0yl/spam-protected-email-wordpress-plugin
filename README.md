# Spam Protected Email for WordPress

An inline WordPress Block Editor (Gutenberg) format tool that obfuscates email addresses to shield them from spam bots and web scrapers.

This plugin implements the [email obfuscation techniques developed by Spencer Mortensen](https://spencermortensen.com/articles/email-obfuscation/):

- Method 1.5 (Display None): Injects decoy HTML tags to confuse raw scrapers reading the page source.

 - Method 2.8 (Conversion JS): Scrambles the href attribute on the server and uses browser-side JavaScript on DOMContentLoaded to dynamically build the mailto: link.
---

## Features

- Gutenberg Integration: Adds a "Protect Email" button directly to the Block Editor inline toolbar alongside Bold, Italic, and Link tools.

- Smart Field Extraction: Automatically extracts highlighted email text into the setup popover and remembers previously saved addresses when editing.

- Dual-Layer Protection: Combines client-side DOM manipulation and HTML decoy elements for maximum bot resistance.

- Zero Dependencies: Uses native WordPress scripts and standard vanilla JavaScript without external libraries.

- Clean Fallback: Gracefully degrades while maintaining accessible text markup.

---

## Installation & Setup

1. Download the plugin code [spam-protected-email-wordpress-plugin.zip](https://github.com/w0yl/spam-protected-email-wordpress-plugin/releases/latest/download/spam-protected-email-wordpress-plugin.zip) from the [latest release](https://github.com/w0yl/spam-protected-email-wordpress-plugin/releases/latest).
2. Log in to your WordPress Dashboard and navigate to **Plugins --> Add Plugins --> Upload Plugin** and choose the downloaded plugin.
3. Activate the **Spam Protected Email** plugin.

---

## Usage in Block Editor

1. Open any post or page in the Block Editor.
2. Highlight text inside any standard Paragraph block (e.g., `info@example.com` or `Contact Us`).
3. Click the **Email** (envelope) icon in the inline block toolbar.
4. If your highlighted text contains `@`, the target email field will pre-fill automatically. Adjust if needed and click **Apply**.
5. Save or publish your content.

---

## Requirements

- **WordPress:** 6.0+
- **PHP:** 7.4+
- **Theme:** Any Gutenberg-compatible theme (e.g., Twenty Twenty-Five).

---

## License
The code has been Licensed under GNU GPLv2.