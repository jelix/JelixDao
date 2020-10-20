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
 * Interface for objects representing an XML DAO file
 *
 * Depending of the framework which is integrate JelixDao, the implementation
 * should know where to read the content, and where to store the generated
 * PHP content.
 *
 * @package Jelix\Dao
 */
interface DaoFileInterface
{
    /**
     * A name that allow to identify easily the dao.
     *
     * @return string a filename, a URI or another identifier
     */
    function getName();

    /**
     * @return string path to the Dao file
     */
    function getPath();

    /**
     * @return string path of a file where to store generated classes
     */
    function getCompiledFilePath();

    /**
     * @return string name of the factory class that should be used by the generator
     */
    function getCompiledFactoryClass();

    /**
     * @return string name of the record class that should be used by the generator
     */
    function getCompiledRecordClass();
}
