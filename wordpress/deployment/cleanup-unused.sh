#!/bin/bash
set -euo pipefail

mode="${1:---preview}"
if [[ "$mode" != '--preview' && "$mode" != '--archive' ]]; then
  echo 'Usage: bash cleanup-unused.sh --preview | --archive'
  exit 2
fi
umask 077
run_dir="$(mktemp -d "${TMPDIR:-/tmp}/rssa-cleanup-XXXXXX")"
archive="/usi_rssa/docs-archive/unused-wordpress-$(date +%Y%m%d-%H%M%S)-$(basename "$run_dir")"
batch="$run_dir/archive.sftp"
restore="$run_dir/restore.sftp"
# Confirmed inactive in the live dashboard. Keep RSSA and Twenty Twenty-Five.
paths=(
  'plugins/akismet'
  'plugins/hello.php'
  'themes/twentytwentyfour'
  'themes/twentytwentythree'
)
{
  for item in "${paths[@]}"; do
    printf 'ls -l "/usi_rssa/docs/wp-content/%s"\n' "$item"
  done
  printf 'mkdir "%s"\n' "$archive"
  for item in "${paths[@]}"; do
    printf 'rename "/usi_rssa/docs/wp-content/%s" "%s/%s"\n' "$item" "$archive" "${item##*/}"
  done
  printf 'bye\n'
} > "$batch"
{
  for item in "${paths[@]}"; do
    printf 'rename "%s/%s" "/usi_rssa/docs/wp-content/%s"\n' "$archive" "${item##*/}" "$item"
  done
  printf 'bye\n'
} > "$restore"
echo 'Moves these unused items out of WordPress, without deleting them:'
printf '  %s\n' "${paths[@]}"
echo "Server archive: $archive"
echo 'Before running, confirm these four items are still inactive.'
if [[ "$mode" == '--preview' ]]; then
  echo 'Preview only; no connection or change was made.'
  exit 0
fi
# Keep recovery instructions outside the repository and the public website.
backup_base="${RSSA_BACKUP_DIR:-$HOME/Library/Application Support/RSSA/deployments}"
mkdir -p "$backup_base"
recovery_dir="$(mktemp -d "$backup_base/cleanup-XXXXXX")"
cp "$batch" "$restore" "$recovery_dir/"
printf '%s\n' "$archive" > "$recovery_dir/server-archive.txt"
echo "Recovery instructions: $recovery_dir/restore.sftp"
echo 'Enter your USI password at the SFTP prompt.'
if ! sftp -oBatchMode=no -b "$batch" 'kaplak@usi.ch@rssa.usi.ch'; then
  echo 'Cleanup stopped. Some moves may have completed; inspect the output before retrying.'
  echo "Keep $recovery_dir for recovery. No files were deleted."
  exit 1
fi
echo 'Archived the four unused items. Refresh WordPress Site Health to rerun its checks.'
echo 'This does not change update permissions or install caching software.'
