# Declaring PHP methods

[XML methods](xml_methods.md) are useful and practical in a lot of case. Anyhow, they
have some limits and are useless for complex queries.

One could for sure code those complex queries in classic PHP using JelixDatabase directly.
However, if your datasets associated are already mapped through a DAO, it would
be sensible to insert such queries in the DAO factory.

There are two solutions :

- declaring the php methods into the XML file
- using a custom class extending `Jelix\Dao\AbstractDaoFactory`, since version 1.2.0
  This is the recommended way.


## The PHP method type into the XML file

You have to declare in your XML factory a method of type `php`:

```xml
  <method type="php" name="foo">
    <parameter name="bar" />
    <body><![CDATA[   
        // php code
    ]]></body> 
  </method>
```

XML tags used in classic XML methods can not be used here (`conditions`, `order`...)
as you have to code directly your SQL query. However, you still have to define a
method name and you can use `parameter`.


## Using a custom class extending AbstractDaoFactory

Since version 1.2.0, you can use a custom class extending `Jelix\Dao\AbstractDaoFactory`
to declare your PHP methods.

- The classname should be indicated into the `extends` attribute of `<factory>`.
- The class can be anywhere and should be autoloadable.
- The class must inherit from `\Jelix\Dao\AbstractDaoFactory` and it must be abstract.

Example:

```php
<?php
namespace Jelix\DaoTests;

use Jelix\Dao\AbstractDaoFactory;

abstract class CustomPostBlogFactory extends AbstractDaoFactory
{
    public function myCustomMethod($email)
    {
    }
}

```

And in your XML file:

```xml

<?xml version="1.0" encoding="UTF-8"?>
<dao xmlns="http://jelix.org/ns/dao/1.0" import="posts">
    ...
    <factory extends="\Jelix\DaoTests\CustomPostBlogFactory">
        <method name="otherSelect" type="select">
            ...
        </method>
    </factory>
</dao>
```



## Factory internal API

You can almost do everything in your PHP code but remember to:

* Respect the DAO pattern. You have to return record objects as defined by the DAO. 
  You'll find the record class name into the `_DaoRecordClassName` property of the factory
* Use `_conn` property to execute queries: it contains a database connector, implementing the `Jelix\Database\ConnectionInterface` interface.
* Use other helpers methods and properties of the factory, see the `Jelix\Dao\AbstractDaoFactory` class.

In the following example, you have to put the code into a PHP method of a custom class
or into an XML method.

### Code pattern

There are 2 ways to code PHP methods:

- either hardcoding queries
- either coding queries by using factory and record informations (table names, field names,
  field types, and so on)

The first one performs a bit better and is simpler. But the latter is more
maintainable as the generated query parts are updated when `datasources` or
`properties` are modified. It is up to you to choose which one is your favourite.

Below is a list of properties and methods, you should use for the more
maintainable way of coding PHP methods:

* `$this->_primaryTable` : primary table alias,
* `$this->_tables` : table informations,
* `$this->getProperties()` : detailed record properties informations,
* `$this->getPrimaryKeyNames()` : primary key properties.

On a record object, `getProperties()` and `getPrimaryKeyNames()` are also defined.

For details, see the `Jelix\Dao\AbstractDaoFactory` class..

### Prepare values

Each value passed as a parameter has to be "filtered" or "prepared" before being
injected in a SQL query. This prevents security problems of SQL injection.

The preferred way is to use, of course, a prepared query.

Example in a custom class:

```php
public function updateLabel($id, $label) 
{
    $sql = 'update '.$this->_tables[$this->_primaryTable]['realname'];
    $sql.= ' set label= :label where product_id=:id';
    $stmt = $this->_conn->prepare($sql);
    $stmt->execute(array(
        'label' => $label,
        'id' => $id
    ));
    return $stmt->rowCount();
}
```

or in an XML method:

```xml
  <method type="php" name="updateLabel">
    <parameter name="id" />
    <parameter name="label" />
    <body><![CDATA[
        $sql = 'update '.$this->_tables[$this->_primaryTable]['realname'];
        $sql.= ' set label= :label where product_id=:id';
        $stmt = $this->_conn->prepare($sql);
        $stmt->execute(array(
            'label' => $label,
            'id' => $id
        ));
        return $stmt->rowCount();
    ]]></body> 
  </method>
```

