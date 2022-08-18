# Usage


You have seen how to describe [a dao in a xml file](daofile.md). Remember that you 
actually define two objects:

* a dao object which is a factory, and inheriting from `Jelix\Dao\AbstractDaoFactory`:
  it allows you to retrieve, insert, modify, delete one ore several records.
  It proposes basic methods, but also has the methods that you will have
  described in the xml file in the `<factory>` section.
* a dao record object, inheriting form `Jelix\Dao\AbstractDaoRecord`, representing a
  database record whose properties are described in the xml file.

Now you should store this dao file somewhere, and tell JelixDao to use it.

## Setting a context

JelixDao is designed to be integrated in any application or even any framework.

It needs three things to work:
- a database connection from JelixDatabase
- a cache/temporary directory where to store classes it generates on the fly. This
  directory may be anywhere.
- the path to the dao file you want to use. The location of Dao files is not predetermined. 
  The name of dao files you give to JelixDao (or indicate into DAO files like import) 
  can be a kind of identifiant, it may not be the real name of a file.

All this information is given by a specific object, implementing the interface `Jelix\Dao\ContextInterface`.
Each framework or application can have their own implementation of the context, allowing JelixDao
to be integrated easily into them.

JelixDao provides a basic implementation for the case where there is not an existing one: the `Jelix\Dao\Context` object.

- it uses a database connection object you instantiate yourself
- it will read dao file from a directory you indicate
- it will use the temporary directory you indicate
- it will resolve all dao name `thename` as `thename.xml` file, and all custom PHP record files
  as `therecord.php` file.

An example of the use of `Jelix\Dao\Context`:


```php

use \Jelix\Database\AccessParameters;
use \Jelix\Database\Connection;

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

$context = new \Jelix\Dao\Context(
    $connector,
    $tempPath,
    $daosDirectory
);
```


## Getting a dao loader

In order to retrieve factories and records, you need a `Jelix\Dao\DaoLoader` object. Note that a framework 
or a library may have already instantiated a Dao loader.

If you need to instantiate yourself a dao loader, you need also a context object, and give it to its constructor.

```php

$context = ...; // here a \Jelix\Dao\ContextInterface object

$loader = new \Jelix\Dao\DaoLoader($context);

```

You can now use factories and records.


## Retrieving the factory and an empty DAO record

`\Jelix\Dao\DaoLoader` proposes several methods:

* `get()`: allows to get a factory. Always return the same instance (use a singleton)
* `create()`: allows to get a new instance of a factory. Rarely useful.
* `createRecord()`: allows to get an empty dao record object.

All of these methods take an identifiant (which can be a filename, depending on the context object)
of a dao file as parameter.

If the database connector used a profile specifying a table prefix, then all tables in the
dao file will be prefixed.

If we have `foo.xml` dao file, with the basic context object, you'll do:

```php

$myDao = $loader->get("foo");

$myNewRecord = $loader->createRecord("foo");

```

`$myDao` contains a factory of the //foo// dao, and `$myNewRecord` an empty record
of //foo// type.

You can also call the method `createRecord()` (which does not have parameters),
available on the factory:

```php
$myDao = $loader->get("foo");
$myNewRecord = $myDao->createRecord();
```

## Create, modify, delete a record

The `insert()`, `update()`, and `delete()` methods of the factory
are made for this. You specify a record to the first two methods. For
`delete`, you specify the keys of the record.

### Create

You should get a new record, fill it and then call the `insert` method of
the factory.

```php
// get the factory
$myFactory = $loader->get("foo");

// create a new record
$record = $myFactory->createRecord();

// fill the record
$record->foo = "hello";
$record->bar = "...";

// save the record
$myFactory->insert($record);

```

If there are some auto-incremented fields, the corresponding properties will be
updated by the `insert` method with the new value.


### Update

The process is the same as record creation: you retrieve a record, you modify
its properties, then you call the `update` method of the factory:

```php
// get the factory
$myFactory = $loader->get("foo");

// retrieve the record which have 3 as primary key
$record = $myFactory->get(3);

// fill the record
$record->foo = "hello";
$record->bar = "...";

// save the record
$myFactory->update($record);

```


### Delete

You should call the `delete` method of the factory, by giving the primary key of the 
record to delete.

```php
// get the factory
$myFactory = $loader->get("foo");

// delete the record which have 3 as id
$myFactory->delete(3);
```
