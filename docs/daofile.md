# A Dao file

In order to use JelixDao, you have to write an XML file which describes the mapping,
i.e, which properties of a JelixDao record correspond to which field in a SQL table.


## Details on the XML format

```xml
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
     datasources section
   </datasources>
   <record>
      properties section
   </record>
   <factory>
      methods section
   </factory>
</dao>
```

There are three sections, although the `<factory>` section is optional. This
section is described [in an other chapter](xml_methods).

- `<datasources>`: defines the tables on which the object will be mapped.
- `<record>`: defines the mapping itself.

## Simple mapping

### Table declaration

We call simple mapping, a mapping where a record is mapped to a single table. To
declare the table, we use the tag `<primarytable>` with the following
attributes:

* `name`: alias given to the table and that will be used in SQL queries
* `realname` (optional): real name of the table in the database. If this
  attribute is not specified, it takes the same value as the attribute
  `name`. In this case `name` must contain the real name of the
  table.
* `schema` (optional): a schema name, for database that supports
  it (Postgresql, Oci, SqlServer). The schema can also be indicated
  into `realname`.
* `primarykey` indicates the primary key. You can specify multiple
  fields, separated by a space or a comma.


```xml
  <datasources>
      <primarytable name="p" realname="products" primarykey="id_product" />
  </datasources>
```


It says here that the record will be based on the table `products`, which has
the alias `p`, and which contains the primary key `id_product`.

There is always a single table "primary" in a DAO (hence a single tag
`<primarytable>`). You will see that you can specify other tables (foreign
tables) farther.

Then we should declare the mapping between the record fields and the object properties.

### Properties declaration

The `<record>` section declares the properties of an object `record`. Each
property corresponds to a field in the primary table, or one of those foreign
tables as you will see later. Of course, you are not obliged to declare a
property for all existing fields. We can make several DAO working on the same
table but which are intended for different uses. For example make a specific DAO
to recover slight registration lists (so the mapping is made on the essential
properties), and an other to manage full datas (so the mapping is made on all
fields).

The `<record>` section must contain one or more `<property>` element.
Here is the `<property>` element with possible attributes:

```xml
  <property 
      name="simplificated name" 
      fieldname="filed name" 
      datatype="" required="true/false" minlength="" maxlength="" regexp="" 
      sequence="sequence name" autoincrement="true/false"
      updatepattern="" insertpattern="" selectpattern=""
      default="" comment=""
   />
```

The attribute `name` is the name of the property of the object.

The attribute `fieldname` is the field name on which the property is mapped.
If `name` and `fieldname` are equals, we can leave `fieldname`.

The attributes `datatype`, `required`, `minlength`, `maxlength`
and `regexp` are constraints. This allows the `check()` method to verify
the values of properties (before storage for instance).

The attribute `default` allows you to specify a default value which will be
stored in the property.

The attribute `datatype` indicates the type of the field. Here is the list
of recognized types. 

* `varchar`, `varchar2`, `nvarchar2`, `character`, `character varying`, `char`, `nchar`, `name`, `longvarchar`, `string` (deprecated),
* `int`, `integer`, `tinyint`, `smallint`, `mediumint`, `bigint`,
* `serial`, `bigserial`, `autoincrement`, `bigautoincrement`,
* `double`, `double precision`, `float`, `real`, `number`, `binary_float`, `binary_double`, `money`,
* `numeric`, `decimal`, `dec`,
* `date`, `time`, `datetime`, `timestamp`, `utimestamp`, `year`, `interval`
* `boolean`, `bool`, `bit`
* `tinytext`, `text`, `mediumtext`, `longtext`, `long`, `clob`, `nclob`
* `tinyblob`, `blob`, `mediumblob`, `longblob`, `bfile`,
* `varbinary`, `bytea`, `binary`, `raw`, `long raw`
* `enum`, `set`, `xmltype`, `point`, `line`, `lsed`, `box`, `path`, `polygon`, `circle`, `cidr`, `inet`, `macaddr`, `bit varyong`, `arrays`, `complex types`
* `json` and `jsonb` (see section below)

Of course, some database doesn't recognize all of these types. Don't worry, JelixDao
will use the corresponding type in the selected database. For example, `bytea`
is a postgresql type. If you use a mysql database, JelixDao will use `longblob`.

