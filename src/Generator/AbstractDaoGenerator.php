<?php
/**
 * @author     Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau
 * @contributor Bastien Jaillot (bug fix)
 * @contributor Julien Issler, Guillaume Dugas
 * @contributor Philippe Villiers
 *
 * @copyright  2001-2005 CopixTeam, 2005-2025 Laurent Jouanneau
 * @copyright  2007-2008 Julien Issler
 *
 * @see        https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Generator;

use Jelix\Dao\DaoCondition;
use Jelix\Dao\DaoConditions;
use Jelix\Dao\Parser\DaoMethod;
use Jelix\Dao\Parser\DaoProperty;
use Jelix\Dao\Parser\DaoTable;
use Jelix\Dao\Parser\XMLDaoParser;
use Jelix\Database\Schema\SqlToolsInterface;

/**
 * This is a generator which creates php class from dao xml file.
 *
 * It is called by Compiler
 *
 * @see Compiler
 */
class AbstractDaoGenerator implements DaoGeneratorInterface
{
    /**
     * the dao definition.
     *
     * @var XMLDaoParser
     */
    protected $_dataParser;

    protected $propertiesListForInsert = 'PrimaryTable';

    protected $aliasWord = ' AS ';

    /**
     * @var SqlToolsInterface
     */
    protected $tools;

    /**
     * the real name of the main table.
     */
    protected $tableRealName = '';

    /**
     * the real name of the main table, escaped in SQL
     * so it is ready to include into a SQL query.
     */
    protected $tableRealNameEsc = '';

    protected $sqlWhereClause = '';

    protected $sqlFromClause = '';

    protected $sqlSelectClause = '';

    /**
     * constructor.
     *
     * @param SqlToolsInterface     $tools
     * @param XMLDaoParser   $daoParser
     */
    public function __construct(
        SqlToolsInterface $tools,
        XMLDaoParser $daoParser
    )
    {
        $this->_dataParser = $daoParser;
        $this->tools = $tools;
    }

    /**
     * build all classes.
     */
    public function buildClasses()
    {
        $src = array();

        // prepare some values to generate properties and methods

        $this->buildFromWhereClause();
        $this->sqlSelectClause = $this->buildSelectClause();

        $tables = $this->_dataParser->getTables();
        $pkFields = $this->_getPrimaryFieldsList();
        $primaryTable = $tables[$this->_dataParser->getPrimaryTable()];
        $this->tableRealName = $primaryTable->realName;
        $this->tableRealNameEsc = $primaryTable->escapedNameForPhp;

        $sqlPkCondition = $this->buildSimpleConditions($pkFields);
        if ($sqlPkCondition != '') {
            $sqlPkCondition = ($this->sqlWhereClause != '' ? ' AND ' : ' WHERE ').$sqlPkCondition;
        }

        $daoFile = $this->_dataParser->getDaoFile();
        $daoFactoryClass = $daoFile->getCompiledFactoryClass();
        $daoRecordClass = $daoFile->getCompiledRecordClass();

        //-----------------------
        // Build the record class
        //-----------------------
        $customRecord = $this->_dataParser->getCustomRecord();
        if ($customRecord) {
            $customRecordPath = $customRecord->getPath();
            // if the path is empty, it means the class can be autoloaded
            if ($customRecordPath) {
                $src[] = ' require_once (\''.$customRecordPath.'\');';
            }
            $extendedObject = $customRecord->getClassName();
        } else {
            // @deprecated it should be AbstractDaoRecord in futur next major release
            $extendedObject = '\Jelix\Dao\AbstractDaoRecord';
        }

        $src[] = "\nclass ".$daoRecordClass.' extends '.$extendedObject.' {';

        $properties = array();

        foreach ($this->_dataParser->getProperties() as $id => $field) {
            $properties[$id] = get_object_vars($field);
            if ($field->defaultValue !== null) {
                $src[] = ' public $'.$id.'='.var_export($field->defaultValue, true).';';
            } else {
                $src[] = ' public $'.$id.';';
            }
        }

        $src[] = '   public function getDaoName() { return "'.$daoFile->getName().'"; }';

        $src[] = '   public function getProperties() { return '.$daoFactoryClass.'::$_properties; }';
        $src[] = '   public function getPrimaryKeyNames() { return '.$daoFactoryClass.'::$_pkFields; }';
        $src[] = '}';

        //----------------------------
        // Build the dao factory class
        //----------------------------

        $serializedTables = array();
        foreach($tables as $name =>$table) {
            $serializedTables[$name] = array(
                'name' => $table->name,
                'schema' => $table->schema,
                'realname' => $table->realName,
                'pk' => $table->primaryKey,
                'fk' => $table->foreignKeys,
                'fields' => $table->fields,
                'usageType' => $table->usageType,
            );
        }

        $src[] = "\nclass ".$daoFactoryClass.' extends '.$this->_dataParser->getParentFactoryClass().' {';
        $src[] = '   protected $_tables = '.var_export($serializedTables, true).';';
        $src[] = '   protected $_primaryTable = \''.$this->_dataParser->getPrimaryTable().'\';';
        $src[] = '   protected $_selectClause=\''.$this->sqlSelectClause.'\';';
        $src[] = '   protected $_fromClause;';
        $src[] = '   protected $_whereClause=\''.$this->sqlWhereClause.'\';';
        $src[] = '   protected $_DaoRecordClassName=\''.$daoRecordClass.'\';';
        $src[] = '   protected $_daoName = \''.$daoFile->getName().'\';';

        if ($this->tools->trueValue != '1') {
            $src[] = '   protected $trueValue ='.var_export($this->tools->trueValue, true).';';
            $src[] = '   protected $falseValue ='.var_export($this->tools->falseValue, true).';';
        }

        if ($this->_dataParser->hasEvent('deletebefore') || $this->_dataParser->hasEvent('delete')) {
            $src[] = '   protected $_deleteBeforeEvent = true;';
        }
        if ($this->_dataParser->hasEvent('deleteafter') || $this->_dataParser->hasEvent('delete')) {
            $src[] = '   protected $_deleteAfterEvent = true;';
        }
        if ($this->_dataParser->hasEvent('deletebybefore') || $this->_dataParser->hasEvent('deleteby')) {
            $src[] = '   protected $_deleteByBeforeEvent = true;';
        }
        if ($this->_dataParser->hasEvent('deletebyafter') || $this->_dataParser->hasEvent('deleteby')) {
            $src[] = '   protected $_deleteByAfterEvent = true;';
        }

        $src[] = '   public static $_properties = '.var_export($properties, true).';';
        $src[] = '   public static $_pkFields = array('.$this->_writeFieldNamesWith($start = '\'', $end = '\'', $beetween = ',', $pkFields).');';

        $src[] = ' ';
        $src[] = 'public function __construct($conn){';
        $src[] = '   parent::__construct($conn);';
        $src[] = '   $this->_fromClause = \''.$this->sqlFromClause.'\';';
        $src[] = '   $this->_deleteFromClause = \''.$this->tableRealNameEsc.'\';';
        $src[] = '}';

        $src[] = ' ';
        $src[] = ' protected function _getPkWhereClauseForSelect($pk){';
        $src[] = '   extract($pk);';
        $src[] = ' return \''.$sqlPkCondition.'\';';
        $src[] = '}';

        $src[] = ' ';
        $src[] = 'protected function _getPkWhereClauseForNonSelect($pk){';
        $src[] = '   extract($pk);';
        $src[] = '   return \' where '.$this->buildSimpleConditions($pkFields, '', false).'\';';
        $src[] = '}';

        $src[] = $this->buildFinishResultSet();

        //----- Insert method

        $src[] = $this->buildInsertMethod($pkFields);

        //-----  update method

        $src[] = $this->buildUpdateMethod($pkFields);

        //----- other user methods

        $src[] = $this->buildUserMethods();

        $src[] = $this->buildEndOfClass();

        $src[] = '}'; //end of class

        return implode("\n", $src);
    }

    /**
     * build the insert() method in the final class.
     *
     * @param mixed $pkFields
     *
     * @return string the source of the method
     */
    protected function buildInsertMethod($pkFields)
    {
        $pkai = $this->getAutoIncrementPKField();
        $src = array();
        $src[] = 'public function insert ($record){';

        if ($pkai !== null) {
            // if there is an autoincrement field as primary key

            // if a value is given for the autoincrement field, then with do a full insert
            $src[] = ' if($record->'.$pkai->name.' > 0 ){';
            $src[] = '    $query = \'INSERT INTO '.$this->tableRealNameEsc.' (';
            $fields = $this->_getPropertiesBy('PrimaryTable');
            list($fields, $values) = $this->_prepareValues($fields, 'insertPattern', 'record->');

            $src[] = implode(',', $fields);
            $src[] = ') VALUES (';
            $src[] = implode(', ', $values);
            $src[] = ")';";

            $src[] = '}else{';

            $fields = $this->_getPropertiesBy($this->propertiesListForInsert);
        } else {
            $fields = $this->_getPropertiesBy('PrimaryTable');
        }

        if ($this->_dataParser->hasEvent('insertbefore') || $this->_dataParser->hasEvent('insert')) {
            $src[] = '   if ($this->hook) {';
            $src[] = '      $this->hook->onInsert($this->_daoName, $record, \Jelix\Dao\DaoHookInterface::EVENT_BEFORE);';
            $src[] = '   }';
        }

        // if there isn't an autoincrement as primary key, then we do a full insert.
        // if there isn't a value for the autoincrement field and if this is a mysql/sqlserver and pgsql,
        // we do an insert without given primary key. In other case, we do a full insert.

        $src[] = '    $query = \'INSERT INTO '.$this->tableRealNameEsc.' (';

        list($fields, $values) = $this->_prepareValues($fields, 'insertPattern', 'record->');

        $src[] = implode(',', $fields);
        $src[] = ') VALUES (';
        $src[] = implode(', ', $values);
        $src[] = ")';";

        if ($pkai !== null) {
            $src[] = '}';
        }

        $src[] = '   $result = $this->_conn->exec ($query);';

        if ($pkai !== null) {
            $src[] = '   if(!$result)';
            $src[] = '       return false;';

            $src[] = '   if($record->'.$pkai->name.' < 1 ) ';
            $src[] = $this->buildUpdateAutoIncrementPK($pkai);
        }

        // we generate a SELECT query to update field on the record object, which are autoincrement or calculated
        $fields = $this->_getPropertiesBy('FieldToUpdate');
        if (count($fields)) {
            $result = array();
            foreach ($fields as $id => $prop) {
                $result[] = $this->buildSelectPattern($prop->selectPattern, '', $prop->fieldName, $prop->name);
            }

            $sql = 'SELECT '.(implode(', ', $result)).' FROM '.$this->tableRealNameEsc.' WHERE ';
            $sql .= $this->buildSimpleConditions($pkFields, 'record->', false);

            $src[] = '  $query =\''.$sql.'\';';
            $src[] = '  $rs  =  $this->_conn->query ($query);';
            $src[] = '  $newrecord =  $rs->fetch ();';
            foreach ($fields as $id => $prop) {
                $src[] = '  $record->'.$prop->name.' = $newrecord->'.$prop->name.';';
            }
        }

        if ($this->_dataParser->hasEvent('insertafter') || $this->_dataParser->hasEvent('insert')) {
            $src[] = '   if ($this->hook) {';
            $src[] = '      $this->hook->onInsert($this->_daoName, $record, \Jelix\Dao\DaoHookInterface::EVENT_AFTER);';
            $src[] = '   }';
        }

        $src[] = '    return $result;';
        $src[] = '}';

        return implode("\n", $src);
    }

    /**
     * build the update() method for the final class.
     *
     * @param mixed $pkFields
     *
     * @return string the source of the method
     */
    protected function buildUpdateMethod($pkFields)
    {
        $src = array();

        $src[] = 'public function update ($record){';
        list($fields, $values) = $this->_prepareValues($this->_getPropertiesBy('PrimaryFieldsExcludePk'), 'updatePattern', 'record->');

        if (count($fields)) {
            if ($this->_dataParser->hasEvent('updatebefore') || $this->_dataParser->hasEvent('update')) {
                $src[] = '   if ($this->hook) {';
                $src[] = '      $this->hook->onUpdate($this->_daoName, $record, \Jelix\Dao\DaoHookInterface::EVENT_BEFORE);';
                $src[] = '   }';
            }

            $src[] = '   $query = \'UPDATE '.$this->tableRealNameEsc.' SET ';
            $sqlSet = '';
            foreach ($fields as $k => $fname) {
                $sqlSet .= ', '.$fname.'= '.$values[$k];
            }
            $src[] = substr($sqlSet, 1);

            $sqlCondition = $this->buildSimpleConditions($pkFields, 'record->', false);
            if ($sqlCondition != '') {
                $src[] = ' where '.$sqlCondition;
            }

            $src[] = "';";

            $src[] = '   $result = $this->_conn->exec ($query);';

            // we generate a SELECT query to update field on the record object, which are autoincrement or calculated
            $fields = $this->_getPropertiesBy('FieldToUpdateOnUpdate');
            if (count($fields)) {
                $result = array();
                foreach ($fields as $id => $prop) {
                    $result[] = $this->buildSelectPattern($prop->selectPattern, '', $prop->fieldName, $prop->name);
                }

                $sql = 'SELECT '.(implode(', ', $result)).' FROM '.$this->tableRealNameEsc.' WHERE ';
                $sql .= $this->buildSimpleConditions($pkFields, 'record->', false);

                $src[] = '  $query =\''.$sql.'\';';
                $src[] = '  $rs  =  $this->_conn->query ($query, jDbConnection::FETCH_INTO, $record);';
                $src[] = '  $record =  $rs->fetch ();';
            }

            if ($this->_dataParser->hasEvent('updateafter') || $this->_dataParser->hasEvent('update')) {
                $src[] = '   if ($this->hook) {';
                $src[] = '      $this->hook->onUpdate($this->_daoName, $record, \Jelix\Dao\DaoHookInterface::EVENT_AFTER);';
                $src[] = '   }';
            }

            $src[] = '   return $result;';
        } else {
            //the dao is mapped on a table which contains only primary key : update is impossible
            // so we will generate an error on update
            $daoFile = $this->_dataParser->getDaoFile();
            $src[] = "     throw new \\Jelix\\Dao\\Generator\\Exception('(502)Update is impossible with this dao because table contains only primary keys (DAO: ".$daoFile->getName().", file: ".$daoFile->getPath().")');";
        }

        $src[] = ' }'; //ends the update function
        return implode("\n", $src);
    }

    /**
     * build all methods defined by the developer in the dao file.
     *
     * @return string the source of the methods
     */
    protected function buildUserMethods()
    {
        $allField = $this->_getPropertiesBy('All');
        $primaryFields = $this->_getPropertiesBy('PrimaryTable');
        $src = array();

        foreach ($this->_dataParser->getMethods() as $name => $method) {
            $defval = $method->getParametersDefaultValues();
            if (count($defval)) {
                $mparam = '';
                foreach ($method->getParameters() as $param) {
                    $mparam .= ', $'.$param;
                    if (isset($defval[$param])) {
                        $mparam .= '=\''.str_replace("'", "\\'", $defval[$param]).'\'';
                    }
                }
                $mparam = substr($mparam, 1);
            } else {
                $mparam = implode(', $', $method->getParameters());
                if ($mparam != '') {
                    $mparam = '$'.$mparam;
                }
            }

            $src[] = ' function '.$method->name.' ('.$mparam.'){';

            $limit = '';

            switch ($method->type) {
                case 'delete':
                    $this->buildDeleteUserQuery($method, $src, $primaryFields);

                    break;
                case 'update':
                    $this->buildUpdateUserQuery($method, $src, $primaryFields);

                    break;
                case 'php':
                    $src[] = $method->getBody();
                    $src[] = '}';

                    break;

                case 'count':
                    $this->buildCountUserQuery($method, $src, $allField);

                    break;
                case 'selectfirst':
                case 'select':
                default:
                    $limit = $this->buildSelectUserQuery($method, $src, $allField);
            }

            if ($method->type == 'php') {
                continue;
            }

            switch ($method->type) {
                case 'delete':
                case 'update':
                    if ($method->eventBeforeEnabled || $method->eventAfterEnabled) {
                        $src[] = '   $args = func_get_args();';
                        if ($method->eventBeforeEnabled) {
                            $src[] = '   if ($this->hook) {';
                            $src[] = '      $this->hook->onCustomMethod($this->_daoName, \''.$method->name.'\', \''.$method->type.'\', $args, \Jelix\Dao\DaoHookInterface::EVENT_BEFORE);';
                            $src[] = '   }';
                        }
                        if ($method->eventAfterEnabled) {
                            $src[] = '   $result = $this->_conn->exec ($__query);';

                            $src[] = '   if ($this->hook) {';
                            $src[] = '      $this->hook->onCustomMethod($this->_daoName, \''.$method->name.'\', \''.$method->type.'\', $args, \Jelix\Dao\DaoHookInterface::EVENT_AFTER);';
                            $src[] = '   }';

                            $src[] = '   return $result;';
                        } else {
                            $src[] = '    return $this->_conn->exec ($__query);';
                        }
                    } else {
                        $src[] = '    return $this->_conn->exec ($__query);';
                    }

                    break;
                case 'count':
                    $src[] = '    $__rs = $this->_conn->query($__query);';
                    $src[] = '    $__res = $__rs->fetch();';
                    $src[] = '    return intval($__res->c);';

                    break;
                case 'selectfirst':
                    $src[] = '    $__rs = $this->_conn->limitQuery($__query,0,1);';
                    $src[] = '    $this->finishInitResultSet($__rs);';
                    $src[] = '    return $__rs->fetch();';

                    break;
                case 'select':
                default:
                    if ($limit) {
                        $src[] = '    $__rs = $this->_conn->limitQuery($__query'.$limit.');';
                    } else {
                        $src[] = '    $__rs = $this->_conn->query($__query);';
                    }
                    $src[] = '    $this->finishInitResultSet($__rs);';
                    $src[] = '    return $__rs;';
            }
            $src[] = '}';
        }

        return implode("\n", $src);
    }

    /**
     * @param DaoMethod $method
     * @param string[] $src
     * @param DaoProperty[] $primaryFields
     */
    protected function buildDeleteUserQuery($method, &$src, &$primaryFields)
    {
        $src[] = '    $__query = \'DELETE FROM '.$this->tableRealNameEsc.' \';';
        $cond = $method->getConditions();
        if ($cond !== null) {
            $sqlCond = $this->buildConditions($cond, $primaryFields, $method->getParameters(), false);
            if (trim($sqlCond) != '') {
                $src[] = '$__query .=\' WHERE '.$sqlCond."';";
            }
        }
    }

    /**
     * @param DaoMethod $method
     * @param string[] $src
     * @param DaoProperty[] $primaryFields
     */
    protected function buildUpdateUserQuery($method, &$src, &$primaryFields)
    {
        $src[] = '    $__query = \'UPDATE '.$this->tableRealNameEsc.' SET ';
        $updatefields = $this->_getPropertiesBy('PrimaryFieldsExcludePk');
        $sqlSet = '';

        foreach ($method->getValues() as $propname => $value) {
            if ($value[1]) {
                // value is an expression

                $expression = $this->parseSQLFunction($value[0]);

                // replace all variable name by a php expression
                preg_match_all('/\$([a-zA-Z0-9_]+)/', $expression, $varMatches, PREG_OFFSET_CAPTURE);
                $parameters = $method->getParameters();
                if (count($varMatches[0])) {
                    $result = '';
                    $len = 0;
                    foreach ($varMatches[1] as $k => $var) {
                        $result .= substr($expression, $len, $len + $varMatches[0][$k][1]);
                        $len += $varMatches[0][$k][1] + strlen($varMatches[0][$k][0]);
                        if (in_array($var[0], $parameters)) {
                            $result .= '\'.'.$this->_preparePHPExpr($varMatches[0][$k][0], $updatefields[$propname], true).'.\'';
                        } else {
                            $result .= $varMatches[0][$k][0];
                        }
                    }
                    if ($len < strlen($expression)) {
                        $result .= substr($expression, $len);
                    }
                    $expression = $result;
                }
                $sqlSet .= ', '.$this->_encloseName($updatefields[$propname]->fieldName).'= '.$expression;
            } else {
                // value is a simple value
                $sqlSet .= ', '.$this->_encloseName($updatefields[$propname]->fieldName).'= '.
                    $this->tools->escapeValue($updatefields[$propname]->unifiedType, $value[0], false, true);
            }
        }
        $src[] = substr($sqlSet, 1).'\';';
        $cond = $method->getConditions();
        if ($cond !== null) {
            $sqlCond = $this->buildConditions($cond, $primaryFields, $method->getParameters(), false);
            if (trim($sqlCond) != '') {
                $src[] = '$__query .=\' WHERE '.$sqlCond."';";
            }
        }
    }

    /**
     * @param DaoMethod $method
     * @param string[] $src
     * @param DaoProperty[] $primaryFields
     */
    protected function buildCountUserQuery($method, &$src, &$allField)
    {
        if ($method->distinct != '') {
            $properties = $this->_dataParser->getProperties();
            $tables = $this->_dataParser->getTables();
            $prop = $properties[$method->distinct];
            $count = ' DISTINCT '.$tables[$prop->table]->enclosedName.'.'.$this->_encloseName($prop->fieldName);
        } else {
            $count = '*';
        }
        $src[] = '    $__query = \'SELECT COUNT('.$count.') as c \'.$this->_fromClause.$this->_whereClause;';
        $glueCondition = ($this->sqlWhereClause != '' ? ' AND ' : ' WHERE ');

        $cond = $method->getConditions();
        if ($cond !== null) {
            $sqlCond = $this->buildConditions($cond, $allField, $method->getParameters(), true);
            if (trim($sqlCond) != '') {
                $src[] = '$__query .=\''.$glueCondition.$sqlCond."';";
            }
        }
    }

    /**
     * @param DaoMethod $method
     * @param string[] $src
     * @param DaoProperty[] $primaryFields
     */
    protected function buildSelectUserQuery($method, &$src, &$allField)
    {
        $limit = '';
        if ($method->distinct != '') {
            $select = '\''.$this->buildSelectClause($method->distinct).'\'';
        } else {
            $select = ' $this->_selectClause';
        }
        $src[] = '    $__query = '.$select.'.$this->_fromClause.$this->_whereClause;';
        $glueCondition = ($this->sqlWhereClause != '' ? ' AND ' : ' WHERE ');
        if ($method->type == 'select' && ($lim = $method->getLimit()) !== null) {
            $limit = ', '.$lim['offset'].', '.$lim['count'];
        }

        $sqlCond = $this->buildConditions($method->getConditions(), $allField, $method->getParameters(), true);

        if (trim($sqlCond) != '') {
            $src[] = '$__query .=\''.$glueCondition.$sqlCond."';";
        }

        return $limit;
    }

