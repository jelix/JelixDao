<?php
/**
 * @author      Laurent
 * @copyright   2020-2026 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\DaoTests;

use Jelix\Dao\CustomClassFileInterface;



class CustomClassForTest implements CustomClassFileInterface
{
    protected $name;

    protected $path;

    function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    public function getClassName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->path;
    }
}

