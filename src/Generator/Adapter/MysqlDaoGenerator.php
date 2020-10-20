<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2007-2020 Laurent Jouanneau
 *
 * @see      https://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao\Generator\Adapter;

/**
 * driver for JelixDao compiler.
 */
class MysqlDaoGenerator extends \Jelix\Dao\Generator\AbstractDaoGenerator
{
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';
}
