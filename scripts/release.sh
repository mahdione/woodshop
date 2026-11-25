#!/usr/bin/env bash
set -euo pipefail

# Build the installable theme zip, tag the release and publish it on GitHub.
# Usage: GITHUB_TOKEN=ghp_xxx ./scripts/release.sh
# If GITHUB_TOKEN is not set, the git credential helper is used when available.

cd "$(dirname "$0")/.."

REPO="${GITHUB_REPO:-mahdione/woodshop}"
VERSION="$(grep -m1 '^Version:' style.css | awk '{print $2}')"
TAG="v${VERSION}"

if [ -z "$VERSION" ]; then
  echo "Could not read the theme version from style.css."
  exit 1
fi

if [ -n "$(git status --porcelain)" ]; then
  echo "Working tree is not clean. Commit the version bump first."
  exit 1
fi

if [ -z "${GITHUB_TOKEN:-}" ]; then
  cred="$(printf 'protocol=https\nhost=github.com\n\n' | git credential fill 2>/dev/null || true)"
  GITHUB_TOKEN="$(printf '%s\n' "$cred" | sed -n 's/^password=//p')"
fi

if [ -z "${GITHUB_TOKEN:-}" ]; then
  echo "Set GITHUB_TOKEN or configure the git credential helper for github.com."
  exit 1
fi

echo "Building Wood Shop $VERSION..."
rm -rf build woodshop.zip
mkdir -p build/woodshop
rsync -a ./ build/woodshop/ \
  --exclude '.git' \
  --exclude '.github' \
  --exclude '.gitignore' \
  --exclude 'build' \
  --exclude 'scripts' \
  --exclude '*.zip' \
  --exclude '.DS_Store'
(cd build && zip -qr ../woodshop.zip woodshop)

echo "Pushing main and creating $TAG..."
git push origin main

if git rev-parse "$TAG" >/dev/null 2>&1; then
  git push origin "$TAG"
else
  git tag -a "$TAG" -m "Wood Shop $TAG"
  git push origin "$TAG"
fi

release_json="$(python3 - "$TAG" <<'PY'
import json, sys
print(json.dumps({
    "tag_name": sys.argv[1],
    "name": "Wood Shop " + sys.argv[1],
    "body": "Release " + sys.argv[1] + " of the Wood Shop theme.",
    "draft": False,
    "prerelease": False,
}))
PY
)"

response="$(curl -sS -X POST \
  -H "Authorization: Bearer $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github+json" \
  "https://api.github.com/repos/$REPO/releases" \
  -d "$release_json")"

upload_url="$(printf '%s' "$response" | python3 -c 'import json,sys; print(json.load(sys.stdin).get("upload_url", ""))')"
html_url="$(printf '%s' "$response" | python3 -c 'import json,sys; print(json.load(sys.stdin).get("html_url", ""))')"

if [ -z "$upload_url" ]; then
  echo "$response"
  echo "Could not create the GitHub release."
  exit 1
fi

upload_url="${upload_url%%\{*}"

echo "Uploading woodshop.zip..."
curl -sS -X POST \
  -H "Authorization: Bearer $GITHUB_TOKEN" \
  -H "Content-Type: application/zip" \
  --data-binary @woodshop.zip \
  "${upload_url}?name=woodshop.zip" >/tmp/woodshop-release-asset.json

echo "Release ready: $html_url"
echo "Asset: ${html_url}/download/${TAG}/woodshop.zip"
