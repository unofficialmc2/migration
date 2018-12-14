CREATE TABLE migration_story (
 id_migration INTEGER PRIMARY KEY AUTOINCREMENT,
 file TEXT NOT NULL UNIQUE,
 content TEXT NOT NULL UNIQUE,
 checksum TEXT NOT NULL UNIQUE,
 passed TEXT DEFAULT (datetime('now'))
);