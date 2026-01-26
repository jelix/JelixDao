<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2021-2026 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

use Jelix\Database\Connection;
use Jelix\Database\ConnectionInterface;
use Jelix\Database\Schema\SQLSyntaxHelpersInterface;
use Jelix\FileUtilities\Path;

class Context implements ContextInterface2
{
    /**
     * @var ConnectionInterface|null
     * @deprecated
     */
    protected $connection;

    /**
     * SQL type
     */
    protected $sqlType;

    /**
     * @var SQLSyntaxHelpersInterface
     */
    protected $syntaxHelpers;

    protected $basePath;

    protected $tempPath;

    protected $daoXmlSuffix = '.xml';
    protected $daoXmlSuffixRe = '/\\.xml$/';
    protected $daoPhpSuffix = '.php';
    protected $daoPhpSuffixRe = '/\\.php$/';

    protected $daoFactPhpSuffix = '.php';

    protected $daoFactPhpSuffixRe = '/\\.php$/';

    /**
     * @param ConnectionInterface|string $connection  Connector (deprecated)
     * or the type of the database (mysql, pgsql, ...)
     *
     * The connection should be passed to other classes that are using the context.
     *
     * @param string $tempPath
     * @param string $daosDirectory
     */
    public function __construct($connection, $tempPath, $daosDirectory = '')
    {
        if (is_string($connection)) {
            $this->sqlType = $connection;
            $connection = null;
        }
        else {
            $this->sqlType = $connection->getSQLType();
        }

        $this->syntaxHelpers = Connection::getSqlSyntaxHelpers($this->sqlType);

        $this->connection = $connection;
        $this->tempPath = $tempPath;
        $this->basePath = $daosDirectory;

    }

    /**
     * @return ConnectionInterface|null
     * @deprecated
     */
    public function getConnector()
    {
        return $this->connection;
    }

    /**
     * @return \Jelix\Database\Schema\SqlToolsInterface|null
     * @deprecated
     */
    public function getDbTools()
    {
        if ($this->connection === null) {
            return null;
        }
        return $this->connection->tools();
    }

    /**
     * @inheritDoc
     */
    public function getSqlType() : string
    {
        return $this->sqlType;
    }

    /**
     * @inheritDoc
     */
    public function getSqlSyntaxHelpers() : SQLSyntaxHelpersInterface
    {
        return $this->syntaxHelpers;
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

        if (!preg_match($this->daoXmlSuffixRe, $path)) {
            $path .= $this->daoXmlSuffix;
        }
        return new DaoSimpleFile($daoName, $path, $this->sqlType, $this->tempPath, $this->daoXmlSuffix);
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

        if (!preg_match($this->daoPhpSuffixRe, $path)) {
            $path .= $this->daoPhpSuffix;
        }

        $class = ucfirst(str_replace($this->daoPhpSuffix, '', basename($path)));

        return new CustomRecordClassFile($class, $path);
    }
    /**
     * @inheritDoc
     */
    public function resolveCustomFactoryClassPath($path)
    {
        if ($path[0] == '\\') {
            // the given path is a full class name with a namespace, so we make the assumption that the
            // class can be autoloaded, and we don't have to forge a path
            return new CustomClassFile($path);
        }

        if (!Path::isAbsolute($path)) {
            $path = Path::normalizePath($this->basePath.'/'.$path);
        }

        if (!preg_match($this->daoFactPhpSuffixRe, $path)) {
            $path .= $this->daoFactPhpSuffix;
        }

        $class = ucfirst(str_replace($this->daoFactPhpSuffix, '', basename($path)));

        return new CustomClassFile($class, $path);
    }

    /**
     * @inheritDoc
     */
    public function shouldCheckCompiledClassCache()
    {
        return false;
    }
}
