# Routine live updates

Run in a normal Terminal shell, not at the `sftp>` prompt:

```sh
bash /Users/kylakaplan/Desktop/rssa+/rssa/wordpress/deployment/publish.sh --publish
```

Enter your USI password at the SFTP prompt. It is not saved. VPN may be required by USI. To validate without connecting, replace `--publish` with `--preview`.

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
