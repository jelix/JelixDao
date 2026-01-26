<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2005-2026 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao\Generator;

use Jelix\Dao\ContextInterface;
use Jelix\Dao\ContextInterface2;
use Jelix\Dao\DaoFileInterface;
use Jelix\Dao\DaoFileInterface2;
use Jelix\Dao\DeprecatedContextProxy;
use Jelix\Dao\Parser\XMLDaoParser;
use Jelix\FileUtilities\File;

/**
 * The compiler for the DAO xml files.
 */
class Compiler
{
    const XML_NAMESPACE = 'http://jelix.org/ns/dao/1.0';

    /**
     * compile the content of the given DAO XML file to a PHP class
     *
     * @param DaoFileInterface $daoFile  informations about the dao xml file to read
     * @param ContextInterface $context  context object
     *
     * @return bool true if success
     */
    public function compile(DaoFileInterface $daoFile, ContextInterface $context)
    {
        if (! ($context instanceof ContextInterface2)) {
            $context = new DeprecatedContextProxy($context);
        }

        $parser = $this->parse($daoFile, $context);

        $dbType = ucfirst($context->getSQLType());
        $class = '\\Jelix\\Dao\\Generator\\Adapter\\'.$dbType.'DaoGenerator';
        if (!class_exists($class)) {
            throw new Exception('Dao adapter for "'.$dbType.'" is not found', 505);
        }
        /** @var AbstractDaoGenerator $generator */
        $generator = new $class($context->getSqlSyntaxHelpers(), $parser);

        // generation of PHP classes corresponding to the DAO definition
        $compiledHeader = "\n";
        if ($context->shouldCheckCompiledClassCache()) {
            $compiledHeader .= "if (\n";
            $compiledHeader .= "\n filemtime('".$daoFile->getPath().'\') > '.filemtime($daoFile->getPath());
            $importedDao = $parser->getImportedDao();
            if ($importedDao) {
                foreach ($importedDao as $selimpdao) {
                    $path = $selimpdao->getPath();
                    $compiledHeader .= "\n|| filemtime('".$path.'\') > '.filemtime($path);
                }
            }
            $compiledHeader .= "){ return false;\n}\nelse {\n";
            $compiledFooter ="\n return true; }";
        } else {
            $compiledFooter = "\n return true;";
        }

        list($factoryNamespace, $factorySources, $recordNamespace, $recordSources) = $generator->buildClasses();

        if ($daoFile instanceof DaoFileInterface2) {
            $factoryHeader = '';
            if ($factoryNamespace) {
                $factoryHeader = "namespace $factoryNamespace;\n";
            }
            File::write($daoFile->getCompiledFactoryFilePath(), '<?php '.$factoryHeader. $compiledHeader.$factorySources.$compiledFooter);
            $recordHeader = '';
            if ($recordNamespace) {
                $recordHeader = "namespace $recordNamespace;\n";
            }
            File::write($daoFile->getCompiledRecordFilePath(), '<?php '.$recordHeader.$compiledHeader.$recordSources.$compiledFooter);
        }
        else {
            File::write($daoFile->getCompiledFilePath(), '<?php '.$compiledHeader.$recordSources."\n".$factorySources.$compiledFooter);
        }

        return true;
    }

    /**
     * @param  DaoFileInterface  $daoFile
     * @param  ContextInterface  $context
     *
     * @return XMLDaoParser
     * @throws \Exception
     */
    public function parse(DaoFileInterface $daoFile, ContextInterface $context)
    {
        if (! ($context instanceof ContextInterface2)) {
            $context = new DeprecatedContextProxy($context);
        }

        // load the XML file
        $doc = new \DOMDocument();

        if (!$doc->load($daoFile->getPath())) {
            throw new \Exception('Unknown dao file ('.$daoFile->getPath().')', 510);
        }

        if ($doc->documentElement->namespaceURI != self::XML_NAMESPACE) {
            throw new \Exception('bad namespace in the DAO file "'.$daoFile->getPath().'" ('.$doc->namespaceURI.')', 511);
        }

        $parser = new XMLDaoParser($daoFile, $context);
        $parser->parse(simplexml_import_dom($doc));
        return $parser;
    }


}
