<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2021 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

use Jelix\Database\ConnectionInterface;

class DaoLoader
{
    /**
     * @var ConnectionInterface
     */
    protected $connector;

    /**
     * @var string
     */
    protected $tempPath;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var  DaoFactoryInterface[]
     */
    protected $daoSingleton = array();

    /**
     * @param ConnectionInterface $connector
     * @param string $tempPath
     * @param string $daosDirectory
     */
    public function __construct(ConnectionInterface $connector, $tempPath, $daosDirectory = '')
    {
        $this->connector = $connector;
        $this->tempPath = $tempPath;
        $this->basePath = $daosDirectory;

        $this->context = new Context(
            $connector,
            $tempPath,
            $daosDirectory
        );
    }

    /**
     * creates a new instance of a DAO.
     * If no dao class is founded, try to compile a DAO from the dao xml file.
     *
     * @param string $daoXmlFile   the dao file
     *
     * @return DaoFactoryInterface the dao object
     */
    public function create($daoXmlFile)
    {
        $daoFile = $this->context->resolveDaoPath($daoXmlFile);

        if (!file_exists($daoFile->getCompiledFilePath())) {
            // the class corresponding to the dao does not exist, let's create it.
            $compiler = new \Jelix\Dao\Generator\Compiler();
            $compiler->compile($daoFile, $this->context);
        }
        require_once($daoFile->getCompiledFilePath());
        $class = $daoFile->getCompiledFactoryClass();
        $dao = new $class($this->connector);
        return $dao;
    }

    /**
     * return a DAO instance. It Handles a singleton of the DAO.
     * If no dao class is founded, try to compile a DAO from the dao xml file.
     *
     * @param string $daoXmlFile   the dao file

     * @return DaoFactoryInterface the dao object
     */
    public function get($daoXmlFile)
    {
        $daoId = $daoXmlFile.'#'.$this->connector->getSQLType();
        if (!isset($this->daoSingleton[$daoId])) {
            $this->daoSingleton[$daoId] = $this->create($daoXmlFile);
        }

        return $this->daoSingleton[$daoId];
    }

    /**
     * Release dao singleton own by jDao. Internal use.
     *
     * @internal
     */
    public function releaseAll()
    {
        $this->daoSingleton = array();
    }

    /**
     * creates a record object for the given dao.
     *
     * See also AbstractDaoFactory::createRecord()
     *
     * @param string $daoXmlFile   the dao file
     *
     * @return DaoRecordInterface a dao record object
     */
    public function createRecord($daoXmlFile)
    {
        $dao = $this->get($daoXmlFile);
        return $dao->createRecord();
    }
}
