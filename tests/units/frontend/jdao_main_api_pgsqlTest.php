<?php
/**
* @author      Laurent Jouanneau
* @contributor
* @copyright   2021 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/MainApiAbstract.lib.php');
/**
 *
 */
class jdao_main_api_pgsqlTest extends MainApiAbstract
{
    static protected $trueValue = 't';
    static protected $falseValue = 'f';

    protected $article2TableName = 'newspaper.article2';
    protected $article2CatTableName = 'newspaper2.article2_category';

    protected function getConnector()
    {
        $accessParameters = new \Jelix\Database\AccessParameters(
            array(
                'driver'=>'pgsql',
                'host'=>'pgsql',
                'port'=>'5432',
                'database'=>"jelixtests",
                "user"=>"jelix",
                "password"=>"jelixpass",
            ),
            array('charset'=>'UTF-8')
        );

        $connector = \Jelix\Database\Connection::create($accessParameters);
        return $connector;
    }

    function testInstanciation()
    {
        $dao = $this->daoLoader->create ('products');
        $this->assertInstanceOf('ProductsPgsqlFactory', $dao);

        $dao = $this->daoLoader->get ('products');
        $this->assertInstanceOf('ProductsPgsqlFactory', $dao);

        $daorec = $this->daoLoader->createRecord ('products');
        $this->assertInstanceOf('ProductsPgsqlRecord', $daorec);

        $daorec = $dao->createRecord();
        $this->assertInstanceOf('ProductsPgsqlRecord', $daorec);
    }

}