Values of fields will be converted in the corresponding PHP type, most of time,
in a string.

For auto-incremented fields, you can indicate `serial` or `autoincrement` as
a type. But for some case, it is better to indicate one of integer types, and to
set a `autoincrement` attribute to `true`. On some databases, an auto
incremented field can be associated with a sequence. Then the attribute
`sequence` should contain the sequence name.

The attribute `comment` allow to indicate a comment on the property. This comment
can be used as label for form field, with the createform command.

The attributes `updatepattern`, `insertpattern` and `selectpattern`
lets you specify a pattern to be applied during the update, the insert or the
read of the field value. This pattern should really be a SQL expression, and can
contain the string `%s`, which will be replaced by the value or the name of the
field. Default values of patterns is `%s`. If it indicates an empty value, this
corresponds to a null operation (so the field is not readed, inserted or
updated). On primary keys you can use `insertpattern` and
`selectpattern` , but not `updatepattern`.

#### Example 1

For a field which contains an updated date, we can do:

```xml
  <property name="date_update" datatype="datetime" insertpattern="NOW()" updatepattern="NOW()" />
```

So each time there is an insert or an update, the inserted value will be the current date.


#### Example 2

It may also have a property that does not correspond directly to a field, but
that is the result of a SQL expression. In this case, you must disable the
inserting and updating.

```xml
   <property name="identite" datatype="string" selectpattern="CONCAT(name,' ',firstname)" insertpattern="" updatepattern="" />
```

Carefull about the content of `selectpattern`:

* Expression must use fields of a single table. If a dao is based on multiple
  tables (for example, A and B, see next section), it is not possible to
  indicate both fields from the table A and the table B in the same
  `selectpattern`.
* If the expression uses some fields of the table B (a foreign table), then
  corresponding properties should be declared for this table, with the
  `table` attribute on the `<property>` element, with the name or
  alias of the table B, as a value.

### Support of JSON fields

When you indicate `json` (or `jsonb`) as datatype, content of the property
is automatically encoded (during insert/update) or decoded (during selects).

```xml
   <property name="configuration" datatype="json" />
```

You can disable this feature if you want to manage the json content by yourself.
Especially if the json content is huge, and you don't want to decode it 
systematically, because it could cause performance issues.

```xml
   <property name="configuration" datatype="json" jsontype="raw" />
```

You can force the type, by indicating `object` or `array`

```xml
   <property name="configuration" datatype="json" jsontype="array" />
```

If you want to map the json content to an object having a specific class,
indicate the class name into the `jsonobjectclass` attribute. In this case, `jsontype="object"` is
not required.


```xml
   <property name="configuration" datatype="json" jsonclass="\MyProject\Configuration" />
```

The constructor of the class should not require parameters. And properties
corresponding to the json object properties must be public.

If the constructor requires parameters and/or properties are not public, 
you can indicate your own json encoder/decoder.

The encoder must accept an object as parameter, and return a string (the json content).
The decoder must accept a string (the json content) and return an object of the given class.

Encoder/Decoder can be static methods of the class. Note the `::` operator.

```xml
   <property name="configuration" datatype="json" 
             jsonclass="\MyProject\Configuration"
             jsonencoder="::toJson"
             jsondecoder="::createFromJson"
/>
```

An example of the implementation of these methods into the `\MyProject\Configuration` class:

```php
namespace MyProject;

class Configuration
{
    public function __construct(
        protected string $parameter1,
        protected string $parameter2
    ) 
    {   
    }

    public static function toJson(Configuration $object)
    {
        return json_encode(['p1'=>$object->parameter1, 'p2'=>$object->parameter2], JSON_FORCE_OBJECT);
    }

    public static function createFromJson($json)
    {
        $obj = json_decode($json);
        return new Configuration($obj->p1, $obj->p2);
    }
}

```

You can indicate static methods of another class :

```xml
   <property name="configuration" datatype="json" 
             jsonclass="\MyProject\Configuration"
             jsonencoder="MyJsonSerializer::toJson"
             jsondecoder="MyJsonSerializer::createFromJson"
/>
```

Or some functions:

```xml
   <property name="configuration" datatype="json" 
             jsonclass="\MyProject\Configuration"
             jsonencoder="myJsonEncoder"
             jsondecoder="myJsonDecoder"
/>
```

