Changelog
=========

2.0.0-pre
----------

- Remove deprecated classes `jDaoConditions`, `jDaoFactoryBase`,
  `jDaoRecordBase`, `jDaoXmlException`

1.2.0
------


- Native support of JSON fields: dao properties having the datatype `json`
  are automatically encoded during insert/update, or decoded during a select.
- new feature: possibility to indicate a base class for the generated factory class.
  The classname should be indicated into the `extends` attribute of `<factory>`.
  The class can be anywhere and should be autoloadable.
  The class must inherit from `\Jelix\Dao\AbstractDaoFactory` and it must be abstract.
- Support of schema names into tables names.
- Fix deprecation warning with PHP 8.4
- Fix generator: better generated joins
- Introduce compatibility with applications that are using jDao API of Jelix 1.8 
  and lower: classes of JelixDao inherit from some empty classes or empty interfaces
  having the name of old implementation, so objects can be passed to functions that
  have parameters typed with theses classes (`jDaoConditions`, `jDaoFactoryBase`, 
  `jDaoRecordBase`, `jDaoXmlException`). This feature will be removed into the
  next major version of JelixDao.



1.1.0 (2023-12-23)
-------------------

- New context class `Jelix\Dao\JelixModuleContext` to load dao from the daos directory of a Jelix module.
- Fix deprecation notices with PHP 8.2/8.3


1.0.1 (2023-01-22)
------------------

Fix an issue with PHP 8.

1.0.0 (2022-08-18)
------------------

First release of the standalone version of jDao, the ORM library of the Jelix framework. Almost methods and properties 
of records and factories are the same as in jDao of Jelix 1.7/1.8, as well as the XML format of dao file.
Class names has been changed, and are into a namespace.


