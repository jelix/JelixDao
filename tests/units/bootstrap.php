<?php
error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');
require(__DIR__.'/assertComplexTrait.php');
require(__DIR__.'/UnitTestCaseDb.php');
require(__DIR__.'/lib/DaoFileForTest.php');
require(__DIR__.'/lib/RecordClassForTest.php');
require(__DIR__.'/lib/ContextForTest.php');
require(__DIR__.'/lib/HookForTest.php');
require(__DIR__.'/lib/CustomPostBlogFactory.php');
require(__DIR__.'/lib/MyJsonObject.php');
require(__DIR__.'/lib/MyJsonSerializer.php');
require(__DIR__.'/DaoParserTest.php');

\Jelix\FileUtilities\Directory::removeExcept(__DIR__.'/tmp/', array('.dummy'), false);
