<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2011-2022 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */



class jdao_factory_Test extends \Jelix\UnitTests\UnitTestCaseDb
{
    public static function setUpBeforeClass() : void  {
        $tempPath = __DIR__.'/../tmp/mainapi/';
        $daosDirectory = __DIR__.'/../lib/daos/';

        $daoLoader = new \Jelix\Dao\DaoLoader(
            self::getConnector(),
            $tempPath,
            $daosDirectory
        );
        // to load factory classes
        $daoLoader->create ('products');
        $daoLoader->create ('productsAlias');
    }

    protected $daoLoader;

    protected $parametersNoPrefix;
    protected $parametersWithPrefix;

    function setUp() : void
    {
        $this->parametersNoPrefix = (new \Jelix\Database\AccessParameters(
            array(
                'driver'=>'mysqli',
                'host'=>'mysql',
                'database'=>"jelixtests",
                "user"=>"jelix",
                "password"=>"jelixpass",
                "table_prefix" => ''
            ),
            array('charset'=>'UTF-8')
        ))->getNormalizedParameters();
        $this->parametersWithPrefix = (new \Jelix\Database\AccessParameters(
            array(
                'driver'=>'mysqli',
                'host'=>'mysql',
                'database'=>"jelixtests",
                "user"=>"jelix",
                "password"=>"jelixpass",
                "table_prefix" => 'foo_'
            ),
            array('charset'=>'UTF-8')
        ))->getNormalizedParameters();

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
                "prefix" => $prefix
            ),
            array('charset'=>'UTF-8')
        );

        $connector = \Jelix\Database\Connection::create($accessParameters);
        return $connector;
    }

    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }

    protected function getConn($profile)
    {
        $conn = $this->getMockBuilder('\Jelix\Database\Connector\Mysqli\Connection')
                     ->disableOriginalConstructor()
                     ->setMethods(array('query', 'exec', 'limitQuery', 'disconnect'))
                     ->getMock();
        $this->setProtectedProperty($conn, '_profile', $profile);

        $rs =  $this->getMockBuilder('\Jelix\Database\Connector\Mysqli\ResultSet')
                    ->disableOriginalConstructor()
                    ->getMock();
        $conn->expects($this->any())
             ->method('query')
             ->will($this->returnValue($rs));
        $conn->expects($this->any())
             ->method('limitQuery')
             ->will($this->returnValue($rs));
        $conn->expects($this->any())
             ->method('query')
             ->will($this->returnValue(0));
        return [$conn, $rs];
    }

    function testFindAll() {
        list($conn, $rs) = $this->getConn($this->parametersNoPrefix);
        $dao = new ProductsMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`create_date`, `products`.`promo`, `products`.`dummy` FROM `products` AS `products`'));
        $dao->findAll();
    }

    function testFindAllPrefix() {
        list($conn, $rs) = $this->getConn($this->parametersWithPrefix);
        $dao = new ProductsMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`create_date`, `products`.`promo`, `products`.`dummy` FROM `foo_products` AS `products`'));
        $dao->findAll();
    }

    function testFindAllAlias() {
        list($conn, $rs) = $this->getConn($this->parametersNoPrefix);
        $dao = new ProductsAliasMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`create_date`, `p`.`promo`, `p`.`dummy` FROM `products` AS `p`'));
        $dao->findAll();
    }

    function testFindAllAliasPrefix() {
        list($conn, $rs) = $this->getConn($this->parametersWithPrefix);
        $dao = new ProductsAliasMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`create_date`, `p`.`promo`, `p`.`dummy` FROM `foo_products` AS `p`'));
        $dao->findAll();
    }

    function testCountAll() {
        list($conn, $rs) = $this->getConn($this->parametersNoPrefix);
        $o = new stdClass();
        $o->c = '54';
        $rs->expects($this->any())
           ->method('fetch')
           ->will($this->returnValue($o));

        $dao = new ProductsMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT COUNT(*) as c  FROM `products` AS `products`'));
        $dao->countAll();
    }

    function testCountAllPrefix() {
        list($conn, $rs) = $this->getConn($this->parametersWithPrefix);
        $o = new stdClass();
        $o->c = '54';
        $rs->expects($this->any())
           ->method('fetch')
           ->will($this->returnValue($o));
        $dao = new ProductsMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT COUNT(*) as c  FROM `foo_products` AS `products`'));
        $dao->countAll();
    }

    function testCountAllAlias() {
        list($conn, $rs) = $this->getConn($this->parametersNoPrefix);
        $o = new stdClass();
        $o->c = '54';
        $rs->expects($this->any())
           ->method('fetch')
           ->will($this->returnValue($o));
        $dao = new ProductsAliasMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT COUNT(*) as c  FROM `products` AS `p`'));
        $dao->countAll();
    }

    function testCountAllAliasPrefix()
    {
        list($conn, $rs) = $this->getConn($this->parametersWithPrefix);
        $o = new stdClass();
        $o->c = '54';
        $rs->expects($this->any())
           ->method('fetch')
           ->will($this->returnValue($o));
        $dao = new ProductsAliasMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT COUNT(*) as c  FROM `foo_products` AS `p`'));
        $dao->countAll();
    }

    function testGet()
    {
        list($conn, $rs) = $this->getConn($this->parametersNoPrefix);
        $o = new stdClass();
        $rs->expects($this->any())
           ->method('fetch')
           ->will($this->returnValue($o));
        $dao = new ProductsMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`create_date`, `products`.`promo`, `products`.`dummy` FROM `products` AS `products` WHERE  `products`.`id` = 32'));
        $dao->get(32);
    }

    function testGetPrefix() {
        $o = new stdClass();
        list($conn, $rs) = $this->getConn($this->parametersWithPrefix);
        $rs->expects($this->any())
           ->method('fetch')
           ->will($this->returnValue($o));
        $dao = new ProductsMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`create_date`, `products`.`promo`, `products`.`dummy` FROM `foo_products` AS `products` WHERE  `products`.`id` = 32'));
        $dao->get(32);
    }

    function testGetAlias() {
        $o = new stdClass();
        list($conn, $rs) = $this->getConn($this->parametersNoPrefix);
        $rs->expects($this->any())
           ->method('fetch')
           ->will($this->returnValue($o));
        $dao = new ProductsAliasMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`create_date`, `p`.`promo`, `p`.`dummy` FROM `products` AS `p` WHERE  `p`.`id` = 32'));
        $dao->get(32);
    }

    function testGetAliasPrefix() {
        $o = new stdClass();
        list($conn, $rs) = $this->getConn($this->parametersWithPrefix);
        $rs->expects($this->any())
           ->method('fetch')
           ->will($this->returnValue($o));
        $dao = new ProductsAliasMysqlFactory($conn);
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`create_date`, `p`.`promo`, `p`.`dummy` FROM `foo_products` AS `p` WHERE  `p`.`id` = 32'));
        $dao->get(32);
    }

    function testFindBy() {
        list($conn, $rs) = $this->getConn($this->parametersNoPrefix);
        $dao = new ProductsMysqlFactory($conn);
        $cond = new \Jelix\Dao\DaoConditions('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`create_date`, `products`.`promo`, `products`.`dummy` FROM `products` AS `products` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByPrefix() {
        list($conn, $rs) = $this->getConn($this->parametersWithPrefix);
        $dao = new ProductsMysqlFactory($conn);
        $cond = new \Jelix\Dao\DaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`create_date`, `products`.`promo`, `products`.`dummy` FROM `foo_products` AS `products` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByAlias() {
        list($conn, $rs) = $this->getConn($this->parametersNoPrefix);
        $dao = new ProductsAliasMysqlFactory($conn);
        $cond = new \Jelix\Dao\DaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`create_date`, `p`.`promo`, `p`.`dummy` FROM `products` AS `p` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByAliasPrefix() {
        list($conn, $rs) = $this->getConn($this->parametersWithPrefix);
        $dao = new ProductsAliasMysqlFactory($conn);
        $cond = new \Jelix\Dao\DaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $conn->expects($this->once())
             ->method('query')
             ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`create_date`, `p`.`promo`, `p`.`dummy` FROM `foo_products` AS `p` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }
}
