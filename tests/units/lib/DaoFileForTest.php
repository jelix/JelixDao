<?php
/**
 * @author      Laurent
 * @copyright   2020-2021 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\DaoTests;

use Jelix\Dao\DaoFileInterface;


class DaoFileForTest implements DaoFileInterface
{
    protected $name;

    protected $path;

    protected $compilPath;


    function __construct($name, $path, $compilPath)
    {
        $this->name = $name;
        $this->path = $path;
        $this->compilPath = $compilPath;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getCompiledFactoryClass()
    {
        // TODO: Implement getCompiledFactoryClass() method.
    }

    public function getCompiledRecordClass()
    {
        // TODO: Implement getCompiledRecordClass() method.
    }

    public function getCompiledFactoryFilePath()
    {
        // TODO: Implement getCompiledFactoryFilePath() method.
    }

    public function getCompiledRecordFilePath()
    {
        // TODO: Implement getCompiledRecordFilePath() method.
    }
}

