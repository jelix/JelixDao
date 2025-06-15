<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2011-2022 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Dao\DaoSimpleFile;
use Jelix\Dao\Generator\Compiler;
use Jelix\Dao\Parser\DaoTable;

class jDaoImportTest extends \Jelix\UnitTests\UnitTestCaseDb {

    use \Jelix\UnitTests\AssertComplexTrait;
    
    /** @var \Jelix\Dao\DaoLoader  */
    protected $daoLoader;

    protected $tempPath;

    protected $daosDirectory;


    public function setUp () : void
    {
        $this->tempPath = __DIR__.'/../tmp/mainapi/';
        $this->daosDirectory = __DIR__.'/../lib/daos/';

        $this->daoLoader = new \Jelix\Dao\DaoLoader(
            new \Jelix\Dao\Context(
                $this->getConnection(),
                $this->tempPath,
                $this->daosDirectory
            )
        );
    }

    function tearDown() : void  {

    }

    protected function getConnection()
    {
        if (! self::$connection) {
            self::$connection = self::getConnector();
        }
        return self::$connection;
    }

    protected static function getConnector($prefix = '')
    {
        $accessParameters = new \Jelix\Database\AccessParameters(
            array(
                'driver'=>'mysqli',
                'host'=>'mysql',
                'database'=>"jelixtests",
                "user"=>"jelix",
                "password"=>"jelixpass",
                "table_prefix" => $prefix
            ),
            array('charset'=>'UTF-8')
        );

        $connector = \Jelix\Database\Connection::create($accessParameters);
        return $connector;
    }

    public function testExtendedRecords() {
        $post = $this->daoLoader->createRecord('posts');
        $blogPost = $this->daoLoader->createRecord('post_blog');
        $trackerPost = $this->daoLoader->createRecord('post_tracker');

        $this->assertInstanceOf('post', $post);
        $this->assertInstanceOf('post', $blogPost);
        $this->assertInstanceOf('post', $trackerPost);
        $this->assertInstanceOf('postBlog', $blogPost);
        $this->assertInstanceOf('postTracker', $trackerPost);

        $postDao = $this->daoLoader->create('posts');
        $blogPostDao = $this->daoLoader->create('post_blog');
        $trackerPostDao = $this->daoLoader->create('post_tracker');

        $post = $postDao->createRecord();
        $blogPost = $blogPostDao->createRecord();
        $trackerPost = $trackerPostDao->createRecord();

        $this->assertInstanceOf('post', $post);
        $this->assertInstanceOf('post', $blogPost);
        $this->assertInstanceOf('post', $trackerPost);
        $this->assertInstanceOf('postBlog', $blogPost);
        $this->assertInstanceOf('postTracker', $trackerPost);
    }

    public function testExtendedFactory() {

        $postDao = $this->daoLoader->create('posts');
        $blogPostDao = $this->daoLoader->create('post_blog');
        $this->assertInstanceOf('\Jelix\Dao\AbstractDaoFactory', $postDao);
        $this->assertInstanceOf('\Jelix\Dao\AbstractDaoFactory', $blogPostDao);
        $this->assertInstanceOf('\Jelix\DaoTests\CustomPostBlogFactory', $blogPostDao);

        $this->assertTrue(method_exists($blogPostDao, 'getByEmail'));
    }

    public function testImportedEvents()
    {

        $postSel = new DaoSimpleFile("posts", $this->daosDirectory.'posts.xml', "sqlite", $this->tempPath);
        $blogSel = new DaoSimpleFile("post_blog", $this->daosDirectory.'post_blog.xml', "sqlite", $this->tempPath);
        $trackerSel = new DaoSimpleFile("post_tracker", $this->daosDirectory.'post_tracker.xml', "sqlite", $this->tempPath);

        $context = new \Jelix\DaoTests\ContextForTest("sqlite");

        $compiler = new Compiler();

        $postParser =  $compiler->parse($postSel, $context);
        $this->assertEquals(array('deletebefore'), $postParser->getEvents());

        $blogParser = $compiler->parse($blogSel, $context);
        $this->assertEquals(array('deletebefore'), $blogParser->getEvents());

        $trackerParser = $compiler->parse($trackerSel, $context);
        $this->assertEquals(array('deletebefore', 'insertbefore', 'updatebefore'), $trackerParser->getEvents());
    }

