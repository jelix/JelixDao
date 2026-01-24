<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2026 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

/**
 * Adds new methods
 * @package Jelix\Dao
 */
interface DaoFileInterface2 extends DaoFileInterface
{
    /**
     * @return string path of a file where to store the generated factory class
     */
    public function getCompiledFactoryFilePath();

    /**
     * @return string path of a file where to store the generated factory class
     */
    public function getCompiledRecordFilePath();
}