If the construction of the SQL query string is more complex, you may want to use
the `_prepareValue()` method. It accepts two arguments : a value and a data type (which refers to `datatype` attribute  of a `property` tag).

```php
public function updateLabel($id, $label) 
{
    $sql = 'update '.$this->_tables[$this->_primaryTable]['realname'];
    $sql.= ' set label=' . $this->_prepareValue($label,'string');
    $sql.= ' where product_id='.$this->_prepareValue($id,'integer');
    return $this->_conn->exec($sql);
}
```

However, consider the `_prepareValue()` as deprecated. It was implemented when JelixDatabase didn't support prepared queries.
Prefer to use a prepared query.

### `SELECT` queries

Usually, if you code a `SELECT` query, you should return every field declared
by `property` tags and thus respect join statements. Moreover returned objects 
should be of dao record type. Some helpful properties simplifies the task
greatly:

* `_selectClause` : defines `SELECT` clause with all declared fields.
* `_fromClause` : defines `FROM` clause with all table names and external joins.
* `_whereClause` : defines `WHERE` clause with inner joins.

A method that returns results would look like:

```php
public function findByPrice($price)
{
    $sql = $this->_selectClause.$this->_fromClause.$this->_whereClause;
    $sql .= ($this->_whereClause == ''?' WHERE ':' AND ');
    $sql .= ' price = :price';
    $sql .= ' ORDER BY label ASC';
    
    $stmt = $this->_conn->prepare($sql);
    $stmt->setFetchMode(
        \Jelix\Database\ConnectionConstInterface::FETCH_CLASS, 
        $this->_DaoRecordClassName
    );
    
    $stmt->execute([
        'price' => $price
    ]);
    return $stmt;
}
```

Note: this method could very well be declared with an XML method `<method name="findByPrice" type="select">`
but it is provided here as an example.

* `_whereClause` can be empty and therefore has to be tested to add other conditions.
* It uses `$this->_conn` to execute query.
* Mandatory : `setFetchMode()`, sets the class name of record objects returned. `_DaoRecordClassName` is passed.
* the record set representing by the $stmt object is returned directly: it implements `Iterator`, and
  as a result can be iterated in a foreach loop. No need of an intermediate
  list.


#### Single record query

It is not so different from the above:

```php
public function findByLabel($label)
{
    $sql = $this->_selectClause.$this->_fromClause.$this->_whereClause;
    $sql .= ($this->_whereClause == ''?' WHERE ':' AND ');
    $sql .= ' label = :label';
    $sql .= ' ORDER BY label ASC';

    $stmt = $this->_conn->prepare($sql);
    $stmt->setFetchMode(
        \Jelix\Database\ConnectionConstInterface::FETCH_CLASS, 
        $this->_DaoRecordClassName
    );
    
    $stmt->execute([
        'label' => $label
    ]);
    $record = $stmt->fetch();
    return $record;
}
```

Note the use of `fetch()` method on the result set object.

### Other queries

For `UPDATE`, `DELETE`, `INSERT` queries, you obviously won't use
`_selectClause`, `_fromClause` and `_whereClause`.

Still another helper method can be used for `UPDATE` et `DELETE`:
`_getPkWhereClauseForNonSelect()`. It generates a `WHERE` condition on the
primary keys.

```php
public function updateLabel($id, $label)
{
    $sql = 'update products set label= :label ';

    // construct the WHERE clause with condition on the primary key
    $keys = array_combine($this->getPrimaryKeyNames(), array($id));
    $sql.= $this->_getPkWhereClauseForNonSelect($keys);

    $stmt = $this->_conn->prepare($sql);
    $stmt->execute([
        'label' => $label
    ]);
    return $stmt->rowCount();
}
```

If there is more than one primary key, `_getPkWhereClauseForNonSelect()` is actually interesting.

For `UPDATE`, `DELETE`, and `INSERT` queries, your method should return the number of affected rows.
Use the `rowCount()` method of the query statement for that.
