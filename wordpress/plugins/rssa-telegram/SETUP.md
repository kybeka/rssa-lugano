# RSSA Telegram setup

## Create the dedicated bot

1. In Telegram open the verified @BotFather account (https://t.me/BotFather), send `/newbot`, choose a display name and an available username ending in `bot`.
2. Keep the issued token private. Do not paste it in chat, commit it, or include it in screenshots.
3. Ask a channel administrator to add the bot to @RSSA_Lugano using the channel's administrator settings. Disable posting/editing/deleting/inviting rights wherever Telegram permits. This integration only reads updates; it never posts to Telegram. It does not need group privacy mode disabled.

Official references: https://core.telegram.org/bots/features#botfather and https://core.telegram.org/bots/api#setwebhook

## Install and connect

1. Install the prepared rssa-telegram.zip plugin alongside the updated RSSA theme on USI. Activate it. WordPress must have outbound HTTPS, database and upload permissions. Use a dedicated bot; the plugin refuses to replace a different existing webhook.
2. As WordPress administrator open Telegram announcements. Enter the token and Save settings; leave the switch away from Juicer unchecked initially. The token is stored as a non-autoloaded WordPress option, never displayed back, and excluded from our theme/content packages. Database backups contain it and must be protected.
3. On https://rssa.usi.ch select Connect webhook. The plugin resolves @RSSA_Lugano to its numeric channel ID, verifies bot membership, and registers a secret-authenticated HTTPS endpoint for channel posts and edits. Localhost is deliberately blocked from registration. No public tunnel is required.
4. Publish the next real announcement, or a clearly identified test announcement if the channel owner agrees. Confirm Last received updates, the text is correct and photos load. Edit it in Telegram and verify the same website entry changes. Try an album. Use Hide from website to hide the test; deleting it in Telegram is not enough.
5. Check Use the Telegram integration on the website instead of Juicer and Save settings. The theme then stops loading Juicer assets and shows up to six recent announcement groups. Until enabled, Juicer remains active. Turning the switch off restores Juicer without deleting imported announcements.
6. Katya uses Telegram announcements to show/hide received posts. Only an administrator sees the token/connection controls.

## Limits and operations

- New channel posts and subsequent edits only; old history is not automatically fetched. No real Telegram delivery has been tested until step 4 is complete.
- One configured channel only: @RSSA_Lugano. Source links are bound to it.
- Text is rendered safely as plain text with line breaks. Telegram rich formatting and inline clickable text links are not preserved; View on Telegram opens the full post.
- Photos are downloaded to WordPress. Photo albums share a display group; messages can arrive individually and briefly appear incomplete. Videos, documents and polls link back to Telegram rather than being downloaded/rendered interactively.
- Image downloads are capped at 8 MB and supported raster types. Upload/network failures return a retryable response and show an error in the dashboard. Investigate promptly; oversized/unsupported photos need an adjusted source post or an implementation change.
- Updates are serialized with a short database lock; duplicates and older updates for a message are ignored. Hidden groups remain hidden after edits/new album members. Replaced images are retained in Media Library rather than automatically deleted.
- Telegram does not supply general channel deletion events. Hide deleted/cancelled announcements manually in WordPress. Update dates/details in Telegram to reflect corrections.
- Telegram retains pending updates for at most 24 hours. Check Last received and Check Telegram status after outages. There is no historical replay or unattended monitoring service in this first version.
- Back up WordPress database and uploads together. Never put the bot token into frontend JavaScript or public URLs. To retire the integration, remove its webhook through Telegram's API or revoke the token via BotFather, then deactivate the plugin. Deactivation alone retains data and does not unregister the webhook.

## Local checks

The integration test uses the disposable `/tmp/rssa-release-rehearsal/wordpress` installation and mocked Telegram HTTP/media, never real tokens or messages:

`php tests/integration.php /tmp/rssa-release-rehearsal/wordpress`

Run from this plugin directory. It checks authentication, allowlisting, edits, duplicate/out-of-order delivery, grouping, hidden-state persistence, concurrent retry, local media storage and output escaping. A direct anonymous HTTP request to the local REST endpoint was also rejected with 403.

## Deleting stored announcements

Version 0.2.0 adds Delete from WordPress next to Hide/Show. Confirming permanently removes the local text and attached photos (including generated sizes); Telegram is never modified. Albums are deleted together. Photos referenced by other announcements, RSSA content/gallery, post content, or featured images are retained. Custom plugin references may not be detected, so avoid reusing Telegram media outside these areas. Minimal message/group IDs remain to prevent Telegram retries or edits from restoring deleted entries. Six visible announcements are still shown; deleting a visible entry allows the next older visible entry to appear. There is no automatic age-based cleanup.

Update the existing plugin PHP file via SFTP; no database migration, reactivation, or webhook reconnection is required.
