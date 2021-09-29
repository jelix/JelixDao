<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2020 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

/**
 * Interface for objects representing an custom record class
 *
 * Depending of the framework which is integrate JelixDao, the implementation
 * should know where to read the content
 *
 * @package Jelix\Dao
 */
interface CustomRecordClassFileInterface
{
    /**
     * The class name
     * @return string
     */
    function getClassName();

    /**
     * Path of the PHP file containing the class. It can be empty if the class
     * can be autoloaded
     *
     * @return string
     */
    function getPath();

}
