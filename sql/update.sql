ALTER TABLE llx_history MODIFY COLUMN what_changed TEXT;
ALTER TABLE llx_history CHANGE date_cre date_creation datetime NULL;
ALTER TABLE llx_history CHANGE datec date_creation datetime NULL;
ALTER TABLE llx_history CHANGE date_maj tms datetime NULL;
ALTER TABLE llx_history CHANGE rowid rowid int(11) NOT NULL AUTO_INCREMENT FIRST;