#!/bin/bash
# Update Homebrew formula with correct SHA256 hashes
# Usage: ./scripts/update-homebrew-sha.sh <path-to-walnut.phar>

set -e

PHAR_PATH="${1:-.build/walnut.phar}"
FORMULA_FILE="Formula/walnut.rb"
VERSION="${GITHUB_REF#refs/tags/v}"  # Extract version from git tag

if [ ! -f "$PHAR_PATH" ]; then
    echo "Error: PHAR file not found at $PHAR_PATH"
    exit 1
fi

if [ ! -f "$FORMULA_FILE" ]; then
    echo "Error: Formula file not found at $FORMULA_FILE"
    exit 1
fi

# Calculate SHA256
SHA256=$(shasum -a 256 "$PHAR_PATH" | awk '{print $1}')

echo "Updating $FORMULA_FILE with:"
echo "  Version: $VERSION"
echo "  SHA256: $SHA256"

# Update formula (same hash for all platforms, can be made per-platform later)
sed -i.bak "s/sha256 \".*\"/sha256 \"$SHA256\"/g" "$FORMULA_FILE"
rm -f "$FORMULA_FILE.bak"

# Also update version if available
if [ -n "$VERSION" ]; then
    sed -i.bak "s/version \".*\"/version \"$VERSION\"/g" "$FORMULA_FILE"
    rm -f "$FORMULA_FILE.bak"
fi

echo "✅ Formula updated successfully"