CREATE TABLE migration_story (
  id_migration INT NOT NULL AUTO_INCREMENT,
  file VARCHAR(256) NOT NULL,
  checksum VARCHAR(256) NOT NULL,
  content TEXT NOT NULL,
  passed TIMESTAMP NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (id_migration),
  UNIQUE INDEX idu_file (file ASC),
  UNIQUE INDEX idu_checksum (checksum ASC)
);