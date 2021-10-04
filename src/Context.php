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
use Jelix\FileUtilities\Path;

class Context implements ContextInterface
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    protected $basePath;

    protected $tempPath;

    public function __construct(ConnectionInterface $connection, $tempPath, $daosDirectory = '')
    {
        $this->connection = $connection;
        $this->tempPath = $tempPath;
        $this->basePath = $daosDirectory;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnector()
    {
        return $this->connection;
    }

    /**
     * @return \Jelix\Database\Schema\SqlToolsInterface
     */
    public function getDbTools()
    {
        return $this->connection->tools();
    }

    /**
     * @inheritDoc
     */
    public function resolveDaoPath($path)
    {

        if (!Path::isAbsolute($path)) {
            $daoName = $path;
            $path = Path::normalizePath($this->basePath.'/'.$path);
        }
        else {
            $daoName = basename($path);
        }
        if (!preg_match("/\\.xml$/", $path)) {
            $path .= '.xml';
        }
        return new DaoSimpleFile($daoName, $path, $this->connection->getSQLType(), $this->tempPath);
    }

    /**
     * @inheritDoc
     */
    public function resolveCustomRecordClassPath($path)
    {
        if ($path[0] == '\\') {
            // the given path is a full class name with a namespace, so we make the assumption that the
            // class can be autoloaded, and we don't have to forge a path
            return new CustomRecordClassFile($path);
        }

        if (!Path::isAbsolute($path)) {
            $path = Path::normalizePath($this->basePath.'/'.$path);
        }

        if (!preg_match("/\\.php$/", $path)) {
            $path .= '.php';
        }

        $class = ucfirst(str_replace('.php', '', basename($path)));

        return new CustomRecordClassFile($class, $path);
    }

    /**
     * @inheritDoc
     */
    public function shouldCheckCompiledClassCache()
    {
        return false;
    }
}
