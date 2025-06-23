# Manipulating results sets

Creating, modifying and deleting records in a database is very useful. However,
it's not as useful as being able to fetch records and use them. The page on the
[use of a dao factory and record](usage.md) showed you the creation, the deletion and the
update. This is the page on the fetch, or the "select data" part of JelixDao.

Depending on how and how much results are needed, the method is somewhat different.

## Retrieve a single record

To retrieve a single record by specifying it's key (the primary key in the database),
the method to use is called `get()`.

There is no need to further parse the return value of the `get()` function since
it will fetch the specified record and return it as an object. The object is design
so that each column name is the name of a variable, and the column value, the variable value.

```php
// instantiation of the factory
$myFactory = $loader->get("foo");

// retrieve a record whose content corresponds
// to the record with identifier = 3
$baz = $myFactory->get(3);

// the id of the record is 3, as we specified
echo $baz->id;
```

In the case where the primary key is made of several fields, declared like this in your
xml file, `primarykey="key1,key2"`, you have to give all key values at the same time:

```php
$myFactory = $loader->get("foo");

// 3 and 4 are values of key1 and key2
$baz = $myFactory->get(3, 4);

// or
$baz = $myFactory->get([3, 4]);

```



Careful: the order of the values should be the same as the order of the declaration
of keys in the `primarykey` attribute. Eg, `$myFactory->get(4, 3)` is different
from `$myFactory->get(3, 4)`.

The `delete()` method works in the same way.

## Retrieving several records

When all the records are needed, the `findAll()` method is used. This method
will return a result set, (an object implementing `Jelix\Database\ResultSetInterface`) 
containing all the results from the database in the order they were added to the database.
Remember that this class implements the `Iterator` interface, so it can be used into a
`foreach` statement.

```php
// instantiation of the factory
$myFactory = $loader->get("foo");

// retrieve a complete list of the foo type records
$resultSet = $myFactory->findAll();

foreach ($resultSet as $row) {
    // $row content a record
    echo $row->id;
}
```

You can use also two other methods to retrieve records. The first one being `fetch()`
which fetches one row from the result set as an object and advances the internal
pointer one step.

```php
// fetch the records one at the time
while ($row = $resultSet->fetch()) {
    // do something with the record
    echo $row->id;
}
```

The second one being `fetchAll()` which returns an array containing an object
for each row in the result set.

```php
    // fetch all the records as an array of objects
    $rows = $resultSet->fetchAll();

    foreach ($rows as $rowID => $row) {
        // do something with the record
        echo $row->id;
    }
```

You can make other retrieving methods by specifying them in the xml file. See
[Declaring methods in the XML file](xml_methods.md) and [Adding PHP methods to the factory](php_methods.md).

## Retrieve record(s) through criteria

To retrieve record(s) through criteria, there are three simple methods available,
`findBy()`, `countBy()` and `deleteBy()`. These methods have one
required parameter, a `Jelix\Dao\DaoConditions` object, which contains all the conditions
to filter the results.

```php
$a_name = "something";

// create the DaoConditions object
$conditions = new \Jelix\Dao\DaoConditions();
$conditions->addCondition('label', '=', $a_name);
$conditions->addCondition('status', '=', 5);

$recordSet = $myFactory->findBy($conditions);
$count = $myFactory->countBy($conditions);
```

In the same way as the `findAll()` method, `findBy()` returns an object implementing
`Jelix\Database\ResultSetInterface`, which gives you  the list of records corresponding
to indicated criteria.

The `addCondition()` method take as parameter a property name, an operator
(SQL), and a value.

You can specify an order of select with the method `addItemOrder()`, and group
various criterion together with `startGroup()` and `endGroup()`:

```php
$conditions = new \Jelix\Dao\DaoConditions();

// condition : label = $a_name AND (status=5 OR status=4) ORDER BY label desc
$conditions->addCondition('label','=',$a_name);   
$conditions->startGroup('OR');
$conditions->addCondition('status','=',5);
$conditions->addCondition('status','=',4);
$conditions->endGroup();
$conditions->addItemOrder('label','desc');

$list = $myFactory->findBy($conditions);
```

To add a limit, you can specify additional parameters to the `findBy()` method:
the offset and the number of records to fetch.

```php
$list = $myFactory->findBy($conditions, 0, 15);
```

As for the `countBy()` method it takes one additional parameter: the property
name that we want to apply a DISTINCT clause.

```php
// SELECT COUNT(DISTINCT table.label)...
$count = $myFactory->countBy($conditions, 'label');
```

Lastly, the `deleteBy()` method allow to delete records corresponding to indicated
criteria, it returns the number of deleted records:

```php
$nb_deleted = $myFactory->deleteBy($conditions);
```

You will see that you can get the same result by defining methods in the xml file.
However, you will choose `findBy()` or a xml-defined method, depending on the use case.

Criteria-based retrieving is best used when you don't know the number of criterion
and their type. This includes things such as a complex search form, where the user
can choose which criterion are applied. Criteria-based retrieving is also best used
when the query is used only one time or very rarely. This is because the xml-defined
methods are compiled to PHP, and thus are included each time you call the factory.
It may not be useful to always include some code which is rarely used. Using
criteria-based retrieving will improve the global performances in that case.

In the other cases, it is recommended to use xml methods, especially when you know
the criteria by advance (without forcibly knowing their value of course), and when
it is an often used research. JelixDao generates SQL queries during the generation of
the PHP file, so this is a process it has not to do when executing the page.

For example, we often redefine the `findAll` method in XML, to be able to specify
a retrieving order.

#### SQL operators

As explained before, `addCondition()` takes a sql operator as a second parameter.
Here are the operators supported:

* `LIKE`, `NOT LIKE`, `ILIKE`
* `IN`, `NOT IN`,
* `IS`, `IS NOT`,
* `IS NULL`, `IS NOT NULL`,
* `MATCH`, `REGEXP`, `NOT REGEXP`, `RLIKE`, `SOUNDS LIKE`
* `@, `!~`, `~*`, `!~*`  (operators for regular expression for postgresql)

To test a (non) NULL value, you can use any supported operators, even `=` or `!=`.
Don't forget to pass the php `null` value, not the string `"NULL"`.

```php
    $conditions->addCondition('status','=', null); // equivalent to IS NULL
    $conditions->addCondition('status','!=', null);  // equivalent to IS NOT NULL
    $conditions->addCondition('status','IS', null);
    $conditions->addCondition('status','IS NOT', null);
    $conditions->addCondition('status','IS NULL', null);
    $conditions->addCondition('status','IS NOT NULL', null);
    $conditions->addCondition('status','LIKE', null);
    $conditions->addCondition('status','NOT LIKE', null);
    ...
```

Some databases like Postgresql support regular expressions. For postgresql you can then 
use keywords `@, `!~`, `~*`, `!~*`.

```php
    $conditions->addCondition('status','~', '^test');
    ...
```

Other operator like `REGEXP`, `NOT REGEXP` are also supported. Verify first if it is 
compatible with your database.
