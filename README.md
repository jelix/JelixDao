Lightweight object relation mapping based on the Database Access Object pattern. 

This library has been extracted from the [Jelix](https://jelix.org) framework 1.7,
and has been modernized a bit.

The work to use it outside Jelix is in progress.

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
$daosDirectory = '...';

// instance of a dao loader, using a Context object
$loader = new DaoLoader(
    new \Jelix\Dao\Context(
        $connector,
        $tempPath,
        $daosDirectory
    )
);

$daoFile = 'myDao.xml';

$dao = $loader->get($daoFile);

// we can now use methods to query records

$list = $dao->findAll();

$record = $dao->get($primaryKey);

$list = $dao->myCustomMethod();

```

Documentation is not available yet. But you can read the documentation of 
the original library from Jelix, to know more : [jDao](https://docs.jelix.org/en/manual-1.7/components/daos).
