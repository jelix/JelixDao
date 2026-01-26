Changelog
=========

2.0.0-pre
----------

- Remove deprecated classes
  - `jDaoConditions`, `jDaoFactoryBase`, `jDaoRecordBase`, `jDaoXmlException`
  - `DeprecatedContextProxy`.
  - `CustomRecordClassFileInterface`, `CustomRecordClassFile`
- Methods of `ContextInterface2` have been moved to `ContextInterface`. `ContextInterface2` is deprecated.
  So all classes implementing `ContextInterface` should implement these methods.
- Deprecated Methods `ContextInterface::getConnector()` and `ContextInterface::getDbTools()` have been removed.
- Methods of `DaoFileInterface2` have been moved to `DaoFileInterface`. `DaoFileInterface2` is deprecated.
  So all classes implementing `DaoFileInterface` should implement these methods.
- Deprecated method `DaoFileInterface::getCompiledFilePath()` has been removed

1.2.0-pre
---------

- Native **support of JSON fields**: dao properties having the datatype `json`
  can be automatically encoded during insert/update, or decoded during a select.
  Optionally, they can be decoded/encoded to/from a specific class.
- new feature: **possibility to indicate a base class for the generated factory class**.
  - The classname should be indicated into the `extends` attribute of `<factory>`.
  - It can be a real class name or a kind of alias that will be resolved by the context object.
  - If it is a real class name, the class can be anywhere and should be autoloadable.
  - The class must inherit from `\Jelix\Dao\AbstractDaoFactory` and it must be abstract.
- **Support of schema names** into tables names.
- **New interface `ContextInterface2`** for context that will be merged to `ContextInterface` in 
  the next major version. It allows to the compiler to have some objects of JelixDatabase 1.4+
  without the need to have a connection object.
- **New interface `CustomClassFileInterface`** that is replacing `CustomRecordClassFileInterface` (which is now deprecated)
- **New class `CustomClassFile`** that is replacing `CustomRecordClassFile` (which is now deprecated)
- Fix deprecation warning with PHP 8.4
- Fix generator: better generated joins
- Introduce compatibility with applications that are using jDao API of Jelix 1.8 
  and lower: classes of JelixDao inherit from some empty classes or empty interfaces
  having the name of old implementation, so objects can be passed to functions that
  have parameters typed with these classes (`jDaoConditions`, `jDaoFactoryBase`, 
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


