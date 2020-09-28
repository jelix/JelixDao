<?php
/**
 * @author      Laurent
 * @copyright   2020 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\DaoTests;

use Jelix\Dao\ContextInterface;
use Jelix\Dao\DaoFileInterface;
use \Jelix\Database\AccessParameters;
use \Jelix\Database\Connection;


class FileTest implements DaoFileInterface
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

    public function getCompiledFilePath()
    {
        return $this->compilPath;
    }
}


class ContextTest implements ContextInterface
{
    /**
     * @var \Jelix\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var \Jelix\Database\Schema\SqlToolsInterface
     */
    protected $dbTools;

    /**
     * ContextTest constructor.
     *
     * @param $databaseType
     */


    public function __construct($databaseType)
    {
        if ($databaseType == 'mysql') {
            $parameters = array(
                'driver' => 'mysqli',
                'host' => "mysql",
                'user'=> "jelix",
                'password'=>'jelixpass',
                'database'=> 'jelixtests'
            );
            $toolsClass = '\Jelix\Database\Schema\Mysql\SQLTools';
        }
        else if ($databaseType == 'pgsql') {
            $parameters = array(
                'driver' => 'pgsql',
                'host' => "pgsql",
                'port' => '5432',
                'user'=> "jelix",
                'password'=>'jelixpass',
                'database'=> 'jelixtests'
            );
            $toolsClass = '\Jelix\Database\Schema\Postgresql\SQLTools';
        }
        else if ($databaseType == 'sqlite')
        {
            $parameters = array(
                'driver'=>'sqlite3',
                "database"=>"/src/tests/tests/units/tests.sqlite3",
            );
            $toolsClass = '\Jelix\Database\Schema\Sqlite\SQLTools';
        }
        else {
            throw new \Exception('bad databaseType');
        }

        $accessParameters = new AccessParameters($parameters, array('charset'=>'UTF-8'));
        $this->connection = Connection::create($accessParameters);
        $this->dbTools = new $toolsClass($this->connection);
    }

    public function getDbTools()
    {
        return $this->dbTools;
    }

    public function resolveDaoPath($path)
    {
        return new FileTest($path,
            __DIR__.'/resources/dao/'.$path.'.xml',
            __DIR__.'/tmp/compile.'.$path.'.php'
        );
    }

    public function resolveRecordClassPath($path)
    {
        return new FileTest($path,
            __DIR__.'/resources/records/'.$path.'.php',
            __DIR__.'/tmp/compile-record.'.$path.'.php'
        );
    }
}

