
UPDATE event_log
SET event_type=9
WHERE event LIKE "Entry added to % Form";

UPDATE event_log
SET event_type=8
WHERE event LIKE "New Form % created";

UPDATE event_log
SET event_type=1
WHERE event LIKE "New Company % created";

UPDATE event_log
SET event_type=3
WHERE event LIKE "New User % created";

UPDATE event_log
SET event_type=4
WHERE event LIKE "User %'s password changed by admin";

UPDATE event_log
SET event_type=11
WHERE event LIKE "% deactivated";

UPDATE event_log
SET event_type=10
WHERE event LIKE "Data imported into %";

UPDATE event_log
SET event_type=13
WHERE event LIKE "Alert added to % Form";

UPDATE event_log
SET event_type=14
WHERE event LIKE "Alert of % Form edited";

UPDATE event_log
SET event_type=2
WHERE event LIKE "Company % edited";

UPDATE event_log
SET event_type=5
WHERE event LIKE "New Department % created";

UPDATE event_log
SET event_type=6
WHERE event LIKE "Department % edited";

UPDATE event_log
SET event_type=7
WHERE event LIKE "Department % deleted";

UPDATE event_log
SET event_type=12
WHERE event LIKE "% reactivated";
