<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2006-2020 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


class ParseMethodTest extends \PHPUnit\Framework\TestCase {


    use \Jelix\UnitTests\AssertComplexTrait;
    protected $_selector;
    protected $_context;

    protected function setUp(): void
    {
        $this->_selector = new \Jelix\DaoTests\FileTest("foo", "bar", "baz");
        $this->_context = new \Jelix\DaoTests\ContextTest("sqlite");
    }

    protected function tearDown(): void
    {
        $this->_selector = null;
        $this->_context = null;
    }


    protected $methDatas=array(
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <order>
                <orderitem property="publishdate" way="desc"/>
            </order>
          </method>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                </object>
                <array p="order">{"publishdate":"desc"}</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">[]</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <parameter name="aWay" />
            <order>
                <orderitem property="publishdate" way="$aWay"/>
            </order>
          </method>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">[]</array>
                </object>
                <array p="order">{"publishdate":"$aWay"}</array>
            </object>
            <array m="getParameters ()">["aWay"]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">[]</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <limit offset="10" count="5" />
          </method>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
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
            <array m="getLimit ()">{"offset":10, "count":5, "offsetparam":false,"countparam":false}</array>
            <array m="getValues ()">[]</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <parameter name="aOffset" />
            <parameter name="aCount" />
            <limit offset="$aOffset" count="$aCount" />
          </method>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
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
            <array m="getParameters ()">["aOffset","aCount"]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <array m="getLimit ()">{"offset":"$aOffset", "count":"$aCount", "offsetparam":true,"countparam":true}</array>
            <array m="getValues ()">[]</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <conditions>
                <eq property="subject" value="bar" />
                <eq property="texte" value="machine" />
            </conditions>
          </method>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[
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
            <array m="getValues ()">[]</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="select" distinct="true">
            <conditions logic="or">
                <eq property="subject" value="bar" />
                <eq property="texte" value="machine" />
            </conditions>
          </method>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="true"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[
                     {"field_id":"subject","field_pattern":"","value":"bar", "operator":"=", "isExpr":false, "dbType":""},
                     {"field_id":"texte","field_pattern":"","value":"machine", "operator":"=", "isExpr":false, "dbType":""}
                     ]
                     </array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="OR"/>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">[]</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),


        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <conditions logic="or">
                <conditions>
                    <eq property="subject" value="bar" />
                    <eq property="texte" value="machine" />
                </conditions>
                <conditions>
                    <eq property="subject" value="bar2" />
                    <conditions logic="or">
                        <eq property="texte" value="machine2" />
                        <eq property="texte" value="truc" />
                    </conditions>
                </conditions>
            </conditions>
          </method>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions">[]</array>
                    <array p="group">
                        <object p="condition" class="\Jelix\Dao\DaoCondition">
                            <notnull p="parent" />
                            <array p="conditions">
                            [
                             {"field_id":"subject","field_pattern":"","value":"bar", "operator":"=", "isExpr":false, "dbType":""},
                             {"field_id":"texte","field_pattern":"","value":"machine", "operator":"=", "isExpr":false, "dbType":""}
                             ]</array>
                            <array p="group">[]</array>
                            <string p="glueOp" value="AND"/>
                        </object>
                        <object p="condition" class="\Jelix\Dao\DaoCondition">
                            <object p="parent" class="\Jelix\Dao\DaoCondition" />
                            <array p="conditions">[
                                {"field_id":"subject","field_pattern":"","value":"bar2", "operator":"=", "isExpr":false, "dbType":""}
                            ]</array>
                            <array p="group">
                                <object p="condition" class="\Jelix\Dao\DaoCondition">
                                    <notnull p="parent" />
                                    <array p="conditions">
                                    [
                                     {"field_id":"texte","field_pattern":"","value":"machine2", "operator":"=", "isExpr":false, "dbType":""},
                                     {"field_id":"texte","field_pattern":"","value":"truc", "operator":"=", "isExpr":false, "dbType":""}
                                     ]</array>
                                    <array p="group">[]</array>
                                    <string p="glueOp" value="OR"/>
                                </object>
                            </array>
                            <string p="glueOp" value="AND"/>
                        </object>
                    </array>
                    <string p="glueOp" value="OR"/>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">[]</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <conditions>
                <eq property="subject" value="" />
                <eq property="texte" expr="\'machine\'" />
            </conditions>
          </method>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="foo"/>
            <string p="type" value="select"/>
            <boolean p="distinct" value="false"/>
            <boolean p="eventBeforeEnabled" value="false"/>
            <boolean p="eventAfterEnabled" value="false"/>
            <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                <object p="condition" class="\Jelix\Dao\DaoCondition">
                    <null p="parent" />
                    <array p="conditions"> [
                             {"field_id":"subject","field_pattern":"","value":"", "operator":"=", "isExpr":false, "dbType":""},
                             {"field_id":"texte","field_pattern":"","value":"\'machine\'", "operator":"=", "isExpr":true, "dbType":""}
                             ]</array>
                    <array p="group">[]</array>
                    <string p="glueOp" value="AND"/>
                </object>
                <array p="order">[]</array>
            </object>
            <array m="getParameters ()">[]</array>
            <array m="getParametersDefaultValues ()">[]</array>
            <null m="getLimit ()"/>
            <array m="getValues ()">[]</array>
            <null m="getProcStock ()"/>
            <null m="getBody ()"/>
        </object>'),

    );


    function getMethodData() {
        return $this->methDatas;
    }

    /**
     * @dataProvider getMethodData
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
        $doc = simplexml_load_string($dao);
        $parser->testParseDatasource($doc);
        $parser->testParseRecord($doc);

        $xml= simplexml_load_string($xmls);
        try {
            $p = new \Jelix\Dao\Parser\DaoMethod($xml, $parser);
            $this->assertComplexIdenticalStr($p, $expected);
        }catch(\Jelix\Dao\Parser\ParserException $e){
            $this->fail("unexpected exception: ".$e->getMessage());
        }
    }



    protected $badmethDatas=array(
        array('<?xml version="1.0"?>
          <method name="foo" type="select">
            <parameter name="aWay" />
            <order>
                <orderitem property="publishdate" way="$afoo"/>
            </order>
          </method>',
            'method "foo",  unknown parameter "$afoo" in the orderitem tag (dao: foo, file: bar)',
            563
        ),

    );

    function getBadMethodData() {
        return $this->badmethDatas;
    }

    /**
     * @dataProvider getBadMethodData
     * @param $xmls
     * @param $localeKey
     * @param $localeParameters
     */
    function testBadMethods($xmls, $errMessage, $errCode) {
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
        $doc = simplexml_load_string($dao);
        $parser->testParseDatasource($doc);
        $parser->testParseRecord($doc);

        //$this->sendMessage("test bad method ".$k);
        $xml= simplexml_load_string($xmls);
        try{
            $p = new \Jelix\Dao\Parser\DaoMethod($xml, $parser);
            $this->fail("No expected exception!");
        }catch(\Jelix\Dao\Parser\ParserException $e){
            $this->assertEquals($errMessage, $e->getMessage());
            $this->assertEquals($errCode, $e->getCode());
        }

    }

}
