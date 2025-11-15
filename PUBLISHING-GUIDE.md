# How to Publish FlowQ to WordPress.org

Complete step-by-step guide to publishing your plugin to the WordPress Plugin Directory.

---

## Prerequisites

Before you begin, make sure you have:

- [x] WordPress.org account (free)
- [x] Plugin is complete and tested
- [x] All files are ready (readme.txt, main plugin file, etc.)
- [x] Plugin follows WordPress.org guidelines (✅ FlowQ is compliant!)

---

## Step 1: Create WordPress.org Account

If you don't have an account:

1. Go to https://login.wordpress.org/register
2. Fill in your details:
   - Username
   - Email address
   - Create password
3. Verify your email
4. Complete your profile at https://profiles.wordpress.org/

**Tip:** Use the same email you'll use for plugin support.

---

## Step 2: Request Plugin Hosting

### 2.1 Prepare Your Plugin ZIP

1. Navigate to the plugin folder:
   ```
   D:\projects\wordpress-site\wp-content\plugins\flowq
   ```

2. Create a ZIP file of the plugin:
   - Select all files and folders
   - Right-click → "Compress to ZIP file"
   - Name it: `flowq-1.0.0.zip` or `flowq.zip`

**Important:** The ZIP should contain the plugin folder contents, NOT a folder inside a folder.

**Correct structure:**
```
flowq.zip
├── survey-plugin.php
├── readme.txt
├── admin/
├── includes/
├── public/
├── assets/
└── languages/
```

**Incorrect structure:**
```
flowq.zip
└── flowq/
    ├── survey-plugin.php
    └── ...
```

### 2.2 Submit Plugin Request

1. Go to: https://wordpress.org/plugins/developers/add/

2. Log in with your WordPress.org account

3. Fill out the form:

   **Plugin Name:** FlowQ

   **Plugin Description:**
   ```
   Create intelligent, dynamic surveys that adapt in real-time to user responses. Features conditional branching, 5 beautiful templates, participant tracking, and privacy-first design. Perfect for customer feedback, lead generation, and interactive assessments.
   ```

   **Plugin URL (optional):** https://github.com/dev-parvej/FlowQ

4. Upload your plugin ZIP file

5. Agree to the guidelines checkbox

6. Click **"Submit Plugin"**

### 2.3 What Happens Next

- You'll receive an automated email confirming submission
- WordPress.org team will review your plugin (usually 2-4 weeks)
- You'll get an email with one of these outcomes:
  - **Approved** - Plugin hosting granted! 🎉
  - **Needs Changes** - Issues to fix before approval
  - **Rejected** - Violates guidelines (unlikely for FlowQ)

---

## Step 3: After Approval

Once approved, you'll receive an email with:
- Your plugin slug (e.g., `flowq` or `flowq`)
- SVN repository URL
- Instructions for uploading

**Example email will include:**
```
Congratulations! Your plugin has been approved.

Plugin Slug: flowq
SVN URL: https://plugins.svn.wordpress.org/flowq/
```

---

## Step 4: Install SVN (Subversion)

WordPress.org uses SVN for version control.

### Windows:

1. Download TortoiseSVN: https://tortoisesvn.net/downloads.html
2. Install it (restart may be required)
3. Right-click in any folder → You should see "SVN Checkout" option

### Mac:

```bash
brew install svn
```

### Linux:

```bash
sudo apt-get install subversion
```

---

## Step 5: Checkout SVN Repository

### Using TortoiseSVN (Windows):

