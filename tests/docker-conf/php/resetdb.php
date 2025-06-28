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
    pg_query($cnx, "drop table if exists products_tags");
    pg_query($cnx, "drop table if exists labels_test");
    pg_query($cnx, "drop table if exists jsessions");
    pg_query($cnx, "drop table if exists newspaper.article");
    pg_query($cnx, "drop table if exists newspaper.article2");
    pg_query($cnx, "drop table if exists newspaper2.article2_category");
    pg_query($cnx, "drop table if exists newspaper.article3");
    pg_query($cnx, "drop table if exists newspaper2.article3_category");

    pg_query($cnx, "CREATE TABLE products (
        id serial NOT NULL,
        name character varying(150) NOT NULL,
        price real NOT NULL,
        create_date time with time zone,
        promo boolean NOT NULL  default 'f',
        dummy character varying (10) NULL CONSTRAINT dummy_check CHECK (dummy IN ('created','started','stopped')),
        metadata jsonb default null,
        metadata2 jsonb default null,
        metadata3 jsonb default null,
        metadata4 jsonb default null,
        metadata5 jsonb default null,
        metadata6 jsonb default null,
        metadata7 jsonb default null
    )");

    pg_query($cnx, "CREATE TABLE products_tags (
    product_id integer NOT NULL,
    tag character varying(50) NOT NULL
);");


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
    data bytea NOT NULL,
    metadata jsonb DEFAULT NULL
)");
    pg_query($cnx, "CREATE SCHEMA IF NOT EXISTS newspaper");
    pg_query($cnx, "CREATE SCHEMA IF NOT EXISTS newspaper2");

    pg_query($cnx, "CREATE TABLE newspaper.article (
        id serial NOT NULL,
        title VARCHAR( 255 ) NOT NULL,
        content TEXT NOT NULL
)");
    pg_query($cnx, "SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('newspaper.article', 'id'), 1, false)");

    pg_query($cnx, "CREATE TABLE newspaper.article2 (
        id serial NOT NULL,
        category_id integer NOT NULL,
        title VARCHAR( 255 ) NOT NULL,
        content TEXT NOT NULL
)");
    pg_query($cnx, "SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('newspaper.article2', 'id'), 1, false)");

    pg_query($cnx, "CREATE TABLE newspaper2.article2_category (
        catid serial NOT NULL,
        label VARCHAR( 255 ) NOT NULL
)");
    pg_query($cnx, "SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('newspaper.article2', 'id'), 1, false)");

    pg_query($cnx, "SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('products', 'id'), 1, false)");

    pg_query($cnx, "ALTER TABLE ONLY labels_test ADD CONSTRAINT labels_test_pkey PRIMARY KEY (\"key\", lang)");

    pg_query($cnx, "ALTER TABLE ONLY labels_test ADD CONSTRAINT labels_test_keyalias UNIQUE (\"keyalias\")");

    pg_query($cnx, "ALTER TABLE ONLY products ADD CONSTRAINT products_pkey PRIMARY KEY (id)");

    pg_query($cnx, "ALTER TABLE ONLY products_tags ADD CONSTRAINT products_tags_pkey PRIMARY KEY (product_id, tag)");

    pg_query($cnx, "ALTER TABLE ONLY jsessions ADD CONSTRAINT jsession_pkey PRIMARY KEY (id)");
    pg_query($cnx, "ALTER TABLE ONLY newspaper.article ADD CONSTRAINT article_pkey PRIMARY KEY (id)");
    pg_query($cnx, "ALTER TABLE ONLY newspaper.article2 ADD CONSTRAINT article2_pkey PRIMARY KEY (id)");
    pg_query($cnx, "ALTER TABLE ONLY newspaper2.article2_category ADD CONSTRAINT article2_category_pkey PRIMARY KEY (catid)");
    pg_query($cnx, "ALTER TABLE ONLY newspaper.article2 ADD CONSTRAINT article2_cat_pkey FOREIGN KEY (category_id) REFERENCES newspaper2.article2_category(catid)");

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
    $cnx->query('drop table if exists products_tags');
    $cnx->query('drop table if exists labels_test');
    $cnx->query('drop table if exists jsessions');
    $cnx->query('drop table if exists article');
    $cnx->query('drop table if exists article2');
    $cnx->query('drop table if exists article2_category');
    $cnx->query('drop table if exists article3');
    $cnx->query('drop table if exists article3_category');

    $cnx->query("CREATE TABLE IF NOT EXISTS `products` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT NOT NULL,
`create_date` datetime default NULL,
`promo` BOOL NOT NULL default 0,
`dummy` set('created','started','stopped') DEFAULT NULL,
`metadata` JSON default NULL,
`metadata2` JSON default NULL,
`metadata3` JSON default NULL,
`metadata4` JSON default NULL,
`metadata5` JSON default NULL,
`metadata6` JSON default NULL,
`metadata7` JSON default NULL
) ENGINE = InnoDB");

    $cnx->query("CREATE TABLE `products_tags` (
    `product_id` INT NOT NULL ,
    `tag` VARCHAR( 50 ) NOT NULL ,
    PRIMARY KEY ( `product_id` , `tag` )
) ENGINE = InnoDb");

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
  `metadata` JSON DEFAULT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;");

    $cnx->query("CREATE TABLE IF NOT EXISTS article (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        title VARCHAR( 255 ) NOT NULL,
        content TEXT NOT NULL
)  ENGINE=InnoDb");

    $cnx->query("CREATE TABLE IF NOT EXISTS article2_category (
        `catid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        label VARCHAR( 255 ) NOT NULL
)  ENGINE=InnoDb");


    $cnx->query("CREATE TABLE IF NOT EXISTS article2 (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        category_id INT NOT NULL,
        title VARCHAR( 255 ) NOT NULL,
        content TEXT NOT NULL,
        FOREIGN KEY (category_id) REFERENCES article2_category (catid)
)  ENGINE=InnoDb");

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
    dummy varchar(10) DEFAULT NULL,
    metadata TEXT DEFAULT NULL,
    metadata2 TEXT DEFAULT NULL,
    metadata3 TEXT DEFAULT NULL,
    metadata4 TEXT DEFAULT NULL,
    metadata5 TEXT DEFAULT NULL,
    metadata6 TEXT DEFAULT NULL,
    metadata7 TEXT DEFAULT NULL
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
  metadata TEXT DEFAULT NULL,
  PRIMARY KEY  (id)
);");

$sqlite->exec("CREATE TABLE article (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR( 255 ) NOT NULL,
        content TEXT NOT NULL
)");

$sqlite->exec("CREATE TABLE article2 (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        title VARCHAR( 255 ) NOT NULL,
        content TEXT NOT NULL
)");

$sqlite->exec("CREATE TABLE article2_category (
        catid INTEGER PRIMARY KEY AUTOINCREMENT,
        label VARCHAR( 255 ) NOT NULL
)");

$sqlite->exec("CREATE TABLE products_tags (
    product_id integer NOT NULL,
    tag character varying(50) NOT NULL,
    PRIMARY KEY (product_id,tag)
)");

echo "  tables restored\n";





