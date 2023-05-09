<?php
/**
 * @author      Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau
 *
 * @copyright   2001-2005 CopixTeam, 2005-2023 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Parser;

use Jelix\Dao\ContextInterface;
use Jelix\Dao\CustomRecordClassFileInterface;
use Jelix\Dao\DaoFileInterface;

/**
 * extract data from a dao xml content.
 *
 * @see Compiler
 */
class XMLDaoParser
{
    /**
     * the properties list.
     * keys = field code name.
     *
     * @var DAOProperty[]
     */
    private $_properties = array();

    /**
     * All tables with their properties, and their own fields
     *
     * keys = table code name
     * @var DaoTable[]
     */
    private $_tables = array();

    /**
     * primary table code name.
     *
     * @var string
     */
    private $_primaryTable = '';

    /**
     * code name of foreign table with an outer join.
     *
     * @var array[] list of array(table code name, 0)
     */
    private $_ojoins = array();

    /**
     * code name of foreign table with an inner join.
     *
     * @var string[] list of table code name
     */
    private $_ijoins = array();

    /**
     * @var DaoMethod[]
     */
    private $_methods = array();

    /**
     * list of main events to sent.
     */
    private $_eventList = array();

    private $_hasOnlyPrimaryKeys = false;

    /**
     * selector of the user record class.
     *
     * @var CustomRecordClassFileInterface
     */
    private $_customRecord;

    /**
     * selector of the imported dao.
     *
     * @var DaoFileInterface[]
     */
    private $_importedDao;

    /**
     * @var DaoFileInterface
     */
    protected $daoFile;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param DaoFileInterface $dao information about the DAO file
     */
    public function __construct(DaoFileInterface $dao, ContextInterface $context)
    {
        $this->daoFile = $dao;
        $this->context = $context;
    }

    /**
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return DaoFileInterface
     */
    public function getDaoFile()
    {
        return $this->daoFile;
    }

    /**
     * @return bool
     */
    public function hasOnlyPrimaryKeys()
    {
        return $this->_hasOnlyPrimaryKeys;
    }

    /**
     * parse a dao xml content.
     *
     * @param \SimpleXMLElement $xml
     */
    public function parse(\SimpleXMLElement $xml)
    {
        $this->import($xml);
        $this->parseDatasource($xml);
        $this->parseRecord($xml);
        $this->parseFactory($xml);
    }

