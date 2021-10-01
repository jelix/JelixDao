<?php
/**
* @author      Laurent Jouanneau
* @copyright   2021 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/MainApiAbstract.lib.php');
/**
 *
 */
class jdao_main_api_sqlite3Test extends MainApiAbstract {

    static protected $productIdType = 'integer';
    static protected $productPriceType = 'float';
    static protected $productPromoType = 'integer';

    protected function getConnector()
    {
        $accessParameters = new \Jelix\Database\AccessParameters(
            array(
                'driver'=>'sqlite3',
                'database'=>"tests.sqlite3",
            ),
            array('charset'=>'UTF-8')
        );

        $connector = \Jelix\Database\Connection::create($accessParameters);
        return $connector;
    }

    function testInstanciation() {
        $dao = $this->daoLoader->create ('products');
        $this->assertInstanceOf('ProductsSqliteFactory', $dao);

        $dao = $this->daoLoader->get ('products');
        $this->assertInstanceOf('ProductsSqliteFactory', $dao);

        $daorec = $this->daoLoader->createRecord ('products');
        $this->assertInstanceOf('ProductsSqliteRecord', $daorec);

        $daorec = $dao->createRecord();
        $this->assertInstanceOf('ProductsSqliteRecord', $daorec);
    }

    function testBinaryField()
    {
        // FIXME sqlite3 driver does not support binary field
        $this->assertTrue(true);
    }
}

