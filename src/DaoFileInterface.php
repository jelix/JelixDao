<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2020 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

interface DaoFileInterface
{
    /**
     * @return string
     */
    function getName();

    /**
     * @return string
     */
    function getPath();

    /**
     * @return string
     */
    function getCompiledFilePath();

}
