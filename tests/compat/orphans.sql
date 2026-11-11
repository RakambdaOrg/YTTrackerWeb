-- Test data for maintenance/orphan-records.sql: run in a scratch database.
DROP DATABASE IF EXISTS yttracker_orphans;
CREATE DATABASE yttracker_orphans;
USE yttracker_orphans;
CREATE TABLE YTT_Records (UserId INT NOT NULL, Type INT NOT NULL, Stat BIGINT NOT NULL, StatDay DATE NOT NULL, Amount INT NOT NULL DEFAULT 1, PRIMARY KEY (UserId, Type, StatDay));
INSERT INTO YTT_Records VALUES
	-- user 1, an old user
	(1, 1, 1000, '2026-01-01', 1), (1, 1, 1000, '2026-01-05', 1),
	-- day 01-05: user 6 created by stats/add (orphan, watched), then posted again that day: moved, merged into (6, 1, 01-05)
	(0, 1, 50, '2026-01-05', 1), (6, 1, 2000, '2026-01-05', 3), (6, 2, 9000, '2026-01-06', 1),
	-- day 01-07: user 7, orphan is opened and 7 has no opened row that day: moved as a new row
	(0, 2, 70, '2026-01-07', 1), (7, 1, 700, '2026-01-07', 2),
	-- day 01-09: users 8 and 9 both start: two candidates, kept
	(0, 1, 90, '2026-01-09', 2), (8, 1, 800, '2026-01-09', 1), (9, 1, 900, '2026-01-09', 1),
	-- day 01-11: no candidate (the new user never posted again): kept
	(0, 1, 110, '2026-01-11', 1);
