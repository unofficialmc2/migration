CREATE TABLE migration_story (
 id_migration bigserial PRIMARY KEY,
 file varchar(256) NOT NULL UNIQUE,
 checksum varchar(256) NOT NULL UNIQUE,
 content TEXT NOT NULL,
 passed varchar(32) DEFAULT current_timestamp
);