<?php

use Jelix\Dao\DaoHookInterface;

/**
 * @author      Laurent Jouanneau
 * @copyright   2021 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


class HookTest extends \Jelix\UnitTests\UnitTestCaseDb
{
    protected function getConnection()
    {
        $accessParameters = new \Jelix\Database\AccessParameters(
            array(
                'driver' => 'sqlite3',
                'database' => "tests.sqlite3",
            ),
            array('charset' => 'UTF-8')
        );

        $connector = \Jelix\Database\Connection::create($accessParameters);

        return $connector;
    }


    function testHook()
    {
        $tempPath = __DIR__.'/../tmp/hook_compiled/';
        $daosDirectory = __DIR__.'/../lib/daos/';
        $daoLoader = new \Jelix\Dao\DaoLoader(
            $this->getConnection(),
            $tempPath,
            $daosDirectory
        );
        $this->emptyTable('products');
        $dao = $daoLoader->create ('products_events');
        $hook = new \Jelix\DaoTests\HookForTest();
        $dao->setHook($hook);


        $prod1 = $daoLoader->createRecord ('products_events');
        $prod1->name ='assiette';
        $prod1->price = 3.87;
        $prod1->promo = false;
        $dao->insert($prod1);

        $this->assertEquals(
            array(
                array('onInsert', 'products_events', $prod1, DaoHookInterface::EVENT_BEFORE),
                array('onInsert', 'products_events', $prod1, DaoHookInterface::EVENT_AFTER),
            ),
            $hook->called
        );

        $hook->called = array();
        $prod1->price = 5.98;
        $dao->update($prod1);

        $this->assertEquals(
            array(
                array('onUpdate', 'products_events', $prod1, DaoHookInterface::EVENT_BEFORE),
                array('onUpdate', 'products_events', $prod1, DaoHookInterface::EVENT_AFTER),
            ),
            $hook->called
        );

        $hook->called = array();
        $dao->removePromo();
        $this->assertEquals(
            array(
                array('onCustomMethod', 'products_events', 'removePromo', 'update', array(), DaoHookInterface::EVENT_BEFORE),
                array('onCustomMethod', 'products_events', 'removePromo', 'update', array(), DaoHookInterface::EVENT_AFTER),
            ),
            $hook->called
        );

        $hook->called = array();
        $dao->delete($prod1->id);
        $this->assertEquals(
            array(
                array('onDelete', 'products_events', array('id'=>$prod1->id), DaoHookInterface::EVENT_BEFORE, null),
                array('onDelete', 'products_events', array('id'=>$prod1->id), DaoHookInterface::EVENT_AFTER, 1),
            ),
            $hook->called
        );

    }
}
