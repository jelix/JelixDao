<?php
/**
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2020 Laurent Jouanneau
* @link        https://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class parserMethodUpdateTest extends \PHPUnit\Framework\TestCase {

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


    protected $methDatas=array(
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
                <values>
                    <value property="subject" expr="\'abc\'" />
                </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">{"subject":["\'abc\'",true]}</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <parameter name="mytext" />
                <values>
                    <value property="subject" expr="$mytext" />
                </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">["mytext"]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">{"subject":["$mytext",true]}</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
                <values>
                    <value property="subject" value="my text" />
                </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">{"subject":["my text",false]}</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
       
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="subject" value="my text" />
            </values>
            <conditions>
                <eq property="subject" value="bar" />
                <eq property="texte" value="machine" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">
                    [
                     {"field_id":"subject","field_pattern":"","value":"bar", "operator":"=", "isExpr":false, "dbType":""},
                     {"field_id":"texte","field_pattern":"","value":"machine", "operator":"=", "isExpr":false, "dbType":""}
                     ]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">{"subject":["my text",false]}</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
       
        array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="subject" value="my text" />
            </values>
            <conditions>
                <eq property="subject" pattern="CONCAT(%s, \'b\')" value="bar" />
                <eq property="texte" pattern="LOWER(%s)" value="machine" />
            </conditions>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[
                     {"field_id":"subject","field_pattern":"CONCAT(%s, \'b\')","value":"bar", "operator":"=", "isExpr":false, "dbType":""},
                     {"field_id":"texte","field_pattern":"LOWER(%s)","value":"machine", "operator":"=", "isExpr":false, "dbType":""}
                     ]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">{"subject":["my text",false]}</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="update" eventbefore="true">
            <values>
                <value property="subject" value="my text" />
            </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="true"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">{"subject":["my text",false]}</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        
        array('<?xml version="1.0"?>
          <method name="foo" type="update" eventafter="true">
            <values>
                <value property="subject" value="my text" />
            </values>
          </method>',
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="true"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">{"subject":["my text",false]}</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
    );

    function getMethDatas() {
        return $this->methDatas;
    }

    /**
     * @dataProvider getMethDatas
     */
    function testMethods($xmls, $expected) {
        $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey="news_id" />
    <foreigntable name="news_author" primarykey="author_id" onforeignkey="author_id" />
  </datasources>
  <record>
    <property name="id" fieldname="news_id" datatype="autoincrement" />
    <property name="subject" datatype="string" />
    <property name="texte" datatype="string" />
    <property name="publishdate" datatype="date" />
    <property name="author_firstname" fieldname="firstname" datatype="string" table="news_author" />
    <property name="author_lastname" fieldname="lastname"  datatype="string" table="news_author" />
  </record>
</dao>';

        $parser = new \Jelix\DaoTests\testDaoParser($this->_selector, $this->_context);
        $xml = simplexml_load_string($dao);
        $parser->testParseDatasource($xml);
        $parser->testParseRecord($xml);

        $xml= simplexml_load_string($xmls);
        try{
            $p = new \Jelix\Dao\Parser\DaoMethod($xml, $parser);
            $this->assertComplexIdenticalStr($p, $expected);
        }catch(\Jelix\Dao\Parser\ParserException $e){
            $this->fail("Unexpected Exception: ".$e->getMessage());
        }

    }

    function testMethods2() {
        $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="product" primarykey="product_id" />
  </datasources>
  <record>
    <property name="id" fieldname="product_id" datatype="autoincrement" />
    <property name="price" datatype="float" />
    <property name="price_big" datatype="float" />
  </record>
</dao>';

        $parser = new \Jelix\DaoTests\testDaoParser($this->_selector, $this->_context);
        $xml = simplexml_load_string($dao);
        $parser->testParseDatasource($xml);
        $parser->testParseRecord($xml);

        $xmlMethod = '<?xml version="1.0"?>
          <method name="foo" type="update">
            <parameter name="price" />
            <parameter name="price_big" />
            <values>
                 <value property="price"     expr="$price"     />
                 <value property="price_big" expr="$price_big" />
            </values>
          </method>';
        $result = 
        '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="update"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">["price","price_big"]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">{"price":["$price",true], "price_big":["$price_big",true]}</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>';

        $xml= simplexml_load_string($xmlMethod);
        try{
            $p = new \Jelix\Dao\Parser\DaoMethod($xml, $parser);
            $this->assertComplexIdenticalStr($p, $result);
        }catch(\Jelix\Dao\Parser\ParserException $e){
            $this->fail("Unexpected Exception: ".$e->getMessage());
        }
    }



    protected $badmethDatas=array(
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
          </method>',
          'method "foo" of "update" type, should contains a "value" tag (dao: foo, file: bar)', 543
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value  value="" />
            </values>
          </method>',
          'method "foo",  unknown property "" on a <value> tag (dao: foo, file: bar)', 554
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="plop" value="" />
            </values>
          </method>',
          'method "foo",  unknown property "plop" on a <value> tag (dao: foo, file: bar)', 554
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="author_firstname" value="" />
            </values>
          </method>',
          'method "foo", the property "author_firstname" should be owned by the primary table  (dao: foo, file: bar)', 555
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="id" value="" />
            </values>
          </method>',
          'method "foo", primary key properties like "id" are not allowed in value tag (dao: foo, file: bar)', 556
          ),
      array('<?xml version="1.0"?>
          <method name="foo" type="update">
            <values>
                <value property="subject" value="abc" expr="\'abs\'"/>
            </values>
          </method>',
          'method "foo", value or expression is missing on a value tag (dao: foo, file: bar)', 557
          ),

    );

    function getBadMethodData() {
        return $this->badmethDatas;
    }

    /**
     * @param $xmls
     * @param $errMessage
     * @param $errCode
     * @dataProvider getBadMethodData
     */
    function testBadUpdateMethods($xmls, $errMessage, $errCode) {
        $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey="news_id" />
    <foreigntable name="news_author" primarykey="author_id" onforeignkey="author_id" />
  </datasources>
  <record>
    <property name="id" fieldname="news_id" datatype="autoincrement" />
    <property name="subject" datatype="string" />
    <property name="texte" datatype="string" />
    <property name="publishdate" datatype="date" />
    <property name="author_firstname" fieldname="firstname" datatype="string" table="news_author" />
    <property name="author_lastname" fieldname="lastname"  datatype="string" table="news_author" />
  </record>
</dao>';

        $parser = new \Jelix\DaoTests\testDaoParser($this->_selector, $this->_context);
        $xml = simplexml_load_string($dao);
        $parser->testParseDatasource($xml);
        $parser->testParseRecord($xml);

        $xml= simplexml_load_string($xmls);
        try{
            $p = new \Jelix\Dao\Parser\DaoMethod($xml, $parser);
            $this->fail("no expected exception!");
        }catch(\Jelix\Dao\Parser\ParserException $e){
            $this->assertEquals($errMessage, $e->getMessage());
            $this->assertEquals($errCode, $e->getCode());
        }
    }

   function testBadUpdateMethods2() {
 $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey="news_id,foo_id" />
  </datasources>
  <record>
    <property name="id" fieldname="news_id" datatype="autoincrement" />
    <property name="id2" fieldname="foo_id" datatype="integer" />
  </record>
</dao>';

        $parser = new \Jelix\DaoTests\testDaoParser($this->_selector, $this->_context);
        $xml = simplexml_load_string($dao);
        $parser->testParseDatasource($xml);
        $parser->testParseRecord($xml);

        //$this->sendMessage("test bad update method ");
        $xml= simplexml_load_string('<?xml version="1.0"?>
          <method name="tryupdate" type="update">
            <parameter name="something" />
            <values>
                <value property="foo_id" expr="$something" />
            </values>
          </method>');

        try{
            $p = new \Jelix\Dao\Parser\DaoMethod($xml, $parser);
            $this->fail("no expected exception!");
        }catch(\Jelix\Dao\Parser\ParserException $e){
            $this->assertEquals('update method "tryupdate" is forbidden because the main table contains only primary keys (dao: foo, file: bar)', $e->getMessage());
            $this->assertEquals(564, $e->getCode());
        }
    }
}


