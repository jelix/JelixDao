<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2021 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

use Jelix\Database\AccessParameters;
use Jelix\Database\Connection;
use Jelix\Dao\AbstractDaoRecord;
use Jelix\Dao\DaoConditions;

/**
 * CAREFULL ! DON'T CHANGE THE ORDER OF METHODS
 */
abstract class MainApiAbstract extends \Jelix\UnitTests\UnitTestCaseDb
{
    use \Jelix\UnitTests\AssertComplexTrait;

    static protected $profile;
    static protected $trueValue = 1;
    static protected $falseValue = 0;

    static protected $productIdType = 'string';
    static protected $productPriceType = 'string';
    static protected $productPromoType = 'string';

    /** @var \Jelix\Dao\DaoLoader  */
    protected $daoLoader;
    protected static $conn = array();
    protected $sqlType;

    protected $jsonSpace = ' ';

    function setUp() : void
    {
        $tempPath = __DIR__.'/../tmp/mainapi/';
        $daosDirectory = __DIR__.'/../lib/daos/';

        $this->daoLoader = new \Jelix\Dao\DaoLoader(
            new \Jelix\Dao\Context(
                $this->getConnection(),
                $tempPath,
                $daosDirectory
            )
        );

        $this->sqlType = ucfirst($this->getConnection()->getSQLType());
        static::$productIdType = 'string';
        static::$productPriceType = 'string';
        static::$productPromoType = 'string';
    }

    function tearDown() : void  {

    }

    abstract protected function getConnector();

    protected function getConnection()
    {
        $class = get_class($this);
        if (!isset(self::$conn[$class]) || ! self::$conn[$class]) {
            self::$conn[$class] = $this->getConnector();
        }
        self::$connection = self::$conn[$class];
        return self::$conn[$class];
    }


    function testInstanciation() {
        $dao = $this->daoLoader->create ('products');
        $this->assertInstanceOf('Products'.$this->sqlType.'Factory', $dao);

        $dao = $this->daoLoader->get ('products');
        $this->assertInstanceOf('Products'.$this->sqlType.'Factory', $dao);

        $daorec = $this->daoLoader->createRecord ('products');
        $this->assertInstanceOf('Products'.$this->sqlType.'Record', $daorec);

        $daorec = $dao->createRecord();
        $this->assertInstanceOf('Products'.$this->sqlType.'Record', $daorec);

        $dao = $this->daoLoader->create ('article');
        $this->assertInstanceOf('Article'.$this->sqlType.'Factory', $dao);

    }

    /**
     * @depends testInstanciation
     */
    function testFindAllEmpty() {
        $this->emptyTable('products');
        $dao = $this->daoLoader->create ('products');
        $res = $dao->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(0, count($list), 'findAll doesn\'t return an empty list');
        $this->assertEquals(0, $dao->countAll(), 'countAll doesn\'t return 0');
    }

    protected static $prod1;
    protected static $prod2;
    protected static $prod3;
    protected static $art;

