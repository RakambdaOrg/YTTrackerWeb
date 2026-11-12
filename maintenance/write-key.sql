-- Run once on production BEFORE deploying the write-key change (the code reads this column).
ALTER TABLE YTT_Users ADD COLUMN KeyHash CHAR(64) NULL;