## Mapping on several tables

We can declare a table, but also additional tables which are linked to the main
table by joins. It is useful when you want to retrieve simultaneously a record
and information of other tables. For example, if you want to retrieve a product
of the "products" table, and at the same time the name of its category from the
table "category", you should also declare the table "category". Note that you
can modify only data which come from the main table when you want to update or
insert a record.

To declare such foreign tables, which are logically related to the main table by
foreign keys, you should use:

* `<foreigntable>` to indicate a foreign table  linked by a normal join.
* `<optionalforeigntable>` to indicate a foreign table linked by an outer join.

Example:

```xml
   <primarytable name="p" realname="products" primarykey="id_product" />
   <foreigntable name="cat" realname="categories" primarykey="id_cat" onforeignkey="id_cat" />
   <optionalforeigntable name="man" realname="manufacturers" primarykey="id" onforeignkey="id_manufacturer" />
```

As for tag `<primarytable>`, there are attributes `name`, `realname`, `schema`
and `primarykey`. There is also an additional attribute,
`<onforeignkey>`, which indicates the name of the field in the primary
table, which is the foreign key on the table in question. Thus, with the above
example, JelixDao generate requests for type `SELECT` terms `FROM` and
`WHERE`:

```sql
 FROM products as p 
     LEFT JOIN manufacturers as man on (p.id_manufacturer = man.id),
     categories as cat
 WHERE cat.id_cat = p.id_cat
```


You can add `<property>` element to map properties to field of foreign
tables, like any other property for the main table. The only difference is that
you have to add an attribute `table` which indicates the alias of the table
in which the field belongs to.

```xml
  <property 
      name="category_label" 
      fieldname="label" 
      table="cat"
   />
```


## Using a PHP class for records

You would want to have methods and additionnal properties on the
record object generated by JelixDao. This is possible by providing your
own class, which will inherits from `Jelix\Dao\AbstractDaoRecord`, and will be inherited
by the generated class.

**Warning**: this class does not suppose to do SQL request on the table of the DAO.
Your methods should only do some calculation, verify some business rules etc.. If you want
to do SQL request, see "XML or PHP methods" on the DAO factory..

Your class have the name you want, and it should be stored into the same directory of other dao files, 
in a file named `<classname>.php`.

You indicate the class name in the `extends` attribute of the `<record>` element.

Note that your class should inherit from `Jelix\Dao\AbstractDaoRecord`.

Example, in your dao file:

```xml
   <record extends="news">
   ...
   </record>
```

Records will be objects from the class `news` stored in the file
`news.php`.

```php
class news extends \Jelix\Dao\AbstractDaoRecord {

    function calculateSomething() 
    {
        $this->field1 = $this->field2 * 1.196;
    }

    function calculateTotal() 
    {
        return $this->amount + $this->vat;
    }
}
```


Then you can call this method on each records:

```php

// (you will see later what is $myDaoLoader...)
$myFactory = $myDaoLoader->get("news");
$list = $myFactory->findAll();

foreach ($list as $row) {
    $total = $row->calculateTotal();
    //...
}
```


## Importing an other dao

In your project, you'll need sometime to create a dao that have the same
content as another one, with additional properties or methods.

To avoid to rewrite all datasources/properties/methods, you can //import//
a dao, and write only new properties or methods. You do that by adding
an attribute `import` on the `dao` element.

How it works: 

* The DAO to import is read first, so your dao will contain all of
  its datasource, properties or methods.
* Then your dao is read. All of its datasource, properties or methods
  are added or overwrite existing properties/methods
* The PHP class is then generated

Note that an import is **not** an inheriting as in PHP.

For example, you may have a library having a dao defining a "user" table. You may want to reuse
this dao, but you want to add new fields and methods. So you can import the original dao into your own dao:

```xml
<dao import="user">
	<datasources>
		<primarytable name="user" realname="myusertable" primarykey="login" />
	</datasources>
	<record>
		<property name="nationality" fieldname="nationality" datatype="string" maxlength="100" required="true">
	</record>
	<factory>
		<method name="findByNationality" type="select">
			<parameter name="nationality" />
			<conditions>
				<eq property="nationality" expr="$nationality" />
			</conditions>
		</method>
	</factory>
</dao>
```

