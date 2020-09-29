<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2006-2020 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


class ParsePropertyTest extends \PHPUnit\Framework\TestCase {

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


    protected $propDatas=array(
        array(
            '<?xml version="1.0"?>
        <property name="label" datatype="string" />',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="label"/>
            <string p="fieldName" value="label"/>
            <string p="table" value="news"/>
            <string p="datatype" value="string"/>
            <string p="unifiedType" value="varchar"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="false"/>
            <boolean p="requiredInConditions" value="false"/>
            <boolean p="isPK" value="false" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="%s" />
            <string p="insertPattern" value="%s" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <null p="defaultValue" />
            <boolean p="ofPrimaryTable" value="true" />
        </object>'
        ),
        array(
            '<?xml version="1.0"?>
        <property name="label" datatype="string" default="no label"/>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="label"/>
            <string p="fieldName" value="label"/>
            <string p="table" value="news"/>
            <string p="datatype" value="string"/>
            <string p="unifiedType" value="varchar"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="false"/>
            <boolean p="requiredInConditions" value="false"/>
            <boolean p="isPK" value="false" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="%s" />
            <string p="insertPattern" value="%s" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <string p="defaultValue" value="no label" />
            <boolean p="ofPrimaryTable" value="true" />
        </object>'
        ),
        array(
            '<?xml version="1.0"?>
        <property name="author_firstname" fieldname="firstname" datatype="string" table="news_author" />',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="author_firstname"/>
            <string p="fieldName" value="firstname"/>
            <string p="table" value="news_author"/>
            <string p="datatype" value="string"/>
            <string p="unifiedType" value="varchar"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="false"/>
            <boolean p="requiredInConditions" value="false"/>
            <boolean p="isPK" value="false" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="" />
            <string p="insertPattern" value="" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <null p="defaultValue" />
            <boolean p="ofPrimaryTable" value="false" />
        </object>'
        ),

        array(
            '<?xml version="1.0"?>
        <property name="id" fieldname="news_id" datatype="autoincrement" />',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="id"/>
            <string p="fieldName" value="news_id"/>
            <string p="table" value="news"/>
            <string p="datatype" value="autoincrement"/>
            <string p="unifiedType" value="integer"/>
            <boolean p="autoIncrement" value="true" />
            <null p="regExp"/>
            <boolean p="required" value="false"/>
            <boolean p="requiredInConditions" value="true"/>
            <boolean p="isPK" value="true" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="" />
            <string p="insertPattern" value="%s" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <boolean p="ofPrimaryTable" value="true" />
        </object>'
        ),
        array( '<?xml version="1.0"?>
        <property name="label" datatype="string" selectpattern="%s" insertpattern="" updatepattern=""/>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="label"/>
            <string p="fieldName" value="label"/>
            <string p="table" value="news"/>
            <string p="datatype" value="string"/>
            <string p="unifiedType" value="varchar"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="false"/>
            <boolean p="requiredInConditions" value="false"/>
            <boolean p="isPK" value="false" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="" />
            <string p="insertPattern" value="" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <boolean p="ofPrimaryTable" value="true" />
        </object>'
        ),

        array( '<?xml version="1.0"?>
        <property name="label" datatype="string" selectpattern="CASE WHEN LENGTH(password) = 0 THEN 1 ELSE 0 END" insertpattern="" updatepattern=""/>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="label"/>
            <string p="fieldName" value="label"/>
            <string p="table" value="news"/>
            <string p="datatype" value="string"/>
            <string p="unifiedType" value="varchar"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="false"/>
            <boolean p="requiredInConditions" value="false"/>
            <boolean p="isPK" value="false" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="" />
            <string p="insertPattern" value="" />
            <string p="selectPattern" value="CASE WHEN LENGTH(password) = 0 THEN 1 ELSE 0 END" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <boolean p="ofPrimaryTable" value="true" />
        </object>'
        ),

        array(
            '<?xml version="1.0"?>
        <property name="id" fieldname="news_id" datatype="string" />',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="id"/>
            <string p="fieldName" value="news_id"/>
            <string p="table" value="news"/>
            <string p="datatype" value="string"/>
            <string p="unifiedType" value="varchar"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="true"/>
            <boolean p="requiredInConditions" value="true"/>
            <boolean p="isPK" value="true" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="" />
            <string p="insertPattern" value="%s" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <boolean p="ofPrimaryTable" value="true" />
        </object>'
        ),

        array(
            '<?xml version="1.0"?>
        <property name="id" fieldname="news_id" datatype="string" insertpattern="now()" updatepattern="concat(\'oups\')" selectpattern="upper(%s)"/>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="id"/>
            <string p="fieldName" value="news_id"/>
            <string p="table" value="news"/>
            <string p="datatype" value="string"/>
            <string p="unifiedType" value="varchar"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="true"/>
            <boolean p="requiredInConditions" value="true"/>
            <boolean p="isPK" value="true" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="" />
            <string p="insertPattern" value="now()" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <boolean p="ofPrimaryTable" value="true" />
        </object>'
        ),

        array(
            '<?xml version="1.0"?>
        <property name="author_id" fieldname="author_id" datatype="integer" required="true" updatepattern="now()"/>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="author_id"/>
            <string p="fieldName" value="author_id"/>
            <string p="table" value="news"/>
            <string p="datatype" value="integer"/>
            <string p="unifiedType" value="integer"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="true"/>
            <boolean p="requiredInConditions" value="true"/>
            <boolean p="isPK" value="false" />
            <boolean p="isFK" value="true" />
            <string p="updatePattern" value="%s" />
            <string p="insertPattern" value="%s" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <boolean p="ofPrimaryTable" value="true" />
        </object>'
        ),

        array(
            '<?xml version="1.0"?>
        <property name="author_id" fieldname="author_id" datatype="integer" table="news_author" required="true"/>',
            '<?xml version="1.0"?>
        <object>
            <string p="name" value="author_id"/>
            <string p="fieldName" value="author_id"/>
            <string p="table" value="news_author"/>
            <string p="datatype" value="integer"/>
            <string p="unifiedType" value="integer"/>
            <boolean p="autoIncrement" value="false" />
            <null p="regExp"/>
            <boolean p="required" value="false"/>
            <boolean p="requiredInConditions" value="false"/>
            <boolean p="isPK" value="true" />
            <boolean p="isFK" value="false" />
            <string p="updatePattern" value="" />
            <string p="insertPattern" value="" />
            <string p="selectPattern" value="%s" />
            <string p="sequenceName" value="" />
            <null p="maxlength"/>
            <null p="minlength"/>
            <boolean p="ofPrimaryTable" value="false" />
        </object>'
        ),
    );

    function testProperties() {
        $dao ='<?xml version="1.0"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
  <datasources>
    <primarytable name="news" primarykey="news_id" />
    <foreigntable name="news_author" primarykey="author_id" onforeignkey="author_id" />
  </datasources>
</dao>';

        $parser = new \Jelix\DaoTests\testDaoParser($this->_selector, $this->_context);
        $parser->testParseDatasource(simplexml_load_string($dao));

        foreach($this->propDatas as $k=>$t){
            $xml= simplexml_load_string($t[0]);
            try{
                $p = new \Jelix\Dao\Parser\DaoProperty($xml, $parser);
                $this->assertComplexIdenticalStr($p, $t[1], "test $k");
            }catch(\Jelix\Dao\Parser\ParserException $e){
                $this->fail("Exception sur le contenu xml inattendue (item $k) : ".$e->getMessage().' ('.$e->getCode().')');
            }
        }
    }
}
