#!/usr/bin/env bash
# Check that every old smilecommunitycentre.org address still works on a site.
#
# Usage:
#   bash scripts/check-old-urls.sh                                          # staging
#   bash scripts/check-old-urls.sh https://smilecommunitycentre.org          # after go-live
#
# Reads docs/old-urls.txt. Each address must end on HTTP 200 with at most one
# redirect. Runs a few requests at a time (the host rate-limits bursts).
# Exit code 1 if anything fails.

set -u
BASE="${1:-https://topnotchmall.co.ke/smilescommunity}"
BASE="${BASE%/}"
LIST="$(dirname "$0")/../docs/old-urls.txt"
FAIL=0
TOTAL=0

while IFS= read -r path; do
	case "$path" in ''|'#'*) continue ;; esac
	TOTAL=$((TOTAL + 1))
	out=$(curl -s -o /dev/null -L --max-redirs 5 -m 20 -A "Mozilla/5.0 (old-url-check)" \
		-w "%{http_code} %{num_redirects} %{url_effective}" "${BASE}${path}")
	code=${out%% *}
	rest=${out#* }
	hops=${rest%% *}
	final=${rest#* }
	if [ "$code" != "200" ] || [ "$hops" -gt 1 ]; then
		FAIL=$((FAIL + 1))
		echo "FAIL  $path  ->  $code after $hops redirect(s)  ${final#"$BASE"}"
	else
		echo "ok    $path  ->  ${final#"$BASE"}"
	fi
	sleep 0.3
done < "$LIST"

echo
echo "$((TOTAL - FAIL)) of $TOTAL old addresses work on $BASE"
[ "$FAIL" -eq 0 ]
