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
        $this->assertEquals('products.xml', $daoFile->getName());

        $this->assertEquals($daoFilePath, $daoFile->getPath());

        $daoFile = $context->resolveDaoPath('products');
        $this->assertEquals('products', $daoFile->getName());
        $this->assertEquals($daoFilePath, $daoFile->getPath());

        $daoFile = $context->resolveDaoPath(__DIR__.'/../lib/daos/products.xml');
        $this->assertEquals('products.xml', $daoFile->getName());
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

    function testContextForJelix()
    {
        $tempPath = __DIR__.'/../tmp';
        $daosDirectory = __DIR__.'/../lib/daos/';
        $context = new \Jelix\Dao\JelixModuleContext(
            $this->getConnection(),
            $tempPath,
            $daosDirectory,
            true
        );

        $daoFilePath = realpath(__DIR__.'/../lib/daos/posts.dao.xml');

        $daoFile = $context->resolveDaoPath('posts.dao.xml');
        $this->assertEquals('posts.dao.xml', $daoFile->getName());

        $this->assertEquals($daoFilePath, $daoFile->getPath());

        $daoFile = $context->resolveDaoPath('posts');
        $this->assertEquals('posts', $daoFile->getName());
        $this->assertEquals($daoFilePath, $daoFile->getPath());

        $daoFile = $context->resolveDaoPath(__DIR__.'/../lib/daos/posts.dao.xml');
        $this->assertEquals('posts.dao.xml', $daoFile->getName());
        $this->assertEquals(__DIR__.'/../lib/daos/posts.dao.xml', $daoFile->getPath());

        $daoCompiledClassPath = __DIR__.'/../tmp/posts.dao.xml.Sqlite.php';
        $this->assertEquals($daoCompiledClassPath, $daoFile->getCompiledFilePath());
        $this->assertEquals('PostsSqliteFactory', $daoFile->getCompiledFactoryClass());
        $this->assertEquals('PostsSqliteRecord', $daoFile->getCompiledRecordClass());

        $daoClassPath = realpath(__DIR__.'/../lib/daos').'/post.daorecord.php';
        $customClass = $context->resolveCustomRecordClassPath('post.daorecord.php');
        $this->assertEquals($daoClassPath, $customClass->getPath());
        $this->assertEquals('Post', $customClass->getClassName());
    }

}
