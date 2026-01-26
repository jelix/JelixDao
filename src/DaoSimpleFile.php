<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2021-2026 Laurent Jouanneau
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

    protected $factoryClass = '';

    protected $recordClass = '';

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
        $prefix = ucfirst(str_replace(array('/', $suffix), '', $daoName)).$this->sqlType;
        $this->factoryClass = $prefix.'Factory';
        $this->recordClass = $prefix.'Record';
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
     * @return string path of a file where to store the generated factory class
     */
    public function getCompiledFactoryFilePath()
    {
        return $this->tempPath.'/'.$this->factoryClass.'.php';
    }

    /**
     * @return string path of a file where to store the generated factory class
     */
    public function getCompiledRecordFilePath()
    {
        return $this->tempPath.'/'.$this->recordClass.'.php';
    }

    /**
     * @return string name of the factory class that should be used by the generator
     */
    public function getCompiledFactoryClass()
    {
        return $this->factoryClass;
    }

    /**
     * @return string name of the record class that should be used by the generator
     */
    public function getCompiledRecordClass()
    {
        return $this->recordClass;
    }
}
