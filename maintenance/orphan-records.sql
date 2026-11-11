-- Orphan stats: YTT_Records rows with UserId = 0, written by the old API (before the PHP 8.5 rewrite).
--
-- The old DBConnection opened a new connection for every query, so lastInsertId() returned 0: when
-- stats/add created a user (one that never set a username), that first stat was stored under UserId 0.
-- These rows are invisible (no user has ID 0) and can't be split between users when several new users
-- posted on the same day, so this script only moves the unambiguous ones.
--
-- Run it AFTER the rewrite is deployed (the old code keeps creating orphans), in ONE session
-- (OrphanMatch is a temporary table), part by part, reading each result before going on.

-- 1. Inspect (read-only).
SELECT Type, COUNT(*) AS Days, SUM(Amount) AS Posts, SUM(Stat) AS TotalMs, MIN(StatDay) AS First, MAX(StatDay) AS Last
FROM YTT_Records WHERE UserId = 0 GROUP BY Type;

-- 2. Match (read-only): for each orphan row, the users whose first own record is on that day.
--    Safe to move: one POST (Amount = 1) and exactly one candidate.
--    Known limit: if the real owner never posted again, a user who set a username first and started
--    that same day could be the only candidate and get the stat by mistake.
CREATE TEMPORARY TABLE OrphanMatch AS
SELECT o.Type, o.StatDay, o.Amount, COUNT(f.UserId) AS Candidates, MIN(f.UserId) AS UserId
FROM YTT_Records o
LEFT JOIN (SELECT UserId, MIN(StatDay) AS FirstDay FROM YTT_Records WHERE UserId <> 0 GROUP BY UserId) f
	ON f.FirstDay = o.StatDay
WHERE o.UserId = 0
GROUP BY o.Type, o.StatDay, o.Amount;

SELECT Amount = 1 AND Candidates = 1 AS Safe, COUNT(*) AS RowCount FROM OrphanMatch GROUP BY Safe;
SELECT * FROM OrphanMatch ORDER BY StatDay, Type;

-- 3. Move the safe rows to their user (writes).
START TRANSACTION;
INSERT INTO YTT_Records (UserId, Type, Stat, StatDay, Amount)
SELECT m.UserId, o.Type, o.Stat, o.StatDay, o.Amount
FROM OrphanMatch m
JOIN YTT_Records o ON o.UserId = 0 AND o.Type = m.Type AND o.StatDay = m.StatDay
WHERE m.Amount = 1 AND m.Candidates = 1
ON DUPLICATE KEY UPDATE YTT_Records.Stat = YTT_Records.Stat + VALUES(Stat), YTT_Records.Amount = YTT_Records.Amount + VALUES(Amount);
DELETE o FROM YTT_Records o
JOIN OrphanMatch m ON o.UserId = 0 AND o.Type = m.Type AND o.StatDay = m.StatDay
WHERE m.Amount = 1 AND m.Candidates = 1;
COMMIT;

-- 4. The rest can't be attributed. Leave them (they are invisible), or delete them:
-- DELETE FROM YTT_Records WHERE UserId = 0;
