<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2021-2023 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

class DaoSimpleFile implements DaoFileInterface
{
    protected $daoName;

    protected $daoFile;

    protected $tempPath;

    protected $sqlType;

    protected $suffix;

    /**
     * DaoSimpleFile constructor.
     * @param string $daoName the name as given to JelixDao API.
     * @param string $daoXmlFile the path to the dao file
     * @param string $sqlType type of the sql language (pgsql, sqlite, mysql...)
     * @param string $tempPath directory where to store the compiled file
     */
    public function __construct($daoName, $daoXmlFile, $sqlType, $tempPath, $suffix = '.xml')
    {
        $this->daoName = $daoName;
        $this->daoFile = $daoXmlFile;
        $this->tempPath = $tempPath;
        $this->sqlType = ucfirst($sqlType);
        $this->suffix = $suffix;
    }

    /**
     * A name that allow to identify easily the dao.
     *
     * @return string a filename, a URI or another identifier
     */
    public function getName()
    {
        return $this->daoName;
    }

    /**
     * @return string path to the Dao file
     */
    public function getPath()
    {
        return $this->daoFile;
    }

    /**
     * @return string path of a file where to store generated classes
     */
    public function getCompiledFilePath()
    {
        return $this->tempPath.'/'.$this->getName().'.'.$this->sqlType.'.php';
    }

    /**
     * @return string name of the factory class that should be used by the generator
     */
    public function getCompiledFactoryClass()
    {
        return ucfirst(str_replace(array('/', $this->suffix), '', $this->getName())).$this->sqlType.'Factory';
    }

    /**
     * @return string name of the record class that should be used by the generator
     */
    public function getCompiledRecordClass()
    {
        return ucfirst(str_replace(array('/', $this->suffix), '', $this->getName())).$this->sqlType.'Record';
    }
}
