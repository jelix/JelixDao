<?php
/**
* @author      Laurent Jouanneau
* @copyright   2021 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(__DIR__.'/MainApiAbstract.lib.php');

class jdao_main_api_mysqliTest extends MainApiAbstract {

    protected function getConnector()
    {
        $accessParameters = new \Jelix\Database\AccessParameters(
            array(
                'driver'=>'mysqli',
                'host'=>'mysql',
                'database'=>"jelixtests",
                "user"=>"jelix",
                "password"=>"jelixpass",
            ),
            array('charset'=>'UTF-8')
        );

        $connector = \Jelix\Database\Connection::create($accessParameters);
        return $connector;
    }

}
