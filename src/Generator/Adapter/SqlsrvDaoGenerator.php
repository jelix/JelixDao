<?php
/**
 * @author     Yann Lecommandoux
 * @contributor Laurent Jouanneau
 *
 * @copyright  2008 Yann Lecommandoux, 2017-2020 Laurent Jouanneau
 *
 * @see     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Generator\Adapter;

/**
 * driver for JelixDao compiler.
 */
class SqlsrvDaoGenerator extends \Jelix\Dao\Generator\AbstractDaoGenerator
{
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';

    protected function buildUpdateAutoIncrementPK($pkai, $pTableRealName)
    {
        return '$record->'.$pkai->name.'= $this->_conn->lastInsertId();';
    }

    protected function _encloseName($name)
    {
        return '['.$name.']';
    }

}
