#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
staging=$(mktemp -d "${TMPDIR:-/tmp}/rssa-package.XXXXXX")
trap 'rm -rf "$staging"' EXIT
mkdir -p "$staging/rssa" "$staging/rssa-content-transfer" release
cp -R plugins/rssa-telegram "$staging/rssa-telegram"
cp -R theme/. "$staging/rssa/"
cp deployment/importer.php "$staging/rssa-content-transfer/rssa-content-transfer.php"
php deployment/export.php "$staging/rssa-content-transfer"
(cd "$staging" && zip -qr rssa-theme.zip rssa -x '*.DS_Store' && zip -qr rssa-content-transfer.zip rssa-content-transfer -x '*.DS_Store' && zip -qr rssa-telegram.zip rssa-telegram -x 'rssa-telegram/tests/*')
cp "$staging/rssa-theme.zip" "$staging/rssa-content-transfer.zip" "$staging/rssa-telegram.zip" release/
cp deployment/DEPLOYMENT.md release/
(cd release && shasum -a 256 rssa-theme.zip rssa-content-transfer.zip rssa-telegram.zip > SHA256SUMS)
printf 'Packages are ready in wordpress/release. Nothing was uploaded.\n'
