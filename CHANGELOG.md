Changelog
=========

Next
----

- Native support of JSON fields: dao properties having the datatype `json`
  are automatically encoded during insert/update, or decoded during a select.
- Fix deprecation warning with PHP 8.4
- Fix generator: better generated joins

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

