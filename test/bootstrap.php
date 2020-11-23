<?php

define("DBPROVIDER", 'sqlite');
define("DBFILE", __DIR__ . DIRECTORY_SEPARATOR . 'db.sqlite');

if (file_exists(DBFILE)) {
    unlink(DBFILE);
}
touch(DBFILE);
