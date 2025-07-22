# JelixDao

A lightweight object relation mapping based on the Database Access Object pattern.

It uses [JelixDatabase](https://github.com/jelix/JelixDatabase/) as database connector.

## installation

You can install it from Composer. In your project:

```
composer require "jelix/dao"
```

## Usage

First create a file `article.xml` describing the mapping:

```xml
<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <primarytable name="art" realname="newspaper.article" primarykey="id" />
   </datasources>
   <record>
      <property name="id"   fieldname="id" datatype="autoincrement"/>
      <property name="title" fieldname="title" datatype="string"  required="true"/>
      <property name="content" fieldname="content" datatype="text" required="true"/>
   </record>
</dao>
```

Then use the JelixDao API to manipulate objects:

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

$daoName = 'article'; // this the filename without path and extension

$dao = $loader->get($daoName);


// Storing a new object

$article = $dao->createRecord();
$article->title = "My title";
$article->content = "Lorem Ipsum";
$dao->insert($article);

echo "id of the new article: ".$article->id."\n";


// Query all records from the article table
$list = $dao->findAll();
foreach($list as $record) {
    echo $record->title;
}

// retrieve a single record
$artId = 1;
$article = $dao->get($artId);
echo $article->title;

// updating the record
$article->title = 'New title';
$dao->update($article);

// deleting a record
$dao->delete($article->id);

//...
```

## Main features

* Database type abstraction
* support of schema into table names (ignored in database that don't support schemas)
* Generate PHP classes for record, and for factories. Factory classes implement
  SQL queries that are mostly generated during compilation time, so they are
  not generated each time you call factories API.
* Generated factory classes have some common methods, but can also have
  custom methods (so custom queries) declared into the dao file
* Generated factory classes and record classes can inherits from your own
  classes.
* A dao file can import the definition of an other dao file
* support of json types: json content can be decoded/encoded dynamically, to/from
  anonymous object or your own classes.
* support of events: generated methods can dispatch an event before or after
  the query
* support of a listener class (hooks)
* support of automatic encoding/decoding for json fields

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