    /**
     * @depends testFindAllEmpty
     */
    function testInsert() {
        $dao = $this->daoLoader->create ('products');

        self::$prod1 = $this->daoLoader->createRecord ('products');
        self::$prod1->name ='assiette';
        self::$prod1->price = 3.87;
        self::$prod1->promo = false;
        self::$prod1->metadata = ['tears'=>'for fears'];
        $res = $dao->insert(self::$prod1);

        $this->assertEquals(1, $res, 'AbstractDaoFactory::insert does not return 1');
        $this->assertNotEquals('', self::$prod1->id, 'AbstractDaoFactory::insert : id not set');
        $this->assertNotEquals('', self::$prod1->create_date, 'AbstractDaoFactory::insert : create_date not updated');

        self::$prod2 = $dao->createRecord();
        self::$prod2->name ='fourchette';
        self::$prod2->price = 1.54;
        self::$prod2->promo = true;
        self::$prod2->dummy = 'started';
        self::$prod2->metadata = ['simple'=>'mind'];
        $res = self::$prod2->save();

        $this->assertEquals(1, $res, 'AbstractDaoFactory::insert does not return 1');
        $this->assertNotEquals('', self::$prod2->id, 'AbstractDaoFactory::insert : id not set');
        $this->assertNotEquals('', self::$prod2->create_date, 'AbstractDaoFactory::insert : create_date not updated');

        self::$prod3 = $this->daoLoader->createRecord ('products');
        self::$prod3->name ='verre';
        self::$prod3->price = 2.43;
        self::$prod3->promo = false;
        $res = self::$prod3->save();

        $this->assertEquals(1, $res, 'AbstractDaoFactory::insert does not return 1');
        $this->assertNotEquals('', self::$prod3->id, 'AbstractDaoFactory::insert : id not set');
        $this->assertNotEquals('', self::$prod3->create_date, 'AbstractDaoFactory::insert : create_date not updated');

        $records = array(
            array('id'=>self::$prod1->id,
                'name'=>'assiette',
                'price'=>3.87,
                'promo'=> static::$falseValue,
                'metadata' => '{"tears":'.$this->jsonSpace.'"for fears"}',
            ),
            array('id'=>self::$prod2->id,
                'name'=>'fourchette',
                'price'=>1.54,
                'promo'=>static::$trueValue,
                'metadata' => '{"simple":'.$this->jsonSpace.'"mind"}',
            ),
            array('id'=>self::$prod3->id,
                'name'=>'verre',
                'price'=>2.43,
                'promo'=>static::$falseValue,
                'metadata' => null
            ),
        );
        $this->assertTableContainsRecords('products', $records);

        $dao = $this->daoLoader->create ('article');

        self::$art = $this->daoLoader->createRecord ('article');
        self::$art->title ='first news';
        self::$art->content = 'lorem ipsum';
        $res = $dao->insert(self::$art);

        $this->assertEquals(1, $res, 'AbstractDaoFactory::insert does not return 1');
        $this->assertNotEquals('', self::$art->id, 'AbstractDaoFactory::insert : id not set');

    }

    /**
     * @depends testInsert
     */
    function testGet() {
        $dao = $this->daoLoader->create ('products');

        $prod = $dao->get(self::$prod1->id);
        $this->assertInstanceOf('\\Jelix\\Dao\\AbstractDaoRecord', $prod,'DaoLoader::get doesn\'t return a AbstractDaoRecord object');
        $this->assertEquals(self::$prod1->id, $prod->id, 'DaoLoader::get : bad id on record');
        $this->assertEquals('assiette', $prod->name,'DaoLoader::get : bad name property on record');
        $this->assertEquals(3.87, $prod->price,'DaoLoader::get : bad price property on record');
        $this->assertEquals(static::$falseValue, $prod->promo,'DaoLoader::get : bad promo property on record');
        $this->assertEquals( ['tears'=>'for fears'], $prod->metadata);

        $dao = $this->daoLoader->create ('article');
        $art = $dao->get(self::$art->id);
        $this->assertInstanceOf('\\Jelix\\Dao\\AbstractDaoRecord', $art,'DaoLoader::get doesn\'t return a AbstractDaoRecord object');
        $this->assertEquals(self::$art->id, $art->id, 'DaoLoader::get : bad id on record');
        $this->assertEquals('first news', $art->title, 'DaoLoader::get : bad name property on record');
    }

    /**
     * @depends testGet
     */
    function testUpdate(){
        $dao = $this->daoLoader->create ('products');
        $prod = $this->daoLoader->createRecord ('products');
        $prod->name ='assiette nouvelle';
        $prod->price = 5.90;
        $prod->promo = true;
        $prod->id = self::$prod1->id;

        $dao->update($prod);

        $prod2 = $dao->get(self::$prod1->id);
        $this->assertInstanceOf('\\Jelix\\Dao\\AbstractDaoRecord', $prod2,'DaoLoader::get doesn\'t return a AbstractDaoRecord object');
        $this->assertEquals(self::$prod1->id, $prod2->id, 'DaoLoader::get : bad id on record');
        $this->assertEquals('assiette nouvelle', $prod2->name,'DaoLoader::get : bad name property on record');
        $this->assertEquals(5.90, $prod2->price,'DaoLoader::get : bad price property on record');
        $this->assertEquals(static::$trueValue, $prod2->promo,'DaoLoader::get : bad promo property on record');
        
        $prod->promo = 't';
        $prod->save();
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$trueValue, $prod2->promo,'DaoLoader::get : bad promo property on record : %');
        