    /**
     * create FROM clause and WHERE clause for all SELECT query.
     */
    protected function buildFromWhereClause()
    {
        $tables = $this->_dataParser->getTables();

        foreach ($tables as $table) {
            $this->escapeTableNameForPHP($table);
        }

        $primarytable = $tables[$this->_dataParser->getPrimaryTable()];

        list($sqlFrom, $sqlWhere) = $this->buildOuterJoins($tables, $primarytable->enclosedName);

        $sqlFrom = $primarytable->escapedNameForPhpForFrom.$sqlFrom;

        foreach ($this->_dataParser->getInnerJoins() as $tablejoin) {
            /** @var DaoTable $table */
            $table = $tables[$tablejoin];

            $sqlFrom .= ' INNER JOIN '.$table->escapedNameForPhpForFrom. ' ON (';
            $innerJoin = [];
            foreach ($table->foreignKeys as $k => $fk) {
                $innerJoin[] = $primarytable->enclosedName.'.'.$this->_encloseName($fk).'='.
                    $table->enclosedName.'.'.$this->_encloseName($table->primaryKey[$k]);
            }

            $sqlFrom .= implode(' AND ', $innerJoin).')';
        }

        $this->sqlWhereClause = $sqlWhere;
        $this->sqlFromClause = ' FROM '.$sqlFrom;
    }

    /**
     * generates the part of the FROM clause for outer joins.
     *
     * @param DaoTable[] $tables
     * @param mixed $primaryTableName
     *
     * @return array [0]=> the part of the FROM clause, [1]=> the part to add to the WHERE clause when needed
     */
    protected function buildOuterJoins(&$tables, $primaryTableName)
    {
        $sqlFrom = '';
        foreach ($this->_dataParser->getOuterJoins() as $tablejoin) {
            $table = $tables[$tablejoin[0]];

            $r = $table->escapedNameForPhpForFrom;

            $fieldjoin = '';
            foreach ($table->foreignKeys as $k => $fk) {
                $fieldjoin .= ' AND '.$primaryTableName.'.'.$this->_encloseName($fk).
                    '='.$table->enclosedName.'.'.$this->_encloseName($table->primaryKey[$k]);
            }
            $fieldjoin = substr($fieldjoin, 4);

            if ($tablejoin[1] == 0) {
                $sqlFrom .= ' LEFT JOIN '.$r.' ON ('.$fieldjoin.')';
            } elseif ($tablejoin[1] == 1) {
                $sqlFrom .= ' RIGHT JOIN '.$r.' ON ('.$fieldjoin.')';
            }
        }

        return array($sqlFrom, '');
    }

    /**
     * build a SELECT clause for all SELECT queries.
     *
     * @param mixed $distinct
     *
     * @return string the select clause
     */
    protected function buildSelectClause($distinct = false)
    {
        $result = array();

        $tables = $this->_dataParser->getTables();
        foreach ($this->_dataParser->getProperties() as $id => $prop) {
            $table = $tables[$prop->table]->enclosedName.'.';

            if ($prop->selectPattern != '') {
                $result[] = $this->buildSelectPattern($prop->selectPattern, $table, $prop->fieldName, $prop->name);
            }
        }

        return 'SELECT '.($distinct ? 'DISTINCT ' : '').(implode(', ', $result));
    }

    /**
     * build an item for the select clause.
     *
     * @param mixed $pattern
     * @param string $table
     * @param mixed $fieldname
     * @param mixed $propname
     */
    protected function buildSelectPattern($pattern, $table, $fieldname, $propname)
    {
        if ($pattern == '%s') {
            $field = $table.$this->_encloseName($fieldname);
            if ($fieldname != $propname) {
                $field .= ' as '.$this->_encloseName($propname);
            }
        } else {
            $expression = $this->parseSQLFunction($pattern);
            $field = str_replace(
                array("'", '%s'),
                array("\\'", $table.$this->_encloseName($fieldname)),
                $expression
            )
                .' as '.$this->_encloseName($propname);
        }

        return $field;
    }

    protected function buildFinishResultSet()
    {
        $jsonFields = $this->_getPropertiesBy('JsonField');
        $src = [];
        if ($jsonFields) {
            $src[] = 'protected function finishInitResultSet($rs) {';
            $src[] = '   parent::finishInitResultSet($rs);';
            $src[] = '   $rs->addModifier(function ($record, $rs) {';

            foreach ($jsonFields as $field) {
                $src[] = '    if ($record->'.$field->name.' !== null) { $record->'.$field->name.' = json_decode($record->'.$field->name.', true); }';
            }

            $src[] = '   });';
            $src[] = '}';
        }

        return implode("\n", $src);
    }

    protected function buildEndOfClass()
    {
        return '';
    }

    /**
     * format field names with a start, an end and a between strings.
     *
     * ex: give 'name' as $info, it will output the result of $field->name
     *
     * @param string         $info     property to get from objects in $using
     * @param string         $start    string to add before the info
     * @param string         $end      string to add after the info
     * @param string         $beetween string to add between each info
     * @param DaoProperty[] $using    list of DaoProperty object. if null, get default fields list
     *
     * @see  DaoProperty
     *
     * @return string list of field names separated by the $between character
     */
    protected function _writeFieldsInfoWith($info, $start = '', $end = '', $beetween = '', $using = null)
    {
        $result = array();
        if ($using === null) {
            //if no fields are provided, using _dataParser's as default.
            $using = $this->_dataParser->getProperties();
        }

        foreach ($using as $id => $field) {
            $result[] = $start.$field->{$info}.$end;
        }

        return implode($beetween, $result);
    }

