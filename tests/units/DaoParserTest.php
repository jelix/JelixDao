<?php

/**
 * @author      Laurent
 * @copyright   2020 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\DaoTests;

use Jelix\Dao\Parser\XMLDaoParser;

class testDaoParser extends XMLDaoParser {

    function testParseDatasource(\SimpleXMLElement $xml) {
        $this->parseDatasource($xml);
    }
    function testParseRecord(\SimpleXMLElement $xml) {
        $this->parseRecord($xml);
    }
    function testParseFactory(\SimpleXMLElement $xml) {
        $this->parseFactory($xml);
    }
}
