#!/bin/bash
set -euo pipefail

wp_dir="$(cd "$(dirname "$0")/.." && pwd)"
repo_dir="$(git -C "$wp_dir" rev-parse --show-toplevel)"
cd "$repo_dir"
message="${1:-chore(site): publish WordPress updates}"
if [[ "$message" == "--help" ]]; then
  echo 'Usage: bash release.sh ["fix(gallery): describe the changes"]'
  echo 'Checks source, commits tracked WordPress changes, pushes main to GitHub, then publishes that exact commit to USI.'
  exit 0
fi
if [[ ! "$message" =~ ^(feat|fix|docs|refactor|test|chore|perf|build|ci|style)(\([a-zA-Z0-9_-]+\))?:\ .+ ]]; then
  echo 'Use a conventional commit message, for example: fix(gallery): adjust spacing'
  exit 2
fi
[[ "$(git branch --show-current)" == 'main' ]] || { echo 'Run releases from the main branch.'; exit 1; }
[[ "$(git remote get-url --push origin)" == 'git@github.com:kybeka/rssa-lugano.git' ]] || { echo 'Unexpected GitHub destination; stopped.'; exit 1; }
git diff --cached --quiet || { echo 'There are already staged changes. Commit or unstage them before releasing.'; exit 1; }
new_files="$(git ls-files --others --exclude-standard -- wordpress)"
[[ -z "$new_files" ]] || { printf 'Review and commit these new source files first:\n%s\n' "$new_files"; exit 1; }

lock_dir="$(git rev-parse --git-path rssa-release.lock)"
mkdir "$lock_dir" 2>/dev/null || { echo 'Another release is running, or its lock remains. Stopped.'; exit 1; }
trap 'rmdir "$lock_dir"' EXIT

# Fail before committing if another machine has pushed changes.
git fetch origin main
git merge-base --is-ancestor origin/main HEAD || { echo 'GitHub has changes missing locally. Sync main before releasing.'; exit 1; }

echo 'Checking the local WordPress source...'
env -u RSSA_RELEASE_COMMIT bash "$wp_dir/deployment/publish.sh" --preview
git diff --check -- wordpress
# Only tracked WordPress source is included; unrelated repository work stays local.
git add -u -- wordpress
if ! git diff --cached --quiet; then
  git commit -m "$message"
fi
revision="$(git rev-parse HEAD)"
# Check the committed version too, before it is pushed or deployed.
RSSA_RELEASE_COMMIT="$revision" bash "$wp_dir/deployment/publish.sh" --preview
git push origin HEAD:refs/heads/main
remote_revision="$(git ls-remote origin refs/heads/main | cut -f1)"
[[ "$remote_revision" == "$revision" ]] || { echo 'GitHub does not match this release. No USI upload was started.'; exit 1; }
echo "GitHub backup verified: $revision"
if ! RSSA_RELEASE_COMMIT="$revision" bash "$wp_dir/deployment/publish.sh" --publish; then
  echo "GitHub is saved at $revision, but USI deployment or verification failed. Read the error above; do not assume the live site matches."
  exit 1
fi
echo "Release complete: GitHub and the USI upload use $revision."
