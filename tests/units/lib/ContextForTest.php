<?php
/**
 * @author      Laurent
 * @copyright   2020-2026 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\DaoTests;

use Jelix\Dao\ContextInterface;
use Jelix\Dao\ContextInterface2;
use Jelix\Dao\CustomClassFile;
use Jelix\Database\Connection;
use Jelix\Database\Schema\SQLSyntaxHelpersInterface;

class ContextForTest implements ContextInterface, ContextInterface2
{
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
        $this->sqlType = $databaseType;

        $this->syntaxHelpers = Connection::getSqlSyntaxHelpers($this->sqlType);

        $this->checkCompiledCache = $checkCompiledCache;
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

    public function resolveCustomFactoryClassPath($path)
    {
        if ($path[0] == '\\') {
            // the given path is a full class name with a namespace, so we make the assumption that the
            // class can be autoloaded, and we don't have to forge a path
            return new CustomClassFile($path);
        }
        return new CustomClassFile($path,
            __DIR__.'/daos/'.$path.'.php'
        );
    }

    public function shouldCheckCompiledClassCache() {
        return $this->checkCompiledCache;
    }
}

