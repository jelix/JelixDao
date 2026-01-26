<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2020-2026 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

use Jelix\Database\Schema\SQLSyntaxHelpersInterface;

/**
 * It allows to abstract the environment or framework where JelixDao is used.
 *
 * A framework or an application should provides an object that implement this
 * interface so JelixDao could run.
 *
 * @package Jelix\Dao
 */

interface ContextInterface
{
    /**
     * @return string the SQL type
     */
    public function getSqlType() : string;

    /**
     * @return SQLSyntaxHelpersInterface the SQL syntax helpers corresponding to the SQL type returned by getSqlType()
     */
    public function getSqlSyntaxHelpers() : SQLSyntaxHelpersInterface;

    /**
     * Convert the given path, representing a PHP class implementing a factory,
     * to the corresponding CustomClassFileInterface object.
     *
     * The path can be a system file path, or an URI, or any other structured
     * name representing the class file. The path type depends on the framework
     * or the environment where JelixDao is used.
     *
     * @param string $path
     *
     * @return CustomClassFileInterface
     */
    public function resolveCustomFactoryClassPath($path);

    /**
     * Convert the given path, representing an XML DAO file, to the corresponding
     * DaoFileInterface object.
     *
     * The path can be a system file path, or an URI, or any other structured
     * name representing the DAO file. The path type depends on the framework
     * or the environment where JelixDao is used.
     *
     * @param string $path
     *
     * @return DaoFileInterface
     */
    public function resolveDaoPath($path);

    /**
     * Convert the given path, representing a PHP class implementing a record,
     * to the corresponding CustomRecordClassFileInterface object.
     *
     * The path can be a system file path, or an URI, or any other structured
     * name representing the class file. The path type depends on the framework
     * or the environment where JelixDao is used.
     *
     * @param string $path
     *
     * @return CustomRecordClassFileInterface|CustomClassFileInterface
     */
    public function resolveCustomRecordClassPath($path);

    /**
     * In the generated class, indicate if the cache should be checked and then
     * recompiled before loading
     *
     * @return boolean
     */
    public function shouldCheckCompiledClassCache();
}