    /**
     * format field names with start, end and between strings.
     *
     * @param string      $start
     * @param string      $end
     * @param string      $beetween
     * @param null|mixed $using
     */
    protected function _writeFieldNamesWith($start = '', $end = '', $beetween = '', $using = null)
    {
        return $this->_writeFieldsInfoWith('name', $start, $end, $beetween, $using);
    }

    /**
     * @return DaoProperty[]
     */
    protected function _getPrimaryFieldsList()
    {
        $tables = $this->_dataParser->getTables();
        $pkFields = array();

        $primTable = $tables[$this->_dataParser->getPrimaryTable()];
        $props = $this->_dataParser->getProperties();
        // we want to have primary keys as the same order indicated into primarykey attr
        foreach ($primTable->primaryKey as $pkname) {
            foreach ($primTable->fields as $f) {
                if ($props[$f]->fieldName == $pkname) {
                    $pkFields[$props[$f]->name] = $props[$f];

                    break;
                }
            }
        }

        return $pkFields;
    }

    /**
     * gets fields that match a condition returned by the $captureMethod.
     *
     * @internal
     *
     * @param string $captureMethod
     * @return DaoProperty[]
     */
    protected function _getPropertiesBy($captureMethod)
    {
        $captureMethod = '_capture'.$captureMethod;
        $result = array();

        foreach ($this->_dataParser->getProperties() as $field) {
            if ($this->{$captureMethod}($field)) {
                $result[$field->name] = $field;
            }
        }

        return $result;
    }

    /**
     * @param DaoProperty $field
     *
     * @return bool
     */
    protected function _capturePrimaryFieldsExcludeAutoIncrement($field)
    {
        return $field->table == $this->_dataParser->getPrimaryTable() && !$field->autoIncrement;
    }

    /**
     * @param DaoProperty $field
     *
     * @return bool
     */
    protected function _capturePrimaryFieldsExcludePk($field)
    {
        return ($field->table == $this->_dataParser->getPrimaryTable()) && !$field->isPK;
    }

    /**
     * @param DaoProperty $field
     *
     * @return bool
     */
    protected function _capturePrimaryTable($field)
    {
        return $field->table == $this->_dataParser->getPrimaryTable();
    }

    /**
     * @param DaoProperty $field
     *
     * @return bool
     */
    protected function _captureAll($field)
    {
        return true;
    }

    /**
     * @param DaoProperty $field
     *
     * @return bool
     */
    protected function _captureFieldToUpdate($field)
    {
        return $field->table == $this->_dataParser->getPrimaryTable()
                && !$field->isPK
                && !$field->isFK
                && ($field->autoIncrement || ($field->insertPattern != '%s' && $field->selectPattern != ''));
    }

    /**
     * @param DaoProperty $field
     *
     * @return bool
     */
    protected function _captureFieldToUpdateOnUpdate($field)
    {
        return $field->table == $this->_dataParser->getPrimaryTable()
                && !$field->isPK
                && !$field->isFK
                && ($field->autoIncrement || ($field->updatePattern != '%s' && $field->selectPattern != ''));
    }

    /**
     * @param DaoProperty $field
     *
     * @return bool
     */
    protected function _captureBinaryField($field)
    {
        return $field->unifiedType == 'binary' || $field->unifiedType == 'varbinary';
    }

    protected function _captureJsonField(&$field)
    {
        return $field->unifiedType == 'json';
    }

    /**
     * get autoincrement PK field.
     *
     * @param null|DaoProperty[] $using
     */
    protected function getAutoIncrementPKField($using = null)
    {
        if ($using === null) {
            $using = $this->_dataParser->getProperties();
        }

        foreach ($using as $id => $field) {
            if (!$field->isPK) {
                continue;
            }
            if ($field->autoIncrement) {
                return $field;
            }
        }

        return null;
    }

    /**
     * build a WHERE clause with conditions on given properties : conditions are
     * equality between a variable and the field.
     * the variable name is the name of the property, made with an optional prefix
     * given in $fieldPrefix parameter.
     * This method is called to generate WHERE clause for primary keys.
     *
     * @param DaoProperty[] $fields
     * @param string         $fieldPrefix an optional prefix to prefix variable names
     * @param bool           $forSelect   if true, the table name or table alias will prefix
     *                                    the field name in the query
     *
     * @return string the WHERE clause (without the WHERE keyword)
     *
     * @internal
     */
    protected function buildSimpleConditions(&$fields, $fieldPrefix = '', $forSelect = true)
    {
        $r = ' ';

        $first = true;
        foreach ($fields as $field) {
            if (!$first) {
                $r .= ' AND ';
            } else {
                $first = false;
            }

            if ($forSelect) {
                $condition = $this->_encloseName($field->table).'.'.$this->_encloseName($field->fieldName);
            } else {
                $condition = $this->_encloseName($field->fieldName);
            }

            $var = '$'.$fieldPrefix.$field->name;
            $value = $this->_preparePHPExpr($var, $field, !$field->requiredInConditions, '=');

            $r .= $condition.'\'.'.$value.'.\'';
        }

        return $r;
    }

