ALTER TABLE messut ADD COLUMN kolehtikohde varchar(99) DEFAULT NULL;
ALTER TABLE messut ADD COLUMN kolehtitavoite varchar(99) DEFAULT NULL;
ALTER TABLE messut ADD COLUMN kolehtia_keratty decimal(60,2) DEFAULT 0;
CREATE TABLE kolehtitavoitteet (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  kohde varchar(100) DEFAULT NULL,
  tavoite varchar(100) DEFAULT NULL,
  tavoitemaara decimal(60,2) DEFAULT 0,
  PRIMARY KEY (id)
);
