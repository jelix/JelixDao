<?php
/**
 * @author     Laurent Jouanneau
 * @contributor Gwendal Jouannic
 * @contributor Philippe Villiers
 *
 * @copyright  2007-2023 Laurent Jouanneau, 2013 Philippe Villiers
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Generator\Adapter;

use Jelix\Dao\Generator\Exception;
use Jelix\Dao\Parser\DaoProperty;

/**
 * driver for JelixDao compiler.
 *
 */
class OciDaoGenerator extends \Jelix\Dao\Generator\AbstractDaoGenerator
{
    protected $aliasWord = ' ';
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';

    protected function buildOuterJoins(&$tables, $primaryTableName)
    {
        $sqlFrom = '';
        $sqlWhere = '';
        foreach ($this->_dataParser->getOuterJoins() as $tablejoin) {
            $table = $tables[$tablejoin[0]];
            $tablename = $this->_encloseName($table->name);

            if ($table->name != $table->realName) {
                $r = $this->_encloseName($table->realName).' '.$tablename;
            } else {
                $r = $this->_encloseName($table->realName);
            }

            $fieldjoin = '';

            if ($tablejoin[1] == 0) {
                $operand = '=';
                $opafter = '(+)';
            } elseif ($tablejoin[1] == 1) {
                $operand = '(+)=';
                $opafter = '';
            }
            foreach ($table->foreignKeys as $k => $fk) {
                $fieldjoin .= ' AND '.$primaryTableName.'.'.$this->_encloseName($fk).$operand.$tablename.'.'.$this->_encloseName($table->primaryKey[$k]).$opafter;
            }
            $sqlFrom .= ', '.$r;
            $sqlWhere .= $fieldjoin;
        }

        return array($sqlFrom, $sqlWhere);
    }

    protected function buildSelectPattern($pattern, $table, $fieldname, $propname)
    {
        if ($pattern == '%s') {
            if ($fieldname != $propname) {
                $field = $table.$this->_encloseName($fieldname).' "'.$propname.'"';
            } else {
                $field = $table.$this->_encloseName($fieldname);
            }
        } else {
            $field = str_replace(array("'", '%s'), array("\\'", $table.$this->_encloseName($fieldname)), $pattern).' "'.$propname.'"';
        }

        return $field;
    }

    // Replaces the lastInsertId which doesn't work with oci
    protected function buildUpdateAutoIncrementPK($pkai)
    {
        return '          $record->'.$pkai->name.'= $this->_conn->query(\'SELECT '.$pkai->sequenceName.'.currval as "'.$pkai->name.'" from dual\')->fetch()->'.$pkai->name.';';
    }