    /**
     * @param DaoProperty[] $fieldList
     * @param  string  $pattern
     * @param  string  $prefixfield
     *
     * @return array[]
     */
    protected function _prepareValues($fieldList, $pattern = '', $prefixfield = '')
    {
        $values = $fields = array();

        foreach ($fieldList as $fieldName => $field) {
            if ($pattern != '' && $field->{$pattern} == '') {
                continue;
            }

            $value = $this->_preparePHPExpr('$'.$prefixfield.$fieldName, $field, true);

            if ($pattern != '') {
                $expression = $this->parseSQLFunction($field->{$pattern});
                if (strpos($expression, "'") !== false && strpos($expression, "\\'") === false) {
                    $values[$field->name] = sprintf(str_replace("'", "\\'", $expression), '\'.'.$value.'.\'');
                } else {
                    $values[$field->name] = sprintf($expression, '\'.'.$value.'.\'');
                }
            } else {
                $values[$field->name] = '\'.'.$value.'.\'';
            }

            $fields[$field->name] = $this->_encloseName($field->fieldName);
        }

        return array($fields, $values);
    }

    /**
     * build 'where' clause from conditions declared with condition tag in a user method.
     *
     * @param DaoConditions $cond       the condition object which contains conditions data
     * @param DaoProperty[] $fields
     * @param string[]       $params     list of parameters name of the method
     * @param bool           $withPrefix true if the field name should be preceded by the table name/table alias
     *
     * @return string a WHERE clause (without the WHERE keyword) with eventually an ORDER clause
     *
     * @internal
     */
    protected function buildConditions($cond, $fields, $params = array(), $withPrefix = true)
    {
        if ($cond) {
            $sql = $this->buildOneSQLCondition($cond->condition, $fields, $params, $withPrefix, true);
        } else {
            $sql = '';
        }

        $order = array();
        foreach ($cond->order as $name => $way) {
            if (isset($fields[$name])) {
                if ($withPrefix) {
                    $ord = $this->_encloseName($fields[$name]->table).'.'.$this->_encloseName($fields[$name]->fieldName);
                } else {
                    $ord = $this->_encloseName($fields[$name]->fieldName);
                }
            } elseif ($name[0] == '$') {
                $ord = '\'.'.$name.'.\'';
            } else {
                continue;
            }
            if ($way[0] == '$') {
                $order[] = $ord.' \'.( strtolower('.$way.') ==\'asc\'?\'asc\':\'desc\').\'';
            } else {
                $order[] = $ord.' '.$way;
            }
        }
        if (count($order) > 0) {
            if (trim($sql) == '') {
                $sql = ' 1=1 ';
            }
            $sql .= ' ORDER BY '.implode(', ', $order);
        }

        return $sql;
    }

    /**
     * build a condition for the SQL WHERE clause.
     * this method call itself recursively.
     *
     * @param DaoCondition  $cond       a condition object which contains conditions data
     * @param DaoProperty[] $fields
     * @param string[]       $params     list of parameters name of the method
     * @param bool           $withPrefix true if the field name should be preceded by the table name/table alias
     * @param bool           $principal  should be true for the first call, and false for recursive call
     * @param mixed          $condition
     *
     * @return string a WHERE clause (without the WHERE keyword)
     *
     * @see AbstractDaoGenerator::buildConditions
     *
     * @internal
     */
    protected function buildOneSQLCondition($condition, $fields, $params, $withPrefix, $principal = false)
    {
        $r = ' ';

        //direct conditions for the group
        $first = true;
        foreach ($condition->conditions as $cond) {
            if (isset($cond['dbType']) && $cond['dbType'] != '' && $cond['dbType'] != $this->tools->getConnection()->getSQLType()) {
                continue;
            }

            if (!$first) {
                $r .= ' '.$condition->glueOp.' ';
            }
            $first = false;

            $prop = $fields[$cond['field_id']];

            $pattern = (isset($cond['field_pattern']) && !empty($cond['field_pattern'])) ?
                $this->parseSQLFunction($cond['field_pattern']) :
                '%s';

            if ($withPrefix) {
                if ($pattern == '%s') {
                    $f = $this->_encloseName($prop->table).'.'.$this->_encloseName($prop->fieldName);
                } else {
                    $f = str_replace(array("'", '%s'), array("\\'", $this->_encloseName($prop->table).'.'.$this->_encloseName($prop->fieldName)), $pattern);
                }
            } else {
                if ($pattern == '%s') {
                    $f = $this->_encloseName($prop->fieldName);
                } else {
                    $f = str_replace(array("'", '%s'), array("\\'", $this->_encloseName($prop->fieldName)), $pattern);
                }
            }

            $r .= $f.' ';

            if ($cond['operator'] == 'IN' || $cond['operator'] == 'NOT IN') {
                if ($cond['isExpr']) {
                    $phpexpr = $this->_preparePHPCallbackExpr($prop);
                    $phpvalue = 'implode(\',\', array_map( '.$phpexpr.', is_array('.$cond['value'].')?'.$cond['value'].':array('.$cond['value'].')))';
                    $value = '(\'.'.$phpvalue.'.\')';
                } else {
                    $value = '('.str_replace("'", "\\'", $cond['value']).')';
                }
                $r .= $cond['operator'].' '.$value;
            } elseif ($cond['operator'] == 'IS NULL' || $cond['operator'] == 'IS NOT NULL') {
                $r .= $cond['operator'].' ';
            } else {
                if ($cond['isExpr']) {
                    // we need to know if the expression is like "$foo" (1) or a thing like "concat($foo,'bla')" (2)
                    // because of the nullability of the parameter. If the value of the parameter is null and the operator
                    // is = or <>, then we need to generate a thing like :
                    // - in case 1: ($foo === null ? 'IS NULL' : '='.$this->_conn->quote($foo))
                    // - in case 2: '= concat('.($foo === null ? 'NULL' : $this->_conn->quote($foo)).' ,\'bla\')'
                    if ($cond['value'][0] == '$') {
                        $value = str_replace("'", "\\'", $cond['value']);
                        $value = '\'.'.$this->_preparePHPExpr($value, $prop, !$prop->requiredInConditions, $cond['operator']).'.\'';
                    } else {
                        $value = $this->parseSQLFunction($cond['value']);
                        $value = str_replace("'", "\\'", $value);
                        foreach ($params as $param) {
                            $value = str_replace('$'.$param, '\'.'.$this->_preparePHPExpr('$'.$param, $prop, !$prop->requiredInConditions).'.\'', $value);
                        }
                        $value = $cond['operator'].' '.$value;
                    }
                } else {
                    $value = $cond['operator'].' ';
                    if ($cond['operator'] == 'LIKE' || $cond['operator'] == 'NOT LIKE') {
                        $value .= $this->tools->escapeValue('varchar', $cond['value'], false, true);
                    } else {
                        $value .= $this->tools->escapeValue($prop->unifiedType, $cond['value'], false, true);
                    }
                }
                $r .= $value;
            }
        }
        //sub conditions
        foreach ($condition->group as $conditionDetail) {
            if (!$first) {
                $r .= ' '.$condition->glueOp.' ';
            }
            $r .= $this->buildOneSQLCondition($conditionDetail, $fields, $params, $withPrefix);
            $first = false;
        }

        //adds parenthesis around the sql if needed (non empty)
        if (strlen(trim($r)) > 0 && (!$principal || ($principal && $condition->glueOp != 'AND'))) {
            $r = '('.$r.')';
        }

        return $r;
    }

