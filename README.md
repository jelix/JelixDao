# JelixDao

A lightweight object relation mapping based on the Database Access Object pattern.

It uses [JelixDatabase](https://github.com/jelix/JelixDatabase/) as database connector.

## installation

You can install it from Composer. In your project:

```
composer require "jelix/dao"
```

## Usage

Quick start:

```php

use \Jelix\Database\AccessParameters;
use \Jelix\Database\Connection;
use \Jelix\Dao\DaoLoader;

// create a connector to the database
$accessParameters = new AccessParameters(
    array(
      'driver'=>'sqlite3',
      "database"=>"/...../tests.sqlite3",
    ), 
    array('charset'=>'UTF-8')
);

$connector = Connection::create($accessParameters);

// path to a directory where compiled class can be stored
$tempPath = '...'; 

// path to a directory where to find dao xml files
$daosDirectory = '...';

// instance of a dao loader, using a Context object
$loader = new DaoLoader(
    new \Jelix\Dao\Context(
        $connector,
        $tempPath,
        $daosDirectory
    )
);

$daoFile = 'myDao';

$dao = $loader->get($daoFile);

// we can now use methods to query records

$list = $dao->findAll();
foreach($list as $record) {
    echo $record->aField;
}

$record = $dao->get($primaryKey);
echo $record->aField;

$list = $dao->myCustomMethod();

//...
```

## Requirements

One of these database servers:

- Postgresql 13+
- Mysql 8+
- Sqlite, SQLServer
- Oracle (support not actively tested, help needed ;-))

## Documentation

The documentation is available into [the docs directory](docs/index.md).


## History

This library has been extracted from the [Jelix](https://jelix.org) framework 1.7/1.8,
and has been modernized a bit since. Except class names, API of factories and records are mostly the same.
The XML format of dao file is the same as in Jelix 1.6/1.7/1.8.

