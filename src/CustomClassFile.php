<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2021-2026 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

class CustomClassFile implements CustomClassFileInterface
{
    protected $path;

    protected $class;

    public function __construct($class, $path='')
    {
        $this->path = $path;
        $this->class = $class;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getClassName()
    {
        return $this->class;
    }
}
