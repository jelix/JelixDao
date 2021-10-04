<?php
/**
 * @author      Laurent
 * @copyright   2021 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\DaoTests;

use Jelix\Dao\DaoConditions;
use Jelix\Dao\DaoHookInterface;
use Jelix\Dao\DaoRecordInterface;

class HookForTest implements DaoHookInterface
{

    public $called = array();

    public function onInsert(string $daoName, DaoRecordInterface $record, $when)
    {
        $this->called[] = array('onInsert', $daoName, $record, $when);
    }

    public function onUpdate(string $daoName, DaoRecordInterface $record, $when)
    {
        $this->called[] = array('onUpdate', $daoName, $record, $when);

    }

    public function onDelete(string $daoName, $keys, $when, $result = null)
    {
        $this->called[] = array('onDelete', $daoName, $keys, $when, $result);

    }

    public function onDeleteBy(string $daoName, DaoConditions $searchCond, $when, $result = null)
    {
        $this->called[] = array('onDeleteBy', $daoName, $searchCond, $when, $result);

    }

    public function onCustomMethod(string $daoName, string $methodName, string $methodType, $parameters, $when)
    {
        $this->called[] = array('onCustomMethod', $daoName, $methodName, $methodType, $parameters, $when);
    }



}
