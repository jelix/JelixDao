<?php

use Jelix\Dao\Parser\DaoProperty;

/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2009-2020 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */



class testDaoProperty extends DaoProperty {
    public $datatype;
    public $unifiedType;
    public $defaultValue=null;
    public $autoIncrement = false;

    public function __construct() {

        $this->attributes['jsonEncoder'] = 'json_encode(%VALUE%)';
        $this->attributes['jsonDecoder'] = 'json_decode(%FIELD%, true)';
    }
}

class TestMysqlDaoGenerator extends \Jelix\Dao\Generator\Adapter\MysqlDaoGenerator {

    function GetPkFields() {
        return $this->_getPrimaryFieldsList();
    }

    function GetPropertiesBy ($captureMethod){
        return $this->_getPropertiesBy ($captureMethod);
    }

    function BuildSimpleConditions2 (&$fields, $fieldPrefix='', $forSelect=true){
        return $this->buildSimpleConditions ($fields, $fieldPrefix, $forSelect);
    }

    function BuildConditions2($cond, $fields, $params=array(), $withPrefix=true) {
        return $this->buildConditions ($cond, $fields, $params, $withPrefix);
    }

    function BuildSQLCondition ($condition, $fields, $params, $withPrefix){
        return $this->buildOneSQLCondition ($condition, $fields, $params, $withPrefix, true);
    }

    function GetPreparePHPValue($value, $fieldType, $checknull=true){
        return $this->tools->escapeValue($fieldType, $value, $checknull, true);
    }

    function GetPreparePHPExpr($expr, $fieldType, $checknull=true, $forCondition=''){
        return $this->_preparePHPExpr($expr, $fieldType, $checknull, $forCondition);
    }

    function GetSelectClause ($distinct=false){
        $this->buildFromWhereClause();
        return $this->buildSelectClause ($distinct);
    }

    function GetFromClause(){
        $this->buildFromWhereClause();
        return array($this->sqlFromClause, $this->sqlWhereClause);
    }

    function PrepareValues ($fieldList, $pattern, $prefixfield) {
        return $this->_prepareValues($fieldList, $pattern, $prefixfield);
    }

    function GetBuildCountUserQuery($method) {
        $allField = $this->_getPropertiesBy('All');
        $src = array();
        parent::buildCountUserQuery($method, $src, $allField);
        return implode("\n", $src);
    }

    function GetBuildUpdateUserQuery($method, &$src, &$primaryFields) {
        $this->buildUpdateUserQuery($method, $src, $primaryFields);
    }
}