        $prod->promo = 'f';
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$falseValue, $prod2->promo,'DaoLoader::get : bad promo property on record : %');

        $prod->promo = false;
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$falseValue, $prod2->promo,'DaoLoader::get : bad promo property on record : %');

        $prod->promo = 'true';
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$trueValue, $prod2->promo,'DaoLoader::get : bad promo property on record : %');

        $prod->promo = 'on';
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$trueValue, $prod2->promo,'DaoLoader::get : bad promo property on record : %');

        $prod->promo = 'false';
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$falseValue, $prod2->promo,'DaoLoader::get : bad promo property on record : %');

        $prod->promo = 0;
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$falseValue, $prod2->promo,'DaoLoader::get : bad promo property on record : '.var_export($prod2->promo,true).' ');

        $prod->promo = 1;
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$trueValue, $prod2->promo,'DaoLoader::get : bad promo property on record : %');

        $prod->promo = '0';
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$falseValue, $prod2->promo,'DaoLoader::get : bad promo property on record : %');

        $prod->promo = '1';
        $dao->update($prod);
        $prod2 = $dao->get(self::$prod1->id);
        $this->assertEquals(static::$trueValue, $prod2->promo,'$this->daoLoader->get : bad promo property on record : %');

    }

    /**
     * @depends testUpdate
     */
    function testFindAllNotEmpty() {
        $dao = $this->daoLoader->create ('products');

        $res = $dao->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(3, count($list), 'findAll doesn\'t return all products. %s ');
        $this->assertEquals(3, $dao->countAll(), 'countAll doesn\'t return 3');
        usort($list, function($itemA, $itemB) {
            if ($itemA->id > $itemB->id) {
                return 1;
            }
            if ($itemA->id == $itemB->id) {
                return 0;
            }
            return -1;
        });
    $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod1->id.'" />
        <string property="name" value="assiette nouvelle" />
        <'.static::$productPriceType.' property="price" value="5.9" />
        <'.static::$productPromoType.' property="promo" value="'.static::$trueValue.'" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod2->id.'" />
        <string property="name" value="fourchette" />
        <'.static::$productPriceType.' property="price" value="1.54" />
        <'.static::$productPromoType.' property="promo" value="'.static::$trueValue.'" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod3->id.'" />
        <string property="name" value="verre" />
        <'.static::$productPriceType.' property="price" value="2.43" />
        <'.static::$productPromoType.' property="promo" value="'.static::$falseValue.'" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);

        $dao = $this->daoLoader->create ('article');

        $res = $dao->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(1, count($list), 'findAll doesn\'t return all articles. %s ');
        $this->assertEquals(1, $dao->countAll(), 'countAll doesn\'t return 1');
    }

    /**
     * @depends testFindAllNotEmpty
     */
    function testEqualityOnValue() {
        $dao = $this->daoLoader->create ('products');

        $res = $dao->findFourchette();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(1, count($list), 'findFourchette doesn\'t return one record. %s ');

    $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod2->id.'" />
        <string property="name" value="fourchette" />
        <'.static::$productPriceType.' property="price" value="1.54" />
        <'.static::$productPromoType.' property="promo" value="'.static::$trueValue.'" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);


        $res = $dao->findStarted();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(1, count($list), 'findStarted doesn\'t return one record. %s ');

    $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod2->id.'" />
        <string property="name" value="fourchette" />
        <'.static::$productPriceType.' property="price" value="1.54" />
        <'.static::$productPromoType.' property="promo" value="'.static::$trueValue.'" />
        <string property="dummy" value="started" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    /**
     * @depends testEqualityOnValue
     */
    function testFindByCountByOrder(){
        $dao = $this->daoLoader->create ('products');

        $conditions = new DaoConditions();
        $conditions->addItemOrder('id','DESC');

        $count = $dao->countBy($conditions);
        $this->assertEquals(3, $count, 'countBy: %s');

        $res = $dao->findBy($conditions);
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(3, count($list), 'findBy doesn\'t return all products. %s ');

        $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod3->id.'" />
        <string property="name" value="verre" />
        <'.static::$productPriceType.' property="price" value="2.43" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod2->id.'" />
        <string property="name" value="fourchette" />
        <'.static::$productPriceType.' property="price" value="1.54" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod1->id.'" />
        <string property="name" value="assiette nouvelle" />
        <'.static::$productPriceType.' property="price" value="5.9" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    /**
     * @depends testFindByCountByOrder
     */
    function testFindByCountByConditionsOrder(){
        $dao = $this->daoLoader->create ('products');

        $conditions = new DaoConditions();
        $conditions->addItemOrder('id','DESC');
        $conditions->addCondition ('id', '>=', self::$prod2->id);

        $count = $dao->countBy($conditions);
        $this->assertEquals(2, $count, 'countBy: %s');

        $res = $dao->findBy($conditions);
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(2, count($list), 'findBy doesn\'t return all products. %s ');

        $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod3->id.'" />
        <string property="name" value="verre" />
        <'.static::$productPriceType.' property="price" value="2.43" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod2->id.'" />
        <string property="name" value="fourchette" />
        <'.static::$productPriceType.' property="price" value="1.54" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    /**
     * @depends testFindByCountByConditionsOrder
     */
    function testFindWithIn(){
        $dao = $this->daoLoader->create ('products');
        $res = $dao->findBySomeNames();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(1, count($list), 'findBySomeNames doesn\'t return default product. %s ');
        $this->assertEquals($list[0]->id, self::$prod2->id);
        $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod2->id.'" />
        <string property="name" value="fourchette" />
        <'.static::$productPriceType.' property="price" value="1.54" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);

        $res = $dao->findBySomeNames(array('verre', 'assiette nouvelle'));
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        usort($list, function($itemA, $itemB) {
            if ($itemA->id > $itemB->id) {
                return 1;
            }
            if ($itemA->id == $itemB->id) {
                return 0;
            }
            return -1;
        });
        $this->assertEquals(2, count($list), 'findBySomeNames doesn\'t return selected products. %s ');
        $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod1->id.'" />
        <string property="name" value="assiette nouvelle" />
        <'.static::$productPriceType.' property="price" value="5.9" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod3->id.'" />
        <string property="name" value="verre" />
        <'.static::$productPriceType.' property="price" value="2.43" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    /**
     * @depends testFindWithIn
     */
    function testDelete(){
        $dao = $this->daoLoader->create ('products');
        $dao->delete(self::$prod1->id);
        $this->assertEquals(2, $dao->countAll(), 'countAll doesn\'t return 2');

        $records = array(
            array('id'=>self::$prod2->id,
            'name'=>'fourchette',
            'price'=>1.54),
            array('id'=>self::$prod3->id,
            'name'=>'verre',
            'price'=>2.43),
        );
        $this->assertTableContainsRecords('products', $records);


        $res = $dao->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(2, count($list), 'findAll doesn\'t return all products. %s ');

    $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod2->id.'" />
        <string property="name" value="fourchette" />
        <'.static::$productPriceType.' property="price" value="1.54" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod3->id.'" />
        <string property="name" value="verre" />
        <'.static::$productPriceType.' property="price" value="2.43" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);

        $dao = $this->daoLoader->create ('article');
        $dao->delete(self::$art->id);
        $this->assertEquals(0, $dao->countAll(), 'countAll doesn\'t return 0');
    }

    /**
     * @depends testDelete
     */
    function testDeleteBy(){
        $dao = $this->daoLoader->create ('products');

        $conditions = new DaoConditions();
        $conditions->addCondition ('id', '=', self::$prod2->id);

        $dao->deleteBy($conditions);
        $this->assertEquals(1, $dao->countAll(), 'countAll doesn\'t return 1');

        $records = array(
            array('id'=>self::$prod3->id,
            'name'=>'verre',
            'price'=>2.43),
        );
        $this->assertTableContainsRecords('products', $records);

        $res = $dao->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(1, count($list), 'findAll doesn\'t return all products. %s ');

    $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.self::$prod3->id.'" />
        <string property="name" value="verre" />
        <'.static::$productPriceType.' property="price" value="2.43" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }

    function testRecordCheck() {

        $record = $this->daoLoader->createRecord ('products');
        $this->assertEquals('', $record->id);
        $record->setPk(5);
        $this->assertEquals(5, $record->id);

        $this->assertEquals(5, $record->getPk());
 
        $record = $this->daoLoader->createRecord ('description');
        $this->assertEquals('', $record->id);
        $this->assertEquals('fr', $record->lang);

        $record->setPk(5,'es');
        $this->assertEquals(5, $record->id);
        $this->assertEquals('es', $record->lang);

        $record->setPk(array(4,'en'));
        $this->assertEquals(4, $record->id);
        $this->assertEquals('en', $record->lang);

        $pk = $record->getPk();
        $this->assertEquals(array(4,'en'), $pk);
    }

    function testErrorCheck() {

        $record = $this->daoLoader->createRecord('products');
        $check = $record->check();
        $expected = array('name'=>array(AbstractDaoRecord::ERROR_REQUIRED));
        $this->assertEquals($expected,$check);

        $record->name = 'Foo';
        $check = $record->check();
        $this->assertFalse($check);

        $record->create_date = 'foo';
        $check = $record->check();
        $expected = array('create_date'=>array(AbstractDaoRecord::ERROR_BAD_FORMAT));
        $this->assertEquals($expected, $check);

        $record->create_date = '2008-02-15';
        $check = $record->check();
        $expected = array('create_date'=>array(AbstractDaoRecord::ERROR_BAD_FORMAT));
        $this->assertEquals($expected,$check);

        $record->create_date = '2008-02-15 12:03:34';
        $check = $record->check();
        $this->assertFalse($check);

        $record->price='foo';
        $check = $record->check();
        $expected = array('price'=>array(AbstractDaoRecord::ERROR_BAD_TYPE));
        $this->assertEquals($expected,$check);

        $record->price=56;
        $check = $record->check();
        $this->assertFalse($check);
    }

    function testBinaryField() {

        $this->emptyTable('jsessions');

        $dao = $this->daoLoader->create ('jsession');

        $sess1 = $dao->createRecord();
        $sess1->id ='sess_02939873A32B';
        $sess1->creation = '2010-02-09 10:28';
        $sess1->access = '2010-02-09 11:00';
        $sess1->data = chr(0).chr(254).chr(1);

        $res = $dao->insert($sess1);
        $this->assertEquals(1, $res, 'AbstractDaoFactory::insert does not return 1');

        $sess2 = $dao->get('sess_02939873A32B');
        $this->assertEquals($sess1->id, $sess2->id, 'DaoLoader::get : bad id on record');
        $this->assertEquals(bin2hex($sess1->data), bin2hex($sess2->data), 'DaoLoader::get : bad binary value on record');
    }

    function testFindAllWithJoin()
    {
        $this->emptyTable('products');
        $this->emptyTable('products_tags');

        $dao = $this->daoLoader->create('products');
        $p1 = $dao->createRecord();
        $p1->name = 'Test 1';
        $p1->price = '1.00';
        $dao->insert($p1);

        $p2 = $dao->createRecord();
        $p2->name = 'Test 2';
        $p2->price = '2.00';
        $dao->insert($p2);

        $p3 = $dao->createRecord();
        $p3->name = 'Test 3';
        $p3->price = '3.00';
        $dao->insert($p3);

        $daoTag = $this->daoLoader->create('products_tags');
        $tag = $daoTag->createRecord();
        $tag->id = $p1->id;
        $tag->tag = 'tag1';
        $daoTag->insert($tag);

        $tag = $daoTag->createRecord();
        $tag->id = $p1->id;
        $tag->tag = 'tag2';
        $daoTag->insert($tag);

        $tag = $daoTag->createRecord();
        $tag->id = $p2->id;
        $tag->tag = 'tag3';
        $daoTag->insert($tag);

        $res = $daoTag->findAll();
        $list = array();
        foreach($res as $r){
            $list[] = $r;
        }
        $this->assertEquals(3, count($list), 'findAll doesn\'t return all products. %s ');

        $verif='<array>
    <object>
        <'.static::$productIdType.' property="id" value="'.$p1->id.'" />
        <string property="tag" value="tag1" />
        <string property="product_name" value="Test 1" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.$p1->id.'" />
        <string property="tag" value="tag2" />
        <string property="product_name" value="Test 1" />
    </object>
    <object>
        <'.static::$productIdType.' property="id" value="'.$p2->id.'" />
        <string property="tag" value="tag3" />
        <string property="product_name" value="Test 2" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($list, $verif);
    }
}
