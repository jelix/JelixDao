<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2006-2020 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use PHPUnit\Framework\TestCase;

class ParseDatasourceTest extends TestCase {

    use \Jelix\UnitTests\AssertComplexTrait;
    protected $_selector;
    protected $_context;

    protected function setUp(): void
    {
        $this->_selector = new \Jelix\DaoTests\DaoFileTest("foo", "bar", "baz");
        $this->_context = new \Jelix\DaoTests\ContextTest("sqlite");
    }

    protected function tearDown(): void
    {
        $this->_selector = null;
        $this->_context = null;
    }

    protected $dsTest=array(
        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news" primarykey="news_id" />
  </datasources>
</dao>',

            '<?xml version="1.0"?>
<object class="\Jelix\Dao\Parser\XMLDaoParser">
    <array method="getProperties()">[]</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="news" />
            <array key="pk" value="">["news_id"]</array>
            <array key="fields">[]</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">[]</array>
    <array method="getOuterJoins()">[]</array>
    <array method="getInnerJoins()">[]</array>
</object>'
        ),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news" realname="foo_news" primarykey="news_id" />
  </datasources>
</dao>',

            '<?xml version="1.0"?>
<object class="\Jelix\Dao\Parser\XMLDaoParser">
    <array method="getProperties()">[]</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="foo_news" />
            <array key="pk" value="">["news_id"]</array>
            <array key="fields">[]</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">[]</array>
    <array method="getOuterJoins()">[]</array>
    <array method="getInnerJoins()">[]</array>
</object>'
        ),



        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news"           primarykey="news_id" />
     <foreigntable name="news_rubriques" primarykey="news_rubriques_id" onforeignkey="news_rubrique" />
  </datasources>
</dao>',

            '<?xml version="1.0"?>
<object class="\Jelix\Dao\Parser\XMLDaoParser">
    <array method="getProperties()">[]</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="news" />
            <array key="pk" value="">["news_id"]</array>
            <array key="fields">[]</array>
        </array>
        <array key="news_rubriques">
            <string key="name" value="news_rubriques" />
            <string key="realname" value="news_rubriques" />
            <array key="pk" value="">["news_rubriques_id"]</array>
            <array key="fk" value="">["news_rubrique"]</array>
            <array key="fields">[]</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">[]</array>
    <array method="getOuterJoins()">[]</array>
    <array method="getInnerJoins()">["news_rubriques"]</array>
</object>'
        ),


        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news"           primarykey="news_id" />
     <optionalforeigntable name="news_rubriques" primarykey="news_rubriques_id" onforeignkey="news_rubrique" />
  </datasources>
</dao>',

            '<?xml version="1.0"?>
<object class="\Jelix\Dao\Parser\XMLDaoParser">
    <array method="getProperties()">[]</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="news" />
            <array key="pk" value="">["news_id"]</array>
            <!-- <array key="fk" value="">[]</array>-->
            <array key="fields">[]</array>
        </array>
        <array key="news_rubriques">
            <string key="name" value="news_rubriques" />
            <string key="realname" value="news_rubriques" />
            <array key="pk" value="">["news_rubriques_id"]</array>
            <array key="fk" value="">["news_rubrique"]</array>
            <array key="fields">[]</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">[]</array>
    <array method="getOuterJoins()">[["news_rubriques",0]]</array>
    <array method="getInnerJoins()">[]</array>
</object>'
        ),


        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
     <primarytable name="news"           primarykey="news_id" />
     <optionalforeigntable name="news_rubriques" primarykey="news_rubriques_id" onforeignkey="news_rubrique" />
     <foreigntable name="news_author" realname="jx_authors_news" primarykey="author_id" onforeignkey="author_id" />
  </datasources>
</dao>',

            '<?xml version="1.0"?>
<object class="\Jelix\Dao\Parser\XMLDaoParser">
    <array method="getProperties()">[]</array>
    <array method="getTables()">
        <array key="news">
            <string key="name" value="news" />
            <string key="realname" value="news" />
            <array key="pk">["news_id"]</array>
            <!-- <array key="fk" value="">[]</array>-->
            <array key="fields">[]</array>
        </array>
        <array key="news_rubriques">
            <string key="name" value="news_rubriques" />
            <string key="realname" value="news_rubriques" />
            <array key="pk">["news_rubriques_id"]</array>
            <array key="fk">["news_rubrique"]</array>
            <array key="fields">[]</array>
        </array>
        <array key="news_author">
            <string key="name" value="news_author" />
            <string key="realname" value="jx_authors_news" />
            <array key="pk">["author_id"]</array>
            <array key="fk">["author_id"]</array>
            <array key="fields">[]</array>
        </array>
    </array>
    <string method="getPrimaryTable()" value="news"/>
    <array method="getMethods()">[]</array>
    <array method="getOuterJoins()">[["news_rubriques",0]]</array>
    <array method="getInnerJoins()">["news_author"]</array>
</object>'
        ),

    );

    function getDsTest() {
        return $this->dsTest;
    }

    /**
     * @dataProvider getDsTest
     */
    function testGoodDatasources($xmls, $expected) {

        $xml= simplexml_load_string($xmls);
        $p = new \Jelix\DaoTests\testDaoParser($this->_selector, $this->_context);
        $p->testParseDatasource($xml);
        $this->assertComplexIdenticalStr($p, $expected);
    }




    protected $dsTestbad=array(
        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
  </datasources>
</dao>',
            'Table is missing (dao: foo, file: bar)',
            520
        ),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable />
  </datasources>
</dao>',
            'table name is missing (dao: foo, file: bar)',
            522

        ),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" />
  </datasources>
</dao>',
            'primary key name is missing (dao: foo, file: bar)',
            523

        ),
        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey=""/>
  </datasources>
</dao>',
            'primary key name is missing (dao: foo, file: bar)',
            523

        ),
        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news"           primarykey="news_id" />
    <primarytable />
  </datasources>
</dao>',
            'Too many primary tables, only one allowed (dao: foo, file: bar)',
            521

        ),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news"           primarykey="news_id" />
    <foreigntable name="news_author" realname="jx_authors_news" primarykey="author_id" />

  </datasources>
</dao>',
            'foreign key name is missing on a join (dao: foo, file: bar)',
            524

        ),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news"           primarykey="news_id" />
    <foreigntable name="news_author" realname="jx_authors_news" primarykey="author_id" onforeignkey="" />

  </datasources>
</dao>',
            'foreign key name is missing on a join (dao: foo, file: bar)',
           524

        ),

        array('<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news"           primarykey="news_id" />
    <foreigntable name="news_author" realname="jx_authors_news" primarykey="author_id" onforeignkey="author_id,foo_id" />

  </datasources>
</dao>',
            'foreign key name is missing on a join (dao: foo, file: bar)',
            524

        ),

    );

    function getDsTestBad() {
        return $this->dsTestbad;
    }

    /**
     * @dataProvider getDsTestBad
     */
    function testBadDatasources($xmls, $errMsg, $errCode) {

        $xml= simplexml_load_string($xmls);
        $p = new \Jelix\DaoTests\testDaoParser($this->_selector, $this->_context);
        try{
            $p->testParseDatasource($xml);
            $this->fail("No expected exception!");
        }catch(\Jelix\Dao\Parser\ParserException $e){
            $this->assertEquals($errMsg, $e->getMessage());
            $this->assertEquals($errCode, $e->getCode());
        }
    }

}