1. Create a folder for your plugin: `C:\FlowQ-SVN\`

2. Right-click inside the folder → **"SVN Checkout"**

3. Enter repository URL:
   ```
   https://plugins.svn.wordpress.org/flowq/
   ```

4. Click **OK** to download

### Using Command Line:

```bash
svn checkout https://plugins.svn.wordpress.org/flowq/ flowq-svn
cd flowq-svn
```

You'll see this structure:
```
flowq/
├── trunk/
├── tags/
├── branches/
└── assets/
```

---

## Step 6: Add Plugin Files to Trunk

### 6.1 Copy Plugin Files

1. Copy ALL plugin files from:
   ```
   D:\projects\wordpress-site\wp-content\plugins\flowq\
   ```

2. Paste into the `trunk/` folder:
   ```
   flowq-svn/trunk/
   ```

### 6.2 Add Assets (Optional but Recommended)

Create banner and icon images:

**Required sizes:**
- `banner-772x250.png` - Plugin banner
- `banner-1544x500.png` - Retina banner (2x)
- `icon-128x128.png` - Plugin icon
- `icon-256x256.png` - Retina icon (2x)

**Screenshot naming:**
- `screenshot-1.png` - First screenshot
- `screenshot-2.png` - Second screenshot
- etc.

Copy these to `assets/` folder:
```
flowq-svn/assets/
├── banner-772x250.png
├── banner-1544x500.png
├── icon-128x128.png
├── icon-256x256.png
├── screenshot-1.png
├── screenshot-2.png
└── ...
```

---

## Step 7: Add Files to SVN

### Using TortoiseSVN:

1. Right-click in `trunk/` folder
2. Select **"TortoiseSVN" → "Add"**
3. Check all files/folders
4. Click **OK**

### Using Command Line:

```bash
cd trunk
svn add --force * --auto-props --parents --depth infinity -q
```

---

## Step 8: Commit to SVN

### Using TortoiseSVN:

1. Right-click in `flowq-svn/` folder
2. Select **"SVN Commit"**
3. Enter commit message:
   ```
   Initial commit of FlowQ 1.0.0
   ```
4. Check all files to commit
5. Click **OK**
6. Enter WordPress.org username and password

### Using Command Line:

```bash
cd ..  # Back to flowq-svn/
svn commit -m "Initial commit of FlowQ 1.0.0"
```

Enter your WordPress.org credentials when prompted.

---

## Step 9: Create Release Tag

### 9.1 Copy Trunk to Tags

### Using TortoiseSVN:

1. Right-click in `flowq-svn/` folder
2. Select **"TortoiseSVN" → "Branch/Tag"**
3. From: `/trunk`
4. To: `/tags/1.0.0`
5. Enter commit message:
   ```
   Tagging version 1.0.0
   ```
6. Click **OK**

### Using Command Line:

```bash
svn copy trunk tags/1.0.0
svn commit -m "Tagging version 1.0.0"
```

---

## Step 10: Verify Plugin on WordPress.org

1. Wait 10-15 minutes for WordPress.org to process

2. Visit your plugin page:
   ```
   https://wordpress.org/plugins/flowq/
   ```

3. Verify:
   - [x] Plugin appears in directory
   - [x] Download button works
   - [x] Screenshots display
   - [x] Readme text shows correctly
   - [x] Version is 1.0.0

**Congratulations! Your plugin is now live! 🎉**

---

## Managing Updates

### To Release Version 1.0.1:

1. Update `survey-plugin.php` version:
   ```php
   Version: 1.0.1
   ```

2. Update `readme.txt` changelog:
   ```
   == Changelog ==

   = 1.0.1 =
   * Bug fixes
   * Performance improvements

   = 1.0.0 =
   * Initial release
   ```

3. Copy updated files to `trunk/`

4. Commit changes:
   ```bash
   svn commit -m "Update to version 1.0.1"
   ```

5. Create new tag:
   ```bash
   svn copy trunk tags/1.0.1
   svn commit -m "Tagging version 1.0.1"
   ```

---

## Alternative: Using GitHub to SVN (Advanced)

You can automate SVN deployment from GitHub:

1. Use GitHub Actions
2. Auto-deploy to WordPress.org SVN on new releases
3. See: https://github.com/10up/action-wordpress-plugin-deploy

---

## Common Issues & Solutions

### Issue: "Authorization failed"
**Solution:** Verify WordPress.org username/password. Use WordPress.org credentials, not your site's.

### Issue: "File already exists"
**Solution:** Update existing files instead. Use `svn update` before committing.

### Issue: "Plugin not showing in directory"
**Solution:** Wait 15-30 minutes. Clear browser cache. Check https://wordpress.org/plugins/flowq/

### Issue: "Download showing old version"
**Solution:** Make sure you created the tag (`tags/1.0.0`). Trunk alone won't be downloadable.

### Issue: "Screenshots not showing"
**Solution:** Screenshots go in `assets/` folder, not `trunk/`. Name them `screenshot-1.png`, etc.

---

## Important Tips

1. **Always test locally first** before committing to SVN
2. **Never commit directly to tags** - Always modify trunk, then tag
3. **Use meaningful commit messages**
4. **Increment version numbers** for each release
5. **Update readme.txt changelog** for every version
6. **Keep stable tag updated** in readme.txt
7. **Test on clean WordPress install** before releasing

---

## Support After Publishing

1. Monitor WordPress.org support forum:
   ```
   https://wordpress.org/support/plugin/flowq/
   ```

2. Respond to user questions within 48 hours

3. Fix reported bugs quickly

4. Update plugin regularly (every 2-3 months at minimum)

---

## Resources

- **Plugin Developer Handbook:** https://developer.wordpress.org/plugins/
- **SVN Guide:** https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
- **readme.txt Validator:** https://wordpress.org/plugins/developers/readme-validator/
- **Plugin Review Team:** https://make.wordpress.org/plugins/
- **Support Forum Guidelines:** https://wordpress.org/support/guidelines/

---

## Quick Reference Commands

```bash
# Checkout repository
svn checkout https://plugins.svn.wordpress.org/flowq/ flowq-svn

# Add all files
svn add --force * --auto-props --parents --depth infinity -q

# Commit changes
svn commit -m "Your message here"

# Create tag
svn copy trunk tags/1.0.0
svn commit -m "Tagging version 1.0.0"

# Update from server
svn update

# Check status
svn status

# View differences
svn diff
```

---

## Checklist Before Publishing

- [ ] Plugin works perfectly on local install
- [ ] All placeholders replaced with real data
- [ ] readme.txt is complete and validated
- [ ] Screenshots are ready (in assets/)
- [ ] Version numbers match (plugin header and readme.txt)
- [ ] Changelog is up to date
- [ ] All links in readme.txt work
- [ ] Plugin tested with latest WordPress version
- [ ] No PHP errors or warnings
- [ ] Compatible with popular themes/plugins

---

**Need Help?**

- WordPress.org Forums: https://wordpress.org/support/
- GitHub Issues: https://github.com/dev-parvej/FlowQ/issues
- Developer Slack: https://make.wordpress.org/chat/

---

**Good luck with your plugin submission!** 🚀

*Last updated: 2024*
