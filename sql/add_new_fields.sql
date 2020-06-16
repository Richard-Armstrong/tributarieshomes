
ALTER TABLE event_log ADD COLUMN event_type int(11) unsigned AFTER event;
ALTER TABLE event_log ADD COLUMN signature BLOB;
