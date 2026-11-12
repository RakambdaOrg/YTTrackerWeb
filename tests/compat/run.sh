#!/bin/sh
# Runs the old API (master) and the new one (this tree) side by side on the same seeded database,
# and diffs every response and the tables after each POST. Needs tests/db.sh; starts and stops it.
set -eu
ROOT=$(cd "$(dirname "$0")/../.." && pwd)
WORK=$(mktemp -d)
OLD=8701
NEW=8702
DB="$ROOT/tests/db.sh"

PIDS=''
cleanup() {
	[ -z "$PIDS" ] || kill $PIDS 2>/dev/null || true
	"$DB" stop || true
	rm -rf "$WORK"
}
trap cleanup EXIT

"$DB" start
mkdir -p "$WORK/old" "$WORK/configs"
git -C "$ROOT" archive "${OLD_REF:-master}" app | tar -x -C "$WORK/old"
cat > "$WORK/configs/database.config.php" <<CONF
<?php return ['host' => '127.0.0.1;port=3399', 'database' => 'yttracker', 'username' => 'root', 'password' => ''];
CONF
# The old code reads the config 5 levels above app/api/v2/model/, which is $WORK/configs.
php -d display_errors=stderr -S 127.0.0.1:$OLD -t "$WORK/old/app" "$ROOT/tests/compat/router.php" >"$WORK/old.log" 2>&1 &
PIDS="$PIDS $!"
YTT_DB_CONFIG="$WORK/configs/database.config.php" php -d display_errors=stderr -S 127.0.0.1:$NEW -t "$ROOT/app" "$ROOT/tests/compat/router.php" >"$WORK/new.log" 2>&1 &
PIDS="$PIDS $!"
sleep 1
for pid in $PIDS; do
	kill -0 "$pid" 2>/dev/null || { echo 'php -S did not start (port in use?)' >&2; cat "$WORK"/*.log >&2; exit 1; }
done

U=11111111-1111-1111-1111-111111111111
A=22222222-2222-2222-2222-222222222222
X=99999999-9999-9999-9999-999999999999
failures=0

seed() { "$DB" sql < "$ROOT/tests/compat/schema.sql"; }
dump() { "$DB" sql -N yttracker -e 'SELECT ID, UUID, Username FROM YTT_Users ORDER BY ID; SELECT * FROM YTT_Records ORDER BY UserId, Type, StatDay;'; }
call() { curl -s -o - -w '\nHTTP %{http_code}\n' "$@"; }

# Intended differences, see AGENTS.md "Changes from the old API".
known() {
	case "$1" in
	'watched range' | 'watched range header' | 'watched range huge') return 0 ;; # range now read from the query/header
	'opened range' | 'opened range header' | 'opened range huge') return 0 ;;
	'add JSON new user') return 0 ;; # the old code stored a new user's first stat under UserId 0
	*) return 1 ;;
	esac
}

# compare <label> <path> [curl args]: same request on both servers, then the tables
compare() {
	label=$1 path=$2
	shift 2
	seed
	call "$@" "http://127.0.0.1:$OLD$path" > "$WORK/a"
	dump >> "$WORK/a"
	seed
	call "$@" "http://127.0.0.1:$NEW$path" > "$WORK/b"
	dump >> "$WORK/b"
	if cmp -s "$WORK/a" "$WORK/b"; then
		echo "same  $label"
	elif known "$label"; then
		echo "known $label"
	else
		echo "DIFF  $label"
		diff "$WORK/a" "$WORK/b" | cut -c1-160 | head -8 || true
		failures=$((failures + 1))
	fi
}

compare 'no request' /api/v2/index.php
compare 'unknown route' /api/v2/nothing
compare 'wrong method' /api/v2/users -X POST
compare 'users' /api/v2/users
compare 'users range' '/api/v2/users?range=14'
compare 'users range text' '/api/v2/users?range=abc'
compare 'users range huge' '/api/v2/users?range=99999'
compare 'stats' /api/v2/$U/stats
compare 'stats anonymous' /api/v2/$A/stats
compare 'stats unknown' /api/v2/$X/stats
compare 'stats uppercase uuid' /api/v2/AAAAAAAA-1111-1111-1111-111111111111/stats
compare 'stats prefix' /api/v2/x/$U/stats
for series in watched opened opened-count; do
	compare "$series" /api/v2/$U/stats/$series
	compare "$series range" "/api/v2/$U/stats/$series?range=7"
	compare "$series range header" /api/v2/$U/stats/$series -H 'range: 7'
	compare "$series range JSON" /api/v2/$U/stats/$series -H 'Content-Type: application/json' --data-binary '{"range": 7}' -X GET
	compare "$series range huge" "/api/v2/$U/stats/$series?range=99999"
	compare "$series unknown" /api/v2/$X/stats/$series
done
compare 'username' /api/v2/$U/username
compare 'username unicode' /api/v2/33333333-3333-3333-3333-333333333333/username
compare 'username null' /api/v2/$A/username
compare 'username unknown' /api/v2/$X/username
compare 'add JSON (extension)' /api/v2/$U/stats/add -H 'Content-Type: application/json' --data-binary '{"type": "watched", "stat": 1234, "date": "2026-1-5 9:3:7"}'
compare 'add JSON new user' /api/v2/$X/stats/add -H 'Content-Type: application/json' --data-binary '{"type": "opened", "stat": 99, "date": "2026-11-11 23:59:59"}'
compare 'add form' /api/v2/$U/stats/add -d type=2 -d stat=500 -d date=2026-11-11
compare 'add type 3' /api/v2/$U/stats/add -d type=3 -d stat=500 -d date=2026-11-11
compare 'add missing date' /api/v2/$U/stats/add -d type=1 -d stat=500
compare 'add bad stat' /api/v2/$U/stats/add -d type=1 -d stat=abc -d date=2026-11-11
compare 'set username (extension)' /api/v2/$U/username -d username=Bob
compare 'set username new user' /api/v2/$X/username -d 'username=Zoé ✓'
compare 'set username Anonymous' /api/v2/$U/username -d username=Anonymous
compare 'set username empty' /api/v2/$U/username -d username=
compare 'set username missing' /api/v2/$U/username -X POST
compare 'set username JSON' /api/v2/$U/username -H 'Content-Type: application/json' --data-binary '{"username": "Json"}'

# Write key (new API only): no key passes until a key is sent, then only that key passes.
expect() {
	label=$1 want=$2
	shift 2
	got=$(curl -s -o /dev/null -w '%{http_code}' "$@")
	if [ "$got" = "$want" ]; then echo "ok    $label"; else echo "FAIL  $label: HTTP $got, expected $want"; failures=$((failures + 1)); fi
}
seed
ADD="http://127.0.0.1:$NEW/api/v2/$U/stats/add"
expect 'key: none, user unlocked' 200 "$ADD" -d type=1 -d stat=1 -d date=2026-11-11
expect 'key: first key locks' 200 "$ADD" -H 'X-YTT-Key: secret' -d type=1 -d stat=1 -d date=2026-11-11
expect 'key: same key, lowercase header' 200 "$ADD" -H 'x-ytt-key: secret' -d type=1 -d stat=1 -d date=2026-11-11
expect 'key: none, user locked' 403 "$ADD" -d type=1 -d stat=1 -d date=2026-11-11
expect 'key: wrong key' 403 "$ADD" -H 'X-YTT-Key: other' -d type=1 -d stat=1 -d date=2026-11-11
expect 'key: username wrong key' 403 "http://127.0.0.1:$NEW/api/v2/$U/username" -H 'X-YTT-Key: other' -d username=Eve
expect 'key: username right key' 200 "http://127.0.0.1:$NEW/api/v2/$U/username" -H 'X-YTT-Key: secret' -d username=Bob
expect 'key: new user with key' 200 "http://127.0.0.1:$NEW/api/v2/$X/stats/add" -H 'X-YTT-Key: k2' -d type=1 -d stat=1 -d date=2026-11-11
expect 'key: new user locked' 403 "http://127.0.0.1:$NEW/api/v2/$X/stats/add" -d type=1 -d stat=1 -d date=2026-11-11
stored=$("$DB" sql -N yttracker -e "SELECT COUNT(*) FROM YTT_Users WHERE UUID='$U' AND KeyHash=SHA2('secret', 256)")
[ "$stored" = 1 ] && echo 'ok    key: stored as sha256' || { echo 'FAIL  key: stored as sha256'; failures=$((failures + 1)); }

# maintenance/orphan-records.sql on tests/compat/orphans.sql: only the unambiguous orphans move.
"$DB" sql < "$ROOT/tests/compat/orphans.sql"
"$DB" sql yttracker_orphans < "$ROOT/maintenance/orphan-records.sql" > /dev/null
got=$("$DB" sql -N yttracker_orphans -e 'SELECT UserId, Type, Stat, StatDay, Amount FROM YTT_Records WHERE UserId IN (0, 6, 7) ORDER BY UserId, StatDay, Type' | tr '\t' ' ')
expected='0 1 90 2026-01-09 2
0 1 110 2026-01-11 1
6 1 2050 2026-01-05 4
6 2 9000 2026-01-06 1
7 1 700 2026-01-07 2
7 2 70 2026-01-07 1'
if [ "$got" = "$expected" ]; then
	echo 'same  orphan records repair'
else
	printf 'DIFF  orphan records repair\n%s\n' "$got"
	failures=$((failures + 1))
fi
"$DB" sql -e 'DROP DATABASE yttracker_orphans'

if grep -qiE 'fatal|warning|deprecated' "$WORK/new.log"; then
	echo 'PHP errors in the new code:'
	grep -iE 'fatal|warning|deprecated' "$WORK/new.log" | head -10
	failures=$((failures + 1))
fi
echo "$failures difference(s)"
[ "$failures" -eq 0 ]
