<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2021 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

class DaoSimpleFile implements DaoFileInterface
{

    protected $daoFile;

    protected $tempPath;

    /**
     * DaoSimpleFile constructor.
     * @param string $daoXmlFile the path to the dao file
     * @param string $tempPath directory where to store the compiled file
     */
    function __construct($daoXmlFile, $tempPath)
    {
        $this->daoFile = $daoXmlFile;
        $this->tempPath = $tempPath;
    }

    /**
     * A name that allow to identify easily the dao.
     *
     * @return string a filename, a URI or another identifier
     */
    function getName()
    {
        return basename($this->daoFile);
    }

    /**
     * @return string path to the Dao file
     */
    function getPath()
    {
        return $this->daoFile;
    }

    /**
     * @return string path of a file where to store generated classes
     */
    function getCompiledFilePath()
    {
        return $this->tempPath.'/'.$this->getName().'.php';
    }

    /**
     * @return string name of the factory class that should be used by the generator
     */
    function getCompiledFactoryClass()
    {
        return ucfirst(str_replace('.xml', '', $this->getName())).'Factory';
    }

    /**
     * @return string name of the record class that should be used by the generator
     */
    function getCompiledRecordClass()
    {
        return ucfirst(str_replace('.xml', '', $this->getName())).'Record';
    }
}
