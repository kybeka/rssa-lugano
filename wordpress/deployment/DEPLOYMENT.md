# RSSA production deployment

Historical initial installation checklist prepared 2026-11-03. WordPress has since been installed on USI. Status claims below describe the original rehearsal, not current production. For routine code updates use PUBLISH.md.

## Package contents

- `rssa-theme.zip`: installable RSSA theme, preserving the current design and editor.
- `rssa-content-transfer.zip`: temporary administrator-only importer with current text, gallery, and two original uploaded photos. No users, passwords, database, localhost settings, or development runtime.
- `SHA256SUMS`: package checksums.

The example gallery photo/caption is included. Review it before launch. These packages are snapshots; regenerate after further content edits.

## Verified locally

- PHP syntax and JavaScript syntax checks.
- Existing editor role restrictions and text output filtering.
- Gallery original-image fallback (local AVIF thumbnail generation produced empty large derivatives).
- Mobile navigation, Escape dismissal, no horizontal overflow at 390px, valid section anchors, and required contact fields.
- One WordPress robots directive: development is noindex. Non-production origins are always noindex.
- Separate clean WordPress 7.1.4 installation: imported two original photos; verified text and gallery; repeat import and anonymous import blocked. Rehearsal used SQLite and host PHP, not USI MySQL.
- Public https://rssa.usi.ch/ responds 200 with a valid TLS connection and the existing 238-byte placeholder. No remote files were changed.

## Hosting prerequisites still to verify

Use WordPress Tools → Site Health after installation, or obtain these from IT before installing:

- PHP 8.3+ recommended (theme requires at least PHP 8.0).
- MySQL 8.0+ or MariaDB 10.11+ recommended; usable mysqli extension and assigned database access.
- PHP file uploads, writable uploads/temp directories, GD or Imagick image processing; test a normal JPEG and an AVIF if AVIF uploads are desired.
- Outbound HTTPS for updates and available update mechanism (automatic filesystem writes or SFTP).
- Actual storage quota and backup/restore policy. The earlier 700GB disk figure was a shared filesystem size, not an account allocation.

Official requirements: https://wordpress.org/about/requirements/
Official core download: https://wordpress.org/download/ (7.1.4 at preparation time; verify again at installation).

## Install over SFTP and the WordPress dashboard

1. Download a backup of `/usi_rssa/docs` and any existing database. Keep credentials and backup files outside the public document root.
2. Arrange restricted staging access with USI, or schedule installation in a maintenance window. An exposed unfinished WordPress installer should not be left unattended. Keep search indexing disabled until all acceptance checks pass; noindex is not access control.
3. Install fresh official WordPress core under `/usi_rssa/docs`, configured for the assigned MySQL database. Read the account details privately from `/usi_rssa/conf/MySQL.account_info`. Do not copy the local runtime, SQLite plugin/db.php, wp-config.php, demo accounts, or node_modules. Use a fresh database/table prefix and production salts.
4. Complete the installer yourself with a new strong administrator password and your own email. Set both site URLs to `https://rssa.usi.ch`, site name RSSA Lugano, timezone Europe/Zurich. Start with plain permalinks; fancy permalinks are unnecessary for this one-page site.
5. Install/activate `rssa-theme.zip` in Appearance → Themes. If dashboard ZIP installation is unavailable, unzip locally and upload `rssa/` to `wp-content/themes/` using SFTP.
6. Install/activate `rssa-content-transfer.zip` in Plugins, or upload its extracted directory to `wp-content/plugins/`. Open Tools → RSSA content transfer and select Import prepared content once. This requires a fresh RSSA installation and refuses to overwrite existing content.
7. Review all text/photos and the gallery. Deactivate and DELETE the transfer plugin after success; retain the package offline. Its content files are intended for publication but need not remain on the server.
8. Create Katya's account with role RSSA Content Editor, using her confirmed email and a new password. Test her login, text save, JPEG upload, gallery ordering and removal. Verify she cannot manage users, plugins, or themes. Test password reset/email delivery; if USI blocks outgoing mail, agree on an account recovery procedure before launch.
9. Retain the old placeholder outside the public root. Remove/rename its `index.html` at cutover so it cannot override WordPress's `index.php`. Preserve USI-managed configuration and `/mysqladmin/`; do not overwrite them with a generic server config or change permissions recursively.
10. Check HTTPS without browser warnings, no localhost URLs in rendered content, every image, mobile navigation, the gallery, direct social links and the email workflow. The form only opens the visitor's email app; no message is sent or stored by WordPress.
11. Remove default sample posts/pages/comments; review sitemaps so sample content is not indexed. Under Settings → Reading allow search engines only when ready to launch. Verify the final robots meta and robots.txt contain no unintended noindex/Disallow rules. Our theme's non-production noindex guard also requires the exact HTTPS production origin.

## Event feed and content caveats

Telegram remains the source of truth. Juicer currently renders posts, but some Telegram-hosted thumbnails fail and the visible posts are several months old. Failed thumbnails are hidden; a direct Telegram link remains available, and a fallback appears if no posts load. Verify the RSS/Juicer feed refresh against Katya's recent Telegram announcements before describing it as current. Do not promise that this hosting move fixes the external feed.

Google Fonts, Juicer and Telegram assets remain external requests. Review these services against USI's website requirements. Existing social handle/email labels do not change hardcoded link destinations. The editor publishes immediately and has no revisions or concurrent-edit protection; edit one at a time. Gallery captions are optional; include descriptive alt text when uploading photos.

## Backups, maintenance and rollback

Agree who owns updates and backups before launch. Back up both MySQL and wp-content (themes, plugins and uploads) daily and before changes, retain several recovery points outside the public root, and perform a restore rehearsal. Enable supported security updates; test larger updates in staging. SFTP credentials do not themselves provide database backups—use USI's backup service or authenticated phpMyAdmin export.

For a failed first launch, restore the original placeholder and keep WordPress inaccessible while investigating. For a later rollback restore the matching database AND file backup together; restoring only theme files does not undo content/media/database migrations. Keep database credentials and salts out of Git and public ZIP files.

## Telegram replacement (prepared, not connected)

The release also includes `rssa-telegram.zip`. Install it with the updated theme and follow `SETUP.md` inside the plugin. Its switch is off by default, retaining Juicer until live delivery is verified. The plugin adds its own announcements table and stores bot settings privately. No real bot token is configured and no live webhook delivery has been tested. The content-transfer package does not contain bot credentials or announcement history.
