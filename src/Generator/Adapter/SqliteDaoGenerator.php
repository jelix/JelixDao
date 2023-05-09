<?php
/**
 * @author     Laurent Jouanneau
 * @contributor Loic Mathaud <loic@mathaud.net>
 * @contributor Steven Jehannet
 *
 * @copyright  2007-2023 Laurent Jouanneau, 2008 Loic Mathaud, 2010 Steven Jehannet
 *
 * @see      https://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Generator\Adapter;

use Jelix\Dao\Parser\DaoProperty;

/**
 * driver for JelixDao compiler.
 */
class SqliteDaoGenerator extends \Jelix\Dao\Generator\AbstractDaoGenerator
{
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';

    protected function buildSelectPattern($pattern, $table, $fieldname, $propname)
    {
        if ($pattern == '%s') {
            $field = $table.$this->_encloseName($fieldname).' as '.$this->_encloseName($propname);
        } else {
            $field = str_replace(array("'", '%s'), array("\\'", $table.$this->_encloseName($fieldname)), $pattern).' as '.$this->_encloseName($propname);
        }

        return $field;
    }

    /**
     * @param \Jelix\Dao\Parser\DaoMethod  $method
     * @param string[]  $src
     * @param DaoProperty[] $allField
     */
    protected function buildCountUserQuery($method, &$src, &$allField)
    {
        if ($method->distinct != '') {
            $properties = $this->_dataParser->getProperties();
            $tables = $this->_dataParser->getTables();
            $prop = $properties[$method->distinct];
            $distinct = ' DISTINCT '.$this->_encloseName($tables[$prop->table]->name).'.'.$this->_encloseName($prop->fieldName);
        } else {
            $distinct = '';
        }

        $src[] = '    $__query = \'SELECT COUNT(*) as c '.($distinct != '' ? 'FROM (SELECT'.$distinct : '').'\'.$this->_fromClause.$this->_whereClause;';
        $glueCondition = ($this->sqlWhereClause != '' ? ' AND ' : ' WHERE ');

        $cond = $method->getConditions();
        if ($cond !== null) {
            $sqlCond = $this->buildConditions($cond, $allField, $method->getParameters(), true);
            if (trim($sqlCond) != '') {
                $src[] = '    $__query .=\''.$glueCondition.$sqlCond."';";
            }
        }
        if ($distinct != '') {
            $src[] .= '    $__query .=\')\';';
        }
    }
}
