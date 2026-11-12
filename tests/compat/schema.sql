-- Schema inferred from the queries (see AGENTS.md), with seed data relative to today.
DROP DATABASE IF EXISTS yttracker;
CREATE DATABASE yttracker;
USE yttracker;

CREATE TABLE YTT_Users (
	ID INT AUTO_INCREMENT PRIMARY KEY,
	UUID VARCHAR(36) NOT NULL UNIQUE,
	Username VARCHAR(255) NULL,
	LastActivity TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	KeyHash CHAR(64) NULL
);

CREATE TABLE YTT_Records (
	UserId INT NOT NULL,
	Type INT NOT NULL,
	Stat BIGINT NOT NULL,
	StatDay DATE NOT NULL,
	Amount INT NOT NULL DEFAULT 1,
	PRIMARY KEY (UserId, Type, StatDay)
);

INSERT INTO YTT_Users (ID, UUID, Username) VALUES
	(1, '11111111-1111-1111-1111-111111111111', 'Alice'),
	(2, '22222222-2222-2222-2222-222222222222', NULL),
	(3, '33333333-3333-3333-3333-333333333333', 'Zoé ✓'),
	(4, '44444444-4444-4444-4444-444444444444', ''),
	(5, '55555555-5555-5555-5555-555555555555', 'Inactive');

-- User n has a record every n days over the last 400 days, user 5 only long ago.
INSERT INTO YTT_Records (UserId, Type, Stat, StatDay, Amount)
SELECT u, t, (u * 1000003 + d * 7919 + t * 104729) % 5000000, CURDATE() - INTERVAL d DAY, 1 + (d + u) % 9
FROM (SELECT seq AS d FROM seq_0_to_400) days
JOIN (SELECT seq AS u FROM seq_1_to_4) users ON d % u = 0
JOIN (SELECT seq AS t FROM seq_1_to_2) types;
INSERT INTO YTT_Records (UserId, Type, Stat, StatDay, Amount) VALUES
	(5, 1, 123456, CURDATE() - INTERVAL 300 DAY, 3),
	(5, 2, 654321, CURDATE() - INTERVAL 300 DAY, 3),
	(1, 2, 0, CURDATE() + INTERVAL 1 DAY, 2);
