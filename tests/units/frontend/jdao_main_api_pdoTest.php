<?php
/**
* @author      Laurent Jouanneau
* @copyright   2021-2023 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/MainApiAbstract.lib.php');
/**
 *
 */
class jdao_main_api_pdoTest extends MainApiAbstract {

    protected $needPDO;

    protected function getConnector()
    {
        $accessParameters = new \Jelix\Database\AccessParameters(
            array(
                'driver'=>'mysqli',
                'host'=>'mysql',
                'database'=>"jelixtests",
                'usepdo' =>1,
                "user"=>"jelix",
                "password"=>"jelixpass",
            ),
            array('charset'=>'UTF-8')
        );

        $connector = \Jelix\Database\Connection::create($accessParameters);
        return $connector;
    }

    function setUp() : void  {
        parent::setUp();
        $this->needPDO =  true;
        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            static::$productIdType = 'integer';
            static::$productPriceType = 'float';
            static::$productPromoType = 'integer';
        }
    }
}

