#!/bin/sh
# Local MariaDB for tests/compat, run as the current user without any service.
# Start it only for a check and stop it right after: tests/db.sh start|stop
# sql_mode is not strict: the extension sends "Y-M-D H:m:s" dates, which strict mode rejects in
# STR_TO_DATE(..., '%Y-%m-%d'), so production must be non-strict (assumed, not checked).
set -eu
DIR=${XDG_DATA_HOME:-$HOME/.local/share}/yttrackerweb-mariadb
PORT=3399
client() { mariadb --no-defaults --socket="$DIR/mysqld.sock" -uroot "$@"; }

case "${1:-}" in
start)
	if [ ! -d "$DIR/data" ]; then
		mkdir -p "$DIR"
		mariadb-install-db --no-defaults --datadir="$DIR/data" --auth-root-authentication-method=normal >/dev/null
	fi
	"$(command -v mariadbd || echo /usr/sbin/mariadbd)" --no-defaults --datadir="$DIR/data" --socket="$DIR/mysqld.sock" --pid-file="$DIR/mysqld.pid" \
		--port=$PORT --bind-address=127.0.0.1 --sql-mode=NO_ENGINE_SUBSTITUTION --log-error="$DIR/error.log" &
	for _ in $(seq 50); do
		client -e 'SELECT 1' >/dev/null 2>&1 && exit 0
		sleep 0.2
	done
	echo "MariaDB did not start, see $DIR/error.log" >&2
	exit 1
	;;
stop)
	if [ -f "$DIR/mysqld.pid" ]; then
		pid=$(cat "$DIR/mysqld.pid")
		client -e SHUTDOWN
		# The socket goes away before the process exits: wait for the process.
		while kill -0 "$pid" 2>/dev/null; do sleep 0.2; done
	fi
	;;
sql)
	shift
	client "$@"
	;;
*)
	echo "usage: $0 start|stop|sql [mariadb args]" >&2
	exit 2
	;;
esac
