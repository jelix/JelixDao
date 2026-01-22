<?php
/**
 * @author      Laurent
 * @copyright   2020-2021 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\DaoTests;

use Jelix\Dao\ContextInterface;
use Jelix\Dao\ContextInterface2;
use Jelix\Database\AccessParameters;
use Jelix\Database\Connection;
use Jelix\Database\ConnectionInterface;
use Jelix\Database\Schema\SQLSyntaxHelpersInterface;

class ContextForTest implements ContextInterface, ContextInterface2
{
    /**
     * @var \Jelix\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * @var \Jelix\Database\Schema\SqlToolsInterface
     */
    protected $dbTools;


    protected $checkCompiledCache = true;

    /**
     * SQL type
     */
    protected $sqlType;

    /**
     * @var SQLSyntaxHelpersInterface
     */
    protected $syntaxHelpers;

    /**
     * ContextTest constructor.
     *
     * @param $databaseType
     */
    public function __construct($databaseType, $checkCompiledCache=true)
    {
        if ($databaseType == 'mysql') {
            $parameters = array(
                'driver' => 'mysqli',
                'host' => "mysql",
                'user'=> "jelix",
                'password'=>'jelixpass',
                'database'=> 'jelixtests'
            );
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
        }
        else if ($databaseType == 'sqlite')
        {
            $parameters = array(
                'driver'=>'sqlite3',
                "database"=>"/app/tests/units/tests.sqlite3",
            );
        }
        else {
            throw new \Exception('bad databaseType');
        }

        $this->sqlType = $databaseType;

        $accessParameters = new AccessParameters($parameters, array('charset'=>'UTF-8'));
        $this->connection = Connection::create($accessParameters);
        $this->dbTools = $this->connection->tools();

        $this->syntaxHelpers = Connection::getSqlSyntaxHelpers($this->sqlType);

        $this->checkCompiledCache = $checkCompiledCache;
    }

    /**
     * @return ConnectionInterface
     */
    function getConnector()
    {
        return $this->connection;
    }

    public function getDbTools()
    {
        return $this->dbTools;
    }

    public function getSqlType() : string
    {
        return $this->sqlType;
    }

    public function getSqlSyntaxHelpers() : SQLSyntaxHelpersInterface
    {
        return $this->syntaxHelpers;
    }

    public function resolveDaoPath($path)
    {
        return new DaoFileForTest($path,
            __DIR__.'/daos/'.$path.'.xml',
            __DIR__.'/tmp/compiled/compile.'.$path.'.php'
        );
    }

    public function resolveCustomRecordClassPath($path)
    {
        return new RecordClassForTest($path,
            __DIR__.'/daos/'.$path.'.php'
        );
    }

    public function shouldCheckCompiledClassCache() {
        return $this->checkCompiledCache;
    }
}