    protected function import($xml)
    {
        if (!isset($xml['import'])) {
            return;
        }
        $import = (string) $xml['import'];

        $importFile = $this->context->resolveDaoPath($import);

        $doc = new \DOMDocument();
        if (!$doc->load($importFile->getPath())) {
            throw new \Exception('Unknown dao file ('.$importFile->getPath().')', 510);
        }
        $parser = new XMLDaoParser($importFile, $this->context);
        $parser->parse(simplexml_import_dom($doc));

        $this->_properties = $parser->getProperties();
        $this->_tables = $parser->getTables();
        $this->_primaryTable = $parser->getPrimaryTable();
        $this->_methods = $parser->getMethods();
        $this->_ojoins = $parser->getOuterJoins();
        $this->_ijoins = $parser->getInnerJoins();
        $this->_eventList = $parser->getEvents();
        $this->_customRecord = $parser->getCustomRecord();
        $this->_importedDao = $parser->getImportedDao();
        $this->_hasOnlyPrimaryKeys = $parser->hasOnlyPrimaryKeys();

        if ($this->_importedDao) {
            $this->_importedDao[] = $importFile;
        } else {
            $this->_importedDao = array($importFile);
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     *
     * @throws ParserException
     */
    protected function parseDatasource(\SimpleXMLElement $xml)
    {
        // -- tables
        if (isset($xml->datasources, $xml->datasources[0]->primarytable)) {
            $previousTables = $this->_tables;
            // erase table definitions (in the case where the dao imports an other one)
            $this->_tables = array();
            $this->_ijoins = array();
            $this->_ojoins = array();

            $t = $this->_parseTable(DaoTable::TYPE_PRIMARY, $xml->datasources[0]->primarytable[0]);
            $this->_primaryTable = $t->name;
            if (isset($previousTables[$t->name])) {
                $this->_tables[$t->name]->fields = $previousTables[$t->name]->fields;
            }
            if (isset($xml->datasources[0]->primarytable[1])) {
                throw new ParserException($this->daoFile, 'Too many primary tables, only one allowed', 521);
            }
            foreach ($xml->datasources[0]->foreigntable as $table) {
                $t = $this->_parseTable(DaoTable::TYPE_FOREIGN, $table);
                if (isset($previousTables[$t->name])) {
                    $this->_tables[$t->name]->fields = $previousTables[$t->name]->fields;
                }
            }
            foreach ($xml->datasources[0]->optionalforeigntable as $table) {
                $t = $this->_parseTable(DaoTable::TYPE_OPTIONAL_FOREIGN, $table);
                if (isset($previousTables[$t->name])) {
                    $this->_tables[$t->name]->fields = $previousTables[$t->name]->fields;
                }
            }
        } elseif ($this->_primaryTable === '') { // no imported dao
            throw new ParserException($this->daoFile, 'Table is missing', 520);
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     *
     * @throws ParserException
     */
    protected function parseRecord(\SimpleXMLElement $xml)
    {
        //add the record properties
        if (isset($xml->record)) {
            if (isset($xml->record[0]['extends'])) {
                $this->_customRecord = $this->context->resolveCustomRecordClassPath((string) $xml->record[0]['extends']);
            }
            if (isset($xml->record[0]->property)) {
                // don't append directly new properties into _properties,
                // so we can see the differences between imported properties
                // and readed properties
                $properties = array();
                foreach ($xml->record[0]->property as $prop) {
                    $p = new DaoProperty($prop->attributes(), $this);
                    if (isset($properties[$p->name])) {
                        throw new ParserException($this->daoFile, 'property '.$p->name.' already defined', 533);
                    }
                    if (!in_array($p->name, $this->_tables[$p->table]->fields)) { // if this property does not redefined an imported property
                        $this->_tables[$p->table]->fields[] = $p->name;
                    }
                    $properties[$p->name] = $p;
                }
                $this->_properties = array_merge($this->_properties, $properties);
            }
        }
        // in the case when there is no defined property and no imported dao
        if (count($this->_properties) == 0) {
            throw new ParserException($this->daoFile, 'no defined property', 530);
        }

        // check that properties are attached to a known table. It can be
        // wrong if the datasource has been redefined with other table names
        $countprop = 0;
        foreach ($this->_properties as $p) {
            if (!isset($this->_tables[$p->table])) {
                throw new ParserException($this->daoFile, 'unknown table name on the imported property '.$p->name, 534);
            }
            if ($p->ofPrimaryTable && !$p->isPK) {
                $countprop++;
            }
        }
        $this->_hasOnlyPrimaryKeys = ($countprop == 0);
    }

    protected function parseFactory(\SimpleXMLElement $xml)
    {
        // get additional methods definition
        if (isset($xml->factory)) {
            if (isset($xml->factory[0]['events'])) {
                $events = (string) $xml->factory[0]['events'];
                $this->_eventList = preg_split('/[\\s,]+/', $events);
            }

            if (isset($xml->factory[0]->method)) {
                $methods = array();
                foreach ($xml->factory[0]->method as $method) {
                    $m = new DaoMethod($method, $this);
                    if (isset($methods[$m->name])) {
                        throw new ParserException($this->daoFile, 'method '.$m->name.' is already defined', 545);
                    }
                    $methods[$m->name] = $m;
                }
                $this->_methods = array_merge($this->_methods, $methods);
            }
        }
    }

    /**
     * parse a join definition.
     *
     * @param int              $typeTable
     * @param \SimpleXMLElement $tableElement
     * @return DaoTable
     */
    private function _parseTable($typeTable, $tableElement)
    {
        $table = DaoTable::parseFromXml($typeTable, $tableElement, $this);
        if ($typeTable == $table::TYPE_FOREIGN) { // for the foreigntable and optionalforeigntable
            $this->_ijoins[] = $table->name;
        }
        else if ($typeTable == $table::TYPE_OPTIONAL_FOREIGN) {
            $this->_ojoins[] = array($table->name, 0);
        }

        $this->_tables[$table->name] = $table;

        return $table;
    }

    /**
     * Try to read all given attributes.
     *
     * @param \SimpleXMLElement $tag
     * @param string[]         $requiredattr attributes list
     *
     * @return string[] attributes and their values
     */
    public function getAttr($tag, $requiredattr)
    {
        $res = array();
        foreach ($requiredattr as $attr) {
            if (isset($tag[$attr]) && trim((string) $tag[$attr]) != '') {
                $res[$attr] = (string) $tag[$attr];
            } else {
                $res[$attr] = null;
            }
        }

        return $res;
    }

    /**
     * just a quick way to retrieve boolean values from a string.
     *  will accept yes, true, 1 as "true" values
     *  all other values will be considered as false.
     *
     * @param mixed $value
     *
     * @return bool true / false
     */
    public function getBool($value)
    {
        if (is_string($value)) {
            return in_array(trim($value), array('true', '1', 'yes'));
        }
        else if (is_bool($value)) {
            return $value;
        }
        else if ($value === 1) {
            return true;
        }
        return false;
    }

    /**
     * the properties list.
     * keys = field code name.
     *
     * @return DaoProperty[]
     */
    public function getProperties()
    {
        return $this->_properties;
    }

    /**
     * all tables with their properties, and their own fields
     * keys = table code name
     * @return DaoTable[]
     */
    public function getTables()
    {
        return $this->_tables;
    }

    /**
     * @return string the primary table code name
     */
    public function getPrimaryTable()
    {
        return $this->_primaryTable;
    }

    /**
     * @return DaoMethod[] list of jDaoMethod objects
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * list of code name of foreign table with a outer join.
     *
     * @return array[] list of array(table code name, 0)
     */
    public function getOuterJoins()
    {
        return $this->_ojoins;
    }

    /**
     * list of code name of foreign tables with a inner join.
     *
     * @return string[] the list
     */
    public function getInnerJoins()
    {
        return $this->_ijoins;
    }

    public function getEvents()
    {
        return $this->_eventList;
    }

    public function hasEvent($event)
    {
        return in_array($event, $this->_eventList);
    }

    /**
     * selector of the user record class.
     *
     * @return CustomRecordClassFileInterface
     */
    public function getCustomRecord()
    {
        return $this->_customRecord;
    }

    /**
     * selector of the imported dao. If can return several selector, if
     * an imported dao import itself an other dao etc.
     *
     * @return DaoFileInterface[]
     */
    public function getImportedDao()
    {
        return $this->_importedDao;
    }
}
