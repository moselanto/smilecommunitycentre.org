#!/usr/bin/env bash
# Builds minified assets (if rcssmin/rjsmin are installed) and dist/smile-community-child.zip.
set -euo pipefail
cd "$(dirname "$0")/.."
T=wp-content/themes/smile-community-child
if python3 -c "import rcssmin, rjsmin" 2>/dev/null; then
  python3 - <<PY
import rcssmin, rjsmin
T="$T"
open(T+"/assets/build/main.min.css","w").write(rcssmin.cssmin(open(T+"/assets/css/main.css").read()))
open(T+"/assets/build/home.min.css","w").write(rcssmin.cssmin(open(T+"/assets/css/slider.css").read()+"\n"+open(T+"/assets/css/home.css").read()))
open(T+"/assets/build/main.min.js","w").write(rjsmin.jsmin(open(T+"/assets/js/main.js").read()))
open(T+"/assets/build/slider.min.js","w").write(rjsmin.jsmin(open(T+"/assets/js/slider.js").read()))
print("Minified assets rebuilt")
PY
else
  echo "rcssmin/rjsmin not installed: keeping existing assets/build files (pip install rcssmin rjsmin)"
fi
python3 "$(dirname "$0")/check-theme.py" || { echo "Build stopped: fix the problems above first."; exit 1; }
mkdir -p dist
rm -f dist/smile-community-child.zip
(cd wp-content/themes && zip -r -q ../../dist/smile-community-child.zip smile-community-child -x "*.DS_Store")
echo "Built dist/smile-community-child.zip"
