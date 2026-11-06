# RSSA local WordPress

This directory contains the custom WordPress theme and Telegram plugin used at https://rssa.usi.ch, plus a separate local development environment. The original static site is retained at the repository root. For routine live code updates, see `deployment/PUBLISH.md`; the initial installation notes are historical.

## Start

Run from this directory:

```sh
npm install
npm start
```

Open http://127.0.0.1:9400/ for the site and http://127.0.0.1:9400/wp-admin/admin.php?page=rssa-content for editing. Stop with Ctrl-C.

The server binds only to 127.0.0.1. Node is installed as a project-local dependency; the global installation is not changed. WordPress Playground runs PHP 8.3 and WordPress 7.1.3 with a local SQLite database. The first start needs internet access to download WordPress. Later starts reuse the existing installation.

## Local demonstration accounts

- Content editor: `katya-preview`, password `rssa-local-preview`.
- Administrator: `admin`, password `password` (Playground default).

These accounts are exclusively for this loopback development environment. Never deploy this runtime or these accounts to a server. Katya's production account will be created separately.

## Editing

Select **Website content**, edit text or choose/upload photos, and **Save changes**. Then use **View website** to review the result. Formatting buttons preserve bold, italic, and line breaks; Shift-Enter creates a line break. Photo slots include the logo, group photo, and three team portraits. Restore original returns a photo slot to its bundled image without deleting uploaded files.

The layout, navigation destinations, contact form behavior, and Telegram/Juicer integration remain in the theme. This first version edits the existing three team profiles; it does not add/reorder profiles. Social handle/address labels should not be changed without also updating their corresponding link destinations in the theme.

The RSSA Content Editor role can edit website content and upload media, but cannot install plugins, change themes, or manage users. Saving publishes immediately to the local site; there is no draft/revision workflow yet. Avoid simultaneous edits to this shared content form.

## Source and persistence

- `theme/`: portable custom WordPress theme, including current design assets.
- `theme/content.json`: default values and editor field definitions.
- `runtime/`: ignored WordPress installation, SQLite database, and uploaded photos. Keep this folder to preserve edits across restarts.
- `blueprint.json`: reproducible initial setup.
- `scripts/check.php`: local capability/output filtering checks; run with `php scripts/check.php`.

Back up `runtime/` while the server is stopped, plus `theme/`. Do not commit runtime data or node_modules. On a new machine, content edits/uploads require transferring a backup; Git alone contains only theme defaults.

## Before USI deployment

Verify WordPress requirements against USI's actual PHP/MySQL versions and file permissions, establish backups and updates, and create production credentials. Install WordPress fresh with MySQL and migrate theme/content/media deliberately. Do not upload this entire development folder or the local SQLite integration. Review remaining SEO metadata and the inherited mailto/feed behavior before launch.

## Event gallery
In Website content, use Event photo gallery → Add gallery photos. Upload or select multiple images, add captions/event names and photo descriptions, then Save changes. Move up/down changes the display order. Remove takes a photo out of the gallery without deleting its media file. An empty gallery is hidden. Visitors can open full-size images. This is one shared gallery, not separate event albums.

## Production packages

See `deployment/DEPLOYMENT.md` for the checked installation sequence, unresolved USI hosting checks, backup/rollback plan, and external-feed caveats. Run `sh deployment/build.sh` from this directory to refresh the two ZIP files in ignored `release/` after content changes. It exports only referenced original photos and editable content, with no users or credentials. The transfer plugin is for a fresh installation only; remove it after import. Run `node deployment/check-http.mjs` while the local preview is running for HTTP/asset/indexing checks.
