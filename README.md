Lightweight object relation mapping based on the Database Access Object pattern. 

This library has been extracted from the [Jelix](https://jelix.org) framework 1.7,
and has been modernized a bit.


# installation

You can install it from Composer. In your project:

```
composer require "jelix/dao"
```

# Usage

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
// it is optional
$daosDirectory = '...';

$loader = new DaoLoader(
        $connector,
        $tempPath,
        $daosDirectory
);

$daoFile = 'myDao.xml';

$dao = $loader->get($daoFile);

// we can now use methods to query records

$list = $dao->findAll();

$record = $dao->get($primaryKey);

$list = $dao->myCustomMethod();


```
