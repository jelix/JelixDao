<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2017-2023 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao;

use Jelix\Dao\Generator\Compiler;
use Jelix\Database\Schema\Column;
use Jelix\Database\Schema\Reference;
use Jelix\Database\Schema\TableInterface;

/**
 * It allows to create tables corresponding to a dao file.
 */
class DbMapper
{
    /**
     * @var ContextInterface
     */
    protected $context;

    protected $profile;

    /**
     *
     */
    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Create a table from a Dao file.
     *
     * @param DaoFileInterface $daoFile    the selector of the DAO file
     *
     * @return TableInterface
     */
    public function createTableFromDao(DaoFileInterface $daoFile)
    {
        $parser = $this->getParser($daoFile);

        $schema = $this->context->getConnector()->schema();

        $tables = $parser->getTables();
        $properties = $parser->getProperties();
        $tableInfo = $tables[$parser->getPrimaryTable()];

        // create the columns and the table
        $columns = array();
        foreach ($tableInfo->fields as $propertyName) {
            $property = $properties[$propertyName];
            $columns[] = $this->createColumnFromProperty($property);
        }

        $fullTableName = $tableInfo->getFullName();

        $table = $schema->createTable($fullTableName, $columns, $tableInfo->primaryKey);
        if (!$table) {
            $table = $schema->getTable($fullTableName);
            foreach ($columns as $column) {
                $table->alterColumn($column);
            }
        }

        // create foreign keys
        foreach ($tables as $tableName => $info) {
            if ($tableName == $tableInfo->realName) {
                continue;
            }
            if (count($info->foreignKeys)) {
                $ref = new Reference('', $info->foreignKeys, $info->realName, $info->primaryKey, $tableInfo->schema, $info->schema);
                $table->addReference($ref);
            }
        }

        return $table;
    }

    /**
     * @param DaoFileInterface $daoFile the dao for which we want to insert data
     * @param string[]  $properties  list of properties for which data are given
     * @param mixed[][] $data        the data. each row is an array of values.
     *                               Values are in the same order as $properties
     * @param int       $option      one of \Jelix\Database\Schema\SqlToolsInterface::IBD_* const
     *
     * @return int number of records inserted/updated
     */
    public function insertDaoData(DaoFileInterface $daoFile, $properties, $data, $option)
    {
        $parser = $this->getParser($daoFile);
        $tools = $this->context->getDbTools();
        $allProperties = $parser->getProperties();
        $tables = $parser->getTables();
        $table =  $tables[$parser->getPrimaryTable()];
        $columns = array();
        $primaryKey = array();
        foreach ($properties as $name) {
            if (!isset($allProperties[$name])) {
                throw new Exception("insertDaoData: Unknown property {$name}");
            }
            $columns[] = $allProperties[$name]->fieldName;
            if ($allProperties[$name]->isPK) {
                $primaryKey[] = $allProperties[$name]->fieldName;
            }
        }
        if (count($primaryKey) == 0) {
            $primaryKey = null;
        }

        return $tools->insertBulkData(
            $table->getFullName(),
            $columns,
            $data,
            $primaryKey,
            $option
        );
    }

    /**
     * @param DaoFileInterface $daoFile
     * @return Parser\XMLDaoParser
     * @throws \Exception
     */
    protected function getParser(DaoFileInterface $daoFile)
    {
        $compiler = new Compiler();

        $parser = $compiler->parse($daoFile, $this->context);
        return $parser;
    }

    protected function createColumnFromProperty(Parser\DaoProperty $property)
    {
        if ($property->autoIncrement) {
            // it should match properties as readed by Db Schema
            $hasDefault = true;
            $default = '';
            $notNull = true;
        } else {
            $hasDefault = $property->defaultValue !== null || !$property->required;
            $default = $hasDefault ? $property->defaultValue : null;
            $notNull = $property->required;
        }

        $column = new Column(
            $property->fieldName,
            $property->datatype,
            0,
            $hasDefault,
            $default,
            $notNull
        );
        $column->autoIncrement = $property->autoIncrement;
        $column->sequence = $property->sequenceName ?: false;
        if ($property->maxlength !== null) {
            $column->maxLength = $column->length = $property->maxlength;
        }
        if ($property->minlength !== null) {
            $column->minLength = $property->minlength;
        }

        return $column;
    }
}
