<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2026 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

use Jelix\Database\Schema\SQLSyntaxHelpersInterface;

/**
 * Add new methods to a context object.
 *
 * This interface will be merged with ContextInterface in a future major version.
 */
interface ContextInterface2 extends ContextInterface
{
    /**
     * @return string the SQL type
     */
    public function getSqlType() : string;

    /**
     * @return SQLSyntaxHelpersInterface the SQL syntax helpers corresponding to the SQL type returned by getSqlType()
     */
    public function getSqlSyntaxHelpers() : SQLSyntaxHelpersInterface;
}
