# Routine live updates

Run in a normal Terminal shell, not at the `sftp>` prompt:

```sh
bash /Users/kylakaplan/Desktop/rssa+/rssa/wordpress/deployment/release.sh
```

This command validates tracked WordPress source, commits any changes with a conventional message, pushes to GitHub, verifies the remote commit, then uploads that exact committed source to USI. With no changes, it reuses the existing commit. Enter your USI password at the SFTP prompt; it is not saved. VPN may be required by USI.

Optionally add a descriptive commit message:

```sh
bash /Users/kylakaplan/Desktop/rssa+/rssa/wordpress/deployment/release.sh "fix(gallery): adjust photo spacing"
```

Run on main. Existing staged changes, unreviewed new WordPress files, a changed GitHub destination, or remote changes missing locally stop the release. Only tracked WordPress files are automatically committed; unrelated uncommitted work stays local. Review and commit new source files separately before the first release that includes them. New deployed files also need to be added to the upload allowlist.

A failed GitHub push stops before SFTP. If SFTP fails afterward, the GitHub backup remains available; the script reports the failure rather than claiming a successful release. Correct the error and rerun. Do not run concurrent releases. If a killed process leaves the release lock, confirm no release is running before removing .git/rssa-release.lock.

For local validation without committing, pushing or connecting, run `bash wordpress/deployment/publish.sh --preview` from the repository root. The lower-level `publish.sh --publish` remains available for a code-only upload; the combined release command is the normal workflow.

The script validates PHP and JavaScript, freezes a local source snapshot, downloads the existing theme and Telegram plugin, stages the allowlisted files, then renames them into place. Each replacement is atomic, but the complete release is not. If an upload or backup fails, the batch stops; if a rename fails, some files may already have been replaced. Keep the terminal output and backup location for recovery. Do not run two deployments simultaneously.

Backups and manifests are private local files in `~/Library/Application Support/RSSA/deployments/`. These are code backups, not database or uploaded-media backups. Nothing is deleted from that backup directory automatically.

Only the custom theme, its bundled images, and the Telegram plugin PHP file are uploaded. WordPress core, wp-config.php, uploads, database content, tokens, demo users, and the importer are excluded. New theme files must be added to the script's explicit allowlist.

After upload, the script checks live stylesheet/script hashes. PHP execution and appearance still require a browser check. CSS and JavaScript URLs use content hashes so subsequent code updates no longer require manual version bumps or a forced refresh.

The Telegram retention change keeps six newest groups including hidden posts. On the next successfully stored Telegram update it deletes older local text and unused photos, preserving tiny message-ID tombstones. Gallery/shared photos are protected; Telegram is not modified. Restoring old code does not restore removed content or media.

For rollback, use the printed deployment folder's `previous-theme` and `previous-telegram` files over SFTP. Database and media recovery require a separate backup.

## Repairing a deployment made with private file permissions

The original uploader used private local copies, which caused SFTP to create unreadable web files. The corrected uploader explicitly sets each staged public code/asset file to 644 before renaming it. Backup directories remain private. To repair a previous upload without uploading files again:

```sh
bash /Users/kylakaplan/Desktop/rssa+/rssa/wordpress/deployment/publish.sh --repair-permissions
```

This changes only the 18 allowlisted deployed files, not directories, wp-config.php, credentials, or uploads.
