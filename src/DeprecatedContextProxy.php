<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2026 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

use Jelix\Database\Schema\SQLSyntaxHelpersInterface;

class DeprecatedContextProxy implements ContextInterface, ContextInterface2
{
    protected ContextInterface $deprecatedContext;

    /**
     * SQL type
     */
    protected $sqlType;

    /**
     * @var SQLSyntaxHelpersInterface
     */
    protected $syntaxHelpers;

    public function __construct(ContextInterface $deprecatedContext)
    {
        $this->deprecatedContext = $deprecatedContext;

        if ($deprecatedContext instanceof ContextInterface2::class) {
            $this->sqlType = ucfirst($deprecatedContext->getSQLType());
            $this->syntaxHelpers = $deprecatedContext->getSqlSyntaxHelpers();
        }
        else {
            $conn = $deprecatedContext->getConnector();
            if ($conn) {
                $this->sqlType = ucfirst($conn->getSQLType());
                $this->syntaxHelpers = $conn->sqlSyntaxHelpers();
            }
            else {
                throw new Exception('Compiler: given context should return a connector object, or should implement ContextInterface2');
            }
        }
    }

    public function getConnector()
    {
        return $this->deprecatedContext->getConnector();
    }

    public function getDbTools()
    {
        return $this->deprecatedContext->getDbTools();
    }

    public function resolveDaoPath($path)
    {
        return $this->deprecatedContext->resolveDaoPath($path);
    }

    public function resolveCustomRecordClassPath($path)
    {
        return $this->deprecatedContext->resolveCustomRecordClassPath($path);
    }

    public function shouldCheckCompiledClassCache()
    {
        return $this->deprecatedContext->shouldCheckCompiledClassCache();
    }

    public function getSqlType(): string
    {
        return $this->sqlType;
    }

    public function getSqlSyntaxHelpers(): SQLSyntaxHelpersInterface
    {
        return $this->syntaxHelpers;
    }
}