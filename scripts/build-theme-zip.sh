#!/usr/bin/env bash
# Builds dist/smile-community-child.zip: the single file you upload at
# Appearance > Themes > Add New > Upload Theme. The Smile Community Core plugin
# is inside it and is installed by the theme's setup wizard.
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p dist
rm -f dist/smile-community-child.zip
(cd wp-content/themes && zip -r -q ../../dist/smile-community-child.zip smile-community-child -x "*.DS_Store" "*/node_modules/*")
echo "Built dist/smile-community-child.zip"
