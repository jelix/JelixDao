<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2020 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

use Jelix\Database\Schema\SqlToolsInterface;

interface ContextInterface
{
    /**
     * @return SqlToolsInterface
     */
    function getDbTools();

    /**
     * @param $path
     *
     * @return DaoFileInterface
     */
    function resolveDaoPath($path);

    /**
     * @param $path
     *
     * @return DaoFileInterface
     */
    function resolveRecordClassPath($path);
}