    public function testImportWithRedefinedMethods()
    {
        $trackerSel = new DaoSimpleFile("post_tracker", $this->daosDirectory.'post_tracker.xml', "sqlite", $this->tempPath);

        $context = new \Jelix\DaoTests\ContextForTest("sqlite");
        $compiler = new Compiler();

        $postTrackerParser = $compiler->parse($trackerSel, $context);

        $postTable = new DaoTable('posts',
            'posts', array('id'), DaoTable::TYPE_PRIMARY);
        $postTable->fields = array('id', 'title', 'author', 'content', 'type', 'status', 'date');

        $this->assertEquals(
            array(
                'posts'=> $postTable
            ),
            $postTrackerParser->getTables());
        $properties = '<?xml version="1.0"?>
        <array>
            <object key="id" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="id"/>
                <string p="fieldName" value="id"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="int"/>
                <string p="unifiedType" value="integer"/>
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
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="title" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="title"/>
                <string p="fieldName" value="title"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="245"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="author" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="author"/>
                <string p="fieldName" value="author"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
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
                <integer p="maxlength" value="50"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="content" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="content"/>
                <string p="fieldName" value="content"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="longtext"/>
                <string p="unifiedType" value="text"/>
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
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="type" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="type"/>
                <string p="fieldName" value="type"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="32"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="status" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="status"/>
                <string p="fieldName" value="status"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
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
                <integer p="maxlength" value="15"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="date" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="date"/>
                <string p="fieldName" value="date"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="datetime"/>
                <string p="unifiedType" value="datetime"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
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
            </object>
        </array>
        ';

        $this->assertComplexIdenticalStr($postTrackerParser->getProperties(), $properties);
        $this->assertEquals('posts',
                            $postTrackerParser->getPrimaryTable());
        /*
             <object key="countOpenPattern" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="countOpenPattern"/>
                <string p="type" value="count"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>{"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : ""}
                            </array>
                           <array>
                            {"field_id" : "status",
                            "value" : "open",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "LOWER(%s)"}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>

            <object key="findOpenPattern" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="findOpenPattern"/>
                <string p="type" value="select"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>
                            {"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : ""}
                            </array>
                           <array>
                            {"field_id" : "status",
                            "value" : "open",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "LOWER(%s)"}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
        */
        $methods = '<?xml version="1.0"?>
        <array>
            <object key="findAll" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="findAll"/>
                <string p="type" value="select"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>
                            {"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
            <object key="countOpen" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="countOpen"/>
                <string p="type" value="count"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>
                            {"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                           <array>
                            {"field_id" : "status",
                            "value" : "open",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
            
            <object key="findOpen" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="findOpen"/>
                <string p="type" value="select"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>
                            {"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                           <array>{
                            "field_id" : "status",
                            "value" : "open",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
            
        </array>';
        $this->assertComplexIdenticalStr($postTrackerParser->getMethods(), $methods);
        $this->assertEquals(array(),
                            $postTrackerParser->getOuterJoins());
        $this->assertEquals(array(),
                            $postTrackerParser->getInnerJoins());
        $this->assertEquals('postTracker',
                            $postTrackerParser->getCustomRecord()->getClassName());
        $daos = $postTrackerParser->getImportedDao();
        $this->assertEquals('posts',  $daos[0]->getName());
    }

    public function testImportWithRedefinedProperties() {
        $this->launchTestImportWithRedefinedProperties('post_blog');
    }

    public function testImportWithRedefinedPropertiesAndTable() {
        // with a dao that redeclare the table
        $this->launchTestImportWithRedefinedProperties('post_blog2');
    }

    protected function launchTestImportWithRedefinedProperties($daoName)
    {
        $postSel = new DaoSimpleFile("posts", $this->daosDirectory.'posts.xml', "sqlite", $this->tempPath);
        $blogSel = new DaoSimpleFile($daoName, $this->daosDirectory.$daoName.'.xml', "sqlite", $this->tempPath);

        $context = new \Jelix\DaoTests\ContextForTest("sqlite");
        $compiler = new Compiler();

        $postBlogParser = $compiler->parse($blogSel, $context);

        $postTable = new DaoTable('posts', 'posts', array('id'), DaoTable::TYPE_PRIMARY);
        $postTable->fields = array('id', 'title', 'author', 'content', 'type', 'status', 'date', 'email');

        $this->assertEquals(
            array(
                'posts'=> $postTable
            ),
            $postBlogParser->getTables());

        $properties = '<?xml version="1.0"?>
        <array>
            <object key="id" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="id"/>
                <string p="fieldName" value="id"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="int"/>
                <string p="unifiedType" value="integer"/>
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
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="title" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="title"/>
                <string p="fieldName" value="title"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="245"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="author" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="author"/>
                <string p="fieldName" value="author"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
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
                <integer p="maxlength" value="100"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="email" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="email"/>
                <string p="fieldName" value="email"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
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
                <integer p="maxlength" value="120"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="content" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="content"/>
                <string p="fieldName" value="content"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="longtext"/>
                <string p="unifiedType" value="text"/>
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
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="type" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="type"/>
                <string p="fieldName" value="type"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="32"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="status" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="status"/>
                <string p="fieldName" value="status"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
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
                <integer p="maxlength" value="15"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="date" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="date"/>
                <string p="fieldName" value="date"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="datetime"/>
                <string p="unifiedType" value="datetime"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
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
            </object>
        </array>
        ';

        $this->assertComplexIdenticalStr($postBlogParser->getProperties(), $properties);
        $this->assertEquals('posts',
                            $postBlogParser->getPrimaryTable());

        $this->assertEquals(array(), $postBlogParser->getMethods());
        $this->assertEquals(array(),
                            $postBlogParser->getOuterJoins());
        $this->assertEquals(array(),
                            $postBlogParser->getInnerJoins());
        $this->assertEquals('postBlog',
                            $postBlogParser->getCustomRecord()->getClassName());
        $daos = $postBlogParser->getImportedDao();
        $this->assertEquals('posts', $daos[0]->getName());
    }
}
