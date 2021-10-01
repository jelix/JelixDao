<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2021 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


class ContextTest extends \PHPUnit\Framework\TestCase
{

    protected function getConnection()
    {
        $accessParameters = new \Jelix\Database\AccessParameters(
            array(
                'driver'=>'sqlite3',
                'database'=>"tests.sqlite3",
            ),
            array('charset'=>'UTF-8')
        );

        $connector = \Jelix\Database\Connection::create($accessParameters);
        return $connector;
    }

    function testContext()
    {
        $tempPath = __DIR__.'/../tmp';
        $daosDirectory = __DIR__.'/../lib/daos/';
        $context = new \Jelix\Dao\Context(
            $this->getConnection(),
            $tempPath,
            $daosDirectory
        );

        $daoFilePath = realpath(__DIR__.'/../lib/daos/products.xml');

        $daoFile = $context->resolveDaoPath('products.xml');
        $this->assertEquals($daoFilePath, $daoFile->getPath());

        $daoFile = $context->resolveDaoPath('products');
        $this->assertEquals($daoFilePath, $daoFile->getPath());

        $daoFile = $context->resolveDaoPath(__DIR__.'/../lib/daos/products.xml');
        $this->assertEquals(__DIR__.'/../lib/daos/products.xml', $daoFile->getPath());

        $daoCompiledClassPath = __DIR__.'/../tmp/products.xml.Sqlite.php';
        $this->assertEquals($daoCompiledClassPath, $daoFile->getCompiledFilePath());
        $this->assertEquals('ProductsSqliteFactory', $daoFile->getCompiledFactoryClass());
        $this->assertEquals('ProductsSqliteRecord', $daoFile->getCompiledRecordClass());

        $daoClassPath = realpath(__DIR__.'/../lib/daos').'/products.php';
        $customClass = $context->resolveCustomRecordClassPath('products.php');
        $this->assertEquals($daoClassPath, $customClass->getPath());
        $this->assertEquals('Products', $customClass->getClassName());
    }

}
