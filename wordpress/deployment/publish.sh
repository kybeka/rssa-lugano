#!/bin/bash
set -euo pipefail

wp_dir="$(cd "$(dirname "$0")/.." && pwd)"
mode="${1:-}"
if [[ "$mode" != "--preview" && "$mode" != "--publish" && "$mode" != "--repair-permissions" ]]; then
  echo 'Usage: bash publish.sh --preview | --publish | --repair-permissions'
  exit 2
fi
for tool in node php sftp; do
  command -v "$tool" >/dev/null || { echo "Required tool is missing: $tool"; exit 1; }
done

# Keep recovery files outside the repository and public website.
backup_base="${RSSA_BACKUP_DIR:-$HOME/Library/Application Support/RSSA/deployments}"
umask 077
mkdir -p "$backup_base"
run_dir="$(mktemp -d "$backup_base/$(date +%Y%m%d-%H%M%S)-XXXXXX")"
mkdir "$run_dir/source"
cp -R "$wp_dir/theme" "$run_dir/source/theme"
cp "$wp_dir/plugins/rssa-telegram/rssa-telegram.php" "$run_dir/source/rssa-telegram.php"

node - "$run_dir" <<'NODE'
const fs = require('node:fs');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
const run = process.argv[2];
const source = path.join(run, 'source');
const themeRoot = '/usi_rssa/docs/wp-content/themes/rssa';
const pluginRoot = '/usi_rssa/docs/wp-content/plugins/rssa-telegram';
// Explicit allowlist: no runtime, accounts, database, uploads or importers.
const themeFiles = ['functions.php','gallery.php','index.php','contact.php','content.json','script.js','editor.js','style.css','icon.svg',
  'images/david.jpg','images/katherine.avif','images/david.avif','images/katherine.jpg','images/logo.png',
  'images/aleksandr.avif','images/group-photo.avif','images/aleksandr.jpg'];
const files = themeFiles.map(name => [path.join(source, 'theme', name), `${themeRoot}/${name}`]);
files.push([path.join(source,'rssa-telegram.php'), `${pluginRoot}/rssa-telegram.php`]);
for (const [local] of files) {
  if (!fs.statSync(local).isFile()) throw new Error(`Missing deployment file: ${local}`);
  if (local.endsWith('.php')) execFileSync('php', ['-l', local]);
  if (local.endsWith('.js')) execFileSync('node', ['--check', local]);
}
const quote = s => {
  if (/[\r\n"\\]/.test(s)) throw new Error('Unsupported character in deployment path');
  return `"${s}"`;
};
const batch = [
  `get -R ${quote(themeRoot)} ${quote(path.join(run,'previous-theme'))}`,
  `get -R ${quote(pluginRoot)} ${quote(path.join(run,'previous-telegram'))}`
];
// Upload every file before replacing any live file. Renames are atomic per file,
// but the complete release is not a single atomic operation.
for (const [local, remote] of files) {
  batch.push(`put ${quote(local)} ${quote(remote + '.rssa-deploy')}`);
  batch.push(`chmod 644 ${quote(remote + '.rssa-deploy')}`);
}
for (const [, remote] of files) batch.push(`rename ${quote(remote + '.rssa-deploy')} ${quote(remote)}`);
batch.push('bye');
fs.writeFileSync(path.join(run,'repair.sftp'), files.map(([,remote]) => `chmod 644 ${quote(remote)}`).concat('bye').join('\n')+'\n');
fs.writeFileSync(path.join(run,'upload.sftp'),batch.join('\n')+'\n');
fs.writeFileSync(path.join(run,'manifest.json'),JSON.stringify(files.map(([local,remote])=>({remote,sha256:require('node:crypto').createHash('sha256').update(fs.readFileSync(local)).digest('hex')})),null,2));
console.log(`Validated ${files.length} code and bundled image files.`);
NODE

echo "Deployment snapshot and backup location: $run_dir"
if [[ "$mode" == "--preview" ]]; then
  echo 'Preview complete. No connection or upload was made.'
  exit 0
fi
if [[ "$mode" == "--repair-permissions" ]]; then
  echo 'Repairing read permissions on the 18 deployed code and image files only.'
  sftp -oBatchMode=no -b "$run_dir/repair.sftp" 'kaplak@usi.ch@rssa.usi.ch'
  echo 'Permissions repaired. Reload https://rssa.usi.ch/'
  exit 0
fi

printf '\nPublishing to rssa.usi.ch. Enter your USI password when prompted.\n'
echo 'The Telegram plugin will prune posts beyond the newest six on its next received update.'
echo 'These are code backups; they do not back up the database or uploaded media.'
sftp -oBatchMode=no -b "$run_dir/upload.sftp" 'kaplak@usi.ch@rssa.usi.ch'
echo 'Upload completed. Checking public stylesheet and script...'
node - "$run_dir" <<'NODE'
const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
(async () => {
  const run = process.argv[2];
  for (const file of ['style.css','script.js']) {
    const response = await fetch(`https://rssa.usi.ch/wp-content/themes/rssa/${file}?deploy=${Date.now()}`, {signal:AbortSignal.timeout(30000)});
    if (!response.ok) throw new Error(`${file}: HTTP ${response.status}`);
    const digest = b => crypto.createHash('sha256').update(b).digest('hex');
    if (digest(Buffer.from(await response.arrayBuffer())) !== digest(fs.readFileSync(path.join(run,'source/theme',file)))) throw new Error(`${file}: live content does not match the upload`);
    console.log(`Verified live: ${file}`);
  }
})().catch(error => {console.error(`Upload finished, but verification failed: ${error.message}`); process.exitCode=1;});
NODE
echo 'Open https://rssa.usi.ch/ and check Latest updates and the gallery.'
