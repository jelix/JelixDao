<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2007-2020 Laurent Jouanneau
 *
 * @see      https://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao\Generator\Adapter;

use Jelix\Dao\Parser\DaoTable;

/**
 * driver for JelixDao compiler.
 */
class MysqlDaoGenerator extends \Jelix\Dao\Generator\AbstractDaoGenerator
{
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';

    protected function escapeTableNameForPHP(DaoTable $table)
    {
        $table->escapedNameForPhp = $this->_encloseName('\'.$this->_conn->prefixTable(\''.$table->realName.'\').\'');
        $table->escapedNameForPhpForFrom = $table->escapedNameForPhp.$this->aliasWord.$this->_encloseName($table->name);
        $table->enclosedName = $this->_encloseName($table->name);
    }
}