    /**
     * @param string $expr
     * @param DaoProperty $field
     * @param bool $checknull
     * @param string $forCondition
     * @return string the PHP expression to insert into the generated class
     */
    protected function _preparePHPExpr($expr, $field, $checknull = true, $forCondition = '')
    {
        $opnull = '';
        if ($checknull && $forCondition != '') {
            if ($forCondition == '=') {
                $opnull = 'IS ';
            } elseif ($forCondition == '<>') {
                $opnull = 'IS NOT ';
            } else {
                $checknull = false;
            }
        }
        $type = '';
        if ($forCondition != 'LIKE' && $forCondition != 'NOT LIKE') {
            $type = strtolower($field->unifiedType);
        }

        if ($forCondition != '') {
            $forCondition = '\' '.$forCondition.' \'.'; // spaces for operators like LIKE
        }

        switch ($type) {
            case 'integer':
                if ($checknull) {
                    $expr = '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'intval('.$expr.'))';
                } else {
                    $expr = $forCondition.'intval('.$expr.')';
                }

                break;
            case 'double':
            case 'float':
            case 'numeric':
            case 'decimal':
                if ($checknull) {
                    $expr = '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'\\Jelix\Database\\Utilities::floatToStr('.$expr.'))';
                } else {
                    $expr = $forCondition.'\\Jelix\Database\\Utilities::floatToStr('.$expr.')';
                }

                break;
            case 'boolean':
                if ($checknull) {
                    $expr = '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'$this->_prepareValue('.$expr.', "boolean", true))';
                } else {
                    $expr = $forCondition.'$this->_prepareValue('.$expr.', "boolean", true)';
                }

                break;
            case 'json':
                if ($checknull) {
                    $expr = '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'$this->_conn->quote(is_string('.$expr.')?'.$expr.':json_encode('.$expr.')))';
                } else {
                    $expr = $forCondition.'$this->_conn->quote(is_string('.$expr.')?'.$expr.':json_encode('.$expr.'))';
                }
                break;
            default:
                if ($type == 'varbinary' || $type == 'binary') {
                    $qparam = ',true';
                } else {
                    $qparam = '';
                }

                if ($checknull) {
                    $expr = '('.$expr.' === null ? \''.$opnull.'NULL\' : '.$forCondition.'$this->_conn->quote2('.$expr.',false'.$qparam.'))';
                } else {
                    $expr = $forCondition.'$this->_conn->quote'.($qparam ? '2('.$expr.',true,true)' : '('.$expr.')');
                }
        }

        return $expr;
    }

    protected function _preparePHPCallbackExpr($field)
    {
        $type = strtolower($field->unifiedType);
        switch ($type) {
            case 'integer':
                return 'function($__e){return intval($__e);}';
            case 'double':
            case 'float':
            case 'numeric':
            case 'decimal':
                return 'function($__e){return \\Jelix\Database\\Utilities::floatToStr($__e);}';
            case 'boolean':
                return 'array($this, \'_callbackBool\')';
            case 'json':
                return 'array($this, \'_callbackJson\')';
            default:
                if ($type == 'varbinary' || $type == 'binary') {
                    return 'array($this, \'_callbackQuoteBin\')';
                }

                return 'array($this, \'_callbackQuote\')';
        }
    }


    protected function escapeTableNameForPHP(DaoTable $table)
    {
        $escName = $this->_encloseName('\'.$this->_conn->prefixTable(\''.$table->realName.'\').\'');
        if ($table->schema) {
            $escName = $this->_encloseName($table->schema).'.'.$escName;
        }
        $table->escapedNameForPhp = $escName;
        //if ($table->name != $table->realName) {
            $table->escapedNameForPhpForFrom = $table->escapedNameForPhp.$this->aliasWord.$this->_encloseName($table->name);
        //}
        //else {
        //    $table->escapedNameForPhpForFrom = $table->escapedNameForPhp;
        //}
        $table->enclosedName = $this->_encloseName($table->name);
    }

    protected function _encloseName($name)
    {
        return $this->tools->encloseName($name);
    }

    protected function buildUpdateAutoIncrementPK($pkai)
    {
        return '       $record->'.$pkai->name.'= $this->_conn->lastInsertId();';
    }

    protected function parseSQLFunction($expression)
    {
        return $this->tools->parseSQLFunctionAndConvert($expression);
    }
}
