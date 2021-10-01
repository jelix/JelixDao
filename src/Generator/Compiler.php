<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2005-2021 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao\Generator;

use Jelix\Dao\ContextInterface;
use Jelix\Dao\DaoFileInterface;
use Jelix\Dao\Parser\XMLDaoParser;

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

        $sqlTools = $context->getDbTools();
        $dbType = ucfirst($sqlTools->getConnection()->getSQLType());
        $class = '\\Jelix\\Dao\\Generator\\Adapter\\'.$dbType.'DaoGenerator';
        if (!class_exists($class)) {
            throw new Exception('Dao adapter for "'.$dbType.'" is not found', 505);
        }
        /** @var AbstractDaoGenerator $generator */
        $generator = new $class($sqlTools, $parser);

        // generation of PHP classes corresponding to the DAO definition
        $compiled = '<?php ';
        if ($context->shouldCheckCompiledClassCache()) {
            $compiled .= "\nif (\n";
            $compiled .= "\n filemtime('".$daoFile->getPath().'\') > '.filemtime($daoFile->getPath());
            $importedDao = $parser->getImportedDao();
            if ($importedDao) {
                foreach ($importedDao as $selimpdao) {
                    $path = $selimpdao->getPath();
                    $compiled .= "\n|| filemtime('".$path.'\') > '.filemtime($path);
                }
            }
            $compiled .= "){ return false;\n}\nelse {\n";
            $compiled .= $generator->buildClasses()."\n return true; }";
        } else {
            $compiled .= $generator->buildClasses()."\n return true;";
        }

        file_put_contents($daoFile->getCompiledFilePath(), $compiled);

        return true;
    }
}
