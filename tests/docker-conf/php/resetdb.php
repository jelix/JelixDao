<?php

echo "Delete and restore all tables from the postgresql database\n";
$tryAgain = true;

while($tryAgain) {
    $cnx = @pg_connect("host='pgsql' port='5432' dbname='jelixtests' user='jelix' password='jelixpass' ");
    if (!$cnx) {
        echo "  postgresql is not ready yet\n";
        sleep(1);
        continue;
    }
    $tryAgain = false;
    pg_query($cnx, "drop table if exists products");
    pg_query($cnx, "drop table if exists labels_test");
    pg_query($cnx, "drop table if exists jsessions");

    pg_query($cnx, "CREATE TABLE products (
        id serial NOT NULL,
        name character varying(150) NOT NULL,
        price real NOT NULL,
        create_date time with time zone,
        promo boolean NOT NULL  default 'f',
        dummy character varying (10) NULL CONSTRAINT dummy_check CHECK (dummy IN ('created','started','stopped'))
    )");

    pg_query($cnx, "SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('products', 'id'), 1, false)");

    pg_query($cnx, "CREATE TABLE labels_test (
    \"key\" integer NOT NULL,
    keyalias VARCHAR( 10 ) NULL,
    lang character varying(5) NOT NULL,
    label character varying(50) NOT NULL
)");

    pg_query($cnx, "CREATE TABLE jsessions (
    id character varying(64) NOT NULL,
    creation timestamp NOT NULL,
    \"access\" timestamp NOT NULL,
    data bytea NOT NULL
)");


             pg_query($cnx, "SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('products', 'id'), 1, false)");

    pg_query($cnx, "ALTER TABLE ONLY labels_test ADD CONSTRAINT labels_test_pkey PRIMARY KEY (\"key\", lang)");

    pg_query($cnx, "ALTER TABLE ONLY labels_test ADD CONSTRAINT labels_test_keyalias UNIQUE (\"keyalias\")");

    pg_query($cnx, "ALTER TABLE ONLY products ADD CONSTRAINT products_pkey PRIMARY KEY (id)");

    pg_query($cnx, "ALTER TABLE ONLY jsessions ADD CONSTRAINT jsession_pkey PRIMARY KEY (id)");

    pg_close($cnx);
}

echo "  tables restored\n";


echo "Delete and restore all tables from the mysql database\n";
$tryAgain = true;

while ($tryAgain) {
    $cnx = @new mysqli("mysql", "jelix", 'jelixpass', 'jelixtests');
    if ($cnx->connect_errno) {
        throw new Exception('Error during the connection on mysql '.$cnx->connect_errno);
    }

    $tryAgain = false;
    $cnx->query('drop table if exists products');
    $cnx->query('drop table if exists labels_test');
    $cnx->query('drop table if exists jsessions');

    $cnx->query("CREATE TABLE IF NOT EXISTS `products` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT NOT NULL,
`create_date` datetime default NULL,
`promo` BOOL NOT NULL default 0,
`dummy` set('created','started','stopped') DEFAULT NULL
) ENGINE = InnoDB");

    $cnx->query("CREATE TABLE IF NOT EXISTS `labels_test` (
`key` INT NOT NULL ,
`keyalias` VARCHAR( 10 ) NULL,
`lang` VARCHAR( 5 ) NOT NULL ,
`label` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `key` , `lang` ),
UNIQUE (`keyalias`)
) ENGINE=InnoDb");

    $cnx->query("CREATE TABLE  IF NOT EXISTS `jsessions` (
  `id` varchar(64) NOT NULL,
  `creation` datetime NOT NULL,
  `access` datetime NOT NULL,
  `data` longblob NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");

    $cnx->close();
}

echo "  tables restored\n";



echo "Delete and restore all tables from the Sqlite3 database\n";

$SQLITE_FILE = '/app/tests/units/tests.sqlite3';

if (file_exists($SQLITE_FILE)) {
    unlink($SQLITE_FILE);
}

$sqlite = new Sqlite3($SQLITE_FILE);
$sqlite->exec("CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR( 150 ) NOT NULL ,
    price FLOAT NOT NULL,
    create_date datetime default NULL,
    promo BOOL NOT NULL default 0,
    dummy varchar(10) DEFAULT NULL
)");

$sqlite->exec("CREATE TABLE labels_test (
    \"key\" INTEGER PRIMARY KEY,
    keyalias varchar( 10 ) NULL,
    lang varchar(5) NOT NULL,
    label varchar(50) NOT NULL
)");
$sqlite->exec("CREATE TABLE jsessions (
  id varchar(64) NOT NULL,
  creation datetime NOT NULL,
  access datetime NOT NULL,
  data blob NOT NULL,
  PRIMARY KEY  (id)
);");
echo "  tables restored\n";