    /**
     * build the insert() method in the final class.
     *
     * @param DaoProperty[] $pkFields
     *
     * @throws Exception
     *
     * @return string the source of the method
     */
    protected function buildInsertMethod($pkFields)
    {
        $pkai = $this->getAutoIncrementPKField();

        // Explicitly forbid auto-incement
        if (is_object($pkai) && $pkai->autoIncrement && !$pkai->sequenceName) {
            throw new Exception('Please don\'t use auto-increment and use a sequence instead for the table '.
                                        $this->tableRealName);
        }

        $src = array();
        $src[] = 'public function insert ($record){';

        if ($this->_dataParser->hasEvent('insertbefore') || $this->_dataParser->hasEvent('insert')) {
            $src[] = '   if ($this->hook) {';
            $src[] = '      $this->hook->onInsert($this->_daoName, $record, \Jelix\Dao\DaoHookInterface::EVENT_BEFORE);';
            $src[] = '   }';
        }

        $src[] = '    $query = \'INSERT INTO '.$this->tableRealNameEsc.' (';

        $fields = $this->_getPropertiesBy('PrimaryTable');
        list($fields, $values) = $this->_prepareValues($fields, 'insertPattern', 'record->');
        $fieldsObj = $this->_getPropertiesBy('PrimaryTable');

        $binds = array();
        $returningInto = array();
        $returningBind = '';

        foreach ($fieldsObj as $k => $fieldData) {
            if (in_array($fieldData->name, $fields)) {
                // We have a blob/clob: replace the field value by an Oracle marker
                if ($fieldData->datatype == 'clob' || $fieldData->datatype == 'blob') {
                    $values[$k] = ':'.$fieldData->fieldName;
                    $binds[$fieldData->fieldName] = $fieldData->name;
                } else {
                    if ($fieldData->isPK) {
                        // Use a returning into only for the numeric primary keys
                        switch ($fieldData->datatype) {
                            case 'int':
                            case 'integer':
                            case 'tinyint':
                            case 'smallint':
                            case 'mediumint':
                            case 'bigint':
                            case 'number':
                                $returningInto['field'][] = $fieldData->fieldName;
                                $returningInto['bind'][] = ':'.$fieldData->name;
                                $returningBind .= '    $prep->bindParam(\':'.$fieldData->name.'\', $record->'.$fieldData->name.', SQLT_INT, -1);'."\n";

                            break;
                        }
                    } else {
                        switch ($fieldData->datatype) {
                            case 'varchar':
                            case 'varchar2':
                            case 'nvarchar2':
                            case 'character':
                            case 'character varying':
                            case 'char':
                            case 'nchar':
                            case 'name':
                            case 'longvarchar':
                            case 'string':
                                $values[$k] = ':'.$fieldData->fieldName;
                                $binds[$fieldData->fieldName] = $fieldData->name;
                        }
                    }
                }
            }
        }

        $src[] = implode(',', $fields);
        $src[] = ') VALUES (';
        $src[] = implode(', ', $values);
        $src[] = ')';

        if (!empty($returningInto)) {
            // We have RETURNING INTO
            $src[] = '    RETURNING '.implode(',', $returningInto['field']).' INTO '.implode(',', $returningInto['bind']);
        }

        $src[] = "';";

        // We have clob/string binds
        if (!empty($binds) || !empty($returningInto)) {
            $src[] = '   $prep = $this->_conn->prepare ($query);';
            // Bind the clobs/strings
            if (!empty($binds)) {
                foreach ($binds as $variable => $name) {
                    $src[] = '   $prep->bindParam(\':'.$variable.'\', $record->'.$name.');';
                }
            }
            // Bind the keys
            if (!empty($returningInto)) {
                $src[] = $returningBind;
            }
            $src[] = '   $result = $prep->execute();';
        } else {
            $src[] = '   $result = $this->_conn->exec ($query);';
        }

        if ($pkai !== null) {
            $src[] = '   if (!$result) {';
            $src[] = '       return false;';
            $src[] = '   }';
            $src[] = '   if ($record->'.$pkai->name.' < 1 ) {';
            $src[] = $this->buildUpdateAutoIncrementPK($pkai);
            $src[] = '   }';
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
     * @param DaoProperty[] $pkFields
     *
     * @return string the source of the method
     */
    protected function buildUpdateMethod($pkFields)
    {
        $src = array();

        $src[] = 'public function update ($record){';
        list($fields, $values) = $this->_prepareValues($this->_getPropertiesBy('PrimaryFieldsExcludePk'), 'updatePattern', 'record->');
        $fieldsObj = $this->_getPropertiesBy('PrimaryFieldsExcludePk');

        if (count($fields)) {

            if ($this->_dataParser->hasEvent('updatebefore') || $this->_dataParser->hasEvent('update')) {
                $src[] = '   if ($this->hook) {';
                $src[] = '      $this->hook->onUpdate($this->_daoName, $record, \Jelix\Dao\DaoHookInterface::EVENT_BEFORE);';
                $src[] = '   }';
            }

            $src[] = '   $query = \'UPDATE '.$this->tableRealNameEsc.' SET ';
            $sqlSet = '';

            $binds = array();

            foreach ($fieldsObj as $k => $fieldData) {
                if ($fieldData->updatePattern != '') {
                    switch ($fieldData->datatype) {
                    // We have blob/clob or string(s): replace the field value by an Oracle marker
                        case 'clob':
                        case 'blob':
                        case 'varchar':
                        case 'varchar2':
                        case 'nvarchar2':
                        case 'character':
                        case 'character varying':
                        case 'char':
                        case 'nchar':
                        case 'name':
                        case 'longvarchar':
                        case 'string':
                            $values[$k] = ':'.$fieldData->fieldName;
                            $binds[$fieldData->fieldName] = $fieldData->name;
                            $sqlSet .= ', '.$fieldData->fieldName.'= :'.$fieldData->fieldName;

                        break;
                        default:
                            $sqlSet .= ', '.$fieldData->fieldName.'= '.$values[$k];
                    }
                }
            }
            $src[] = substr($sqlSet, 1);

            $sqlCondition = $this->buildSimpleConditions($pkFields, 'record->', false);
            if ($sqlCondition != '') {
                $src[] = ' where '.$sqlCondition;
            }

            $src[] = "';";

            // We have clob/strings binds
            if (!empty($binds)) {
                $src[] = '   $prep = $this->_conn->prepare ($query);';
                foreach ($binds as $variable => $name) {
                    $src[] = '   $prep->bindParam(\':'.$variable.'\', $record->'.$name.');';
                }
                $src[] = '   $result = $prep->execute();';
            } else {
                $src[] = '   $result = $this->_conn->exec ($query);';
            }

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
            $src[] = "     throw new \\Jelix\\Dao\\Generator\\Exception('(502)Update is impossible with this dao because table contains only primary keys (DAO: ".$daoFile->getName().", file: ".$daoFile->getPath().")');";        }

        $src[] = ' }'; //ends the update function
        return implode("\n", $src);
    }
}
