<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2022-2023 Laurent Jouanneau
 *
 * @see      https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Parser;

class DaoTable
{
    const TYPE_PRIMARY = 0;
    const TYPE_FOREIGN = 1;
    const TYPE_OPTIONAL_FOREIGN = 2;

    public $name;

    public $realName;

    /**
     * @var string[] list of property names that serve as primary key
     */
    public $primaryKey = array();

    /**
     * @var string[] list of property names of the primary table on which the primary keys match
     */
    public $foreignKeys = array();

    /**
     * @var string[] list of property names belonging to the table
     */
    public $fields = array();

    /**
     * @var int One of TYPE_* const
     */
    public $usageType = 0;

    public function __construct($name, $realName, $primaryKey, $usageType)
    {
        $this->name =  $name;
        $this->realName = $realName;
        $this->primaryKey = $primaryKey;
        $this->usageType = $usageType;
    }

    /**
     * Parse the xml fragment corresponding to a table from a dao file
     *
     * @param int $usageType one of the TYPE_* const
     * @param \SimpleXMLElement $tableElement xml element from which attributes should be read
     * @param XMLDaoParser $parser      the parser on the dao file
     * @return DaoTable
     * @throws ParserException
     *
     * @internal param array $attributes list of attributes of a simpleXmlElement
     */
    public static function parseFromXml($usageType, \SimpleXMLElement $tableElement, XMLDaoParser $parser)
    {
        $infos = $parser->getAttr($tableElement, array('name', 'realname', 'primarykey', 'onforeignkey'));

        if ($infos['name'] === null) {
            throw new ParserException($parser->getDaoFile(), 'table name is missing', 522);
        }

        if ($infos['primarykey'] === null) {
            throw new ParserException($parser->getDaoFile(), 'primary key name is missing', 523);
        }

        $table = new DaoTable($infos['name'],
            $infos['realname'] ?:$infos['name'],
            preg_split('/[\\s,]+/', $infos['primarykey']),
            $usageType
        );

        if (count($table->primaryKey) == 0 || $table->primaryKey[0] == '') {
            throw new ParserException($parser->getDaoFile(), 'primary key name is missing', 523);
        }

        if ($usageType != self::TYPE_PRIMARY) { // for the foreigntable and optionalforeigntable
            if ($infos['onforeignkey'] === null) {
                throw new ParserException($parser->getDaoFile(), 'foreign key name is missing on a join', 524);
            }
            $table->foreignKeys = preg_split('/[\\s,]+/', $infos['onforeignkey']);
            if (count($table->foreignKeys ) == 0 || $table->foreignKeys [0] == '') {
                throw new ParserException($parser->getDaoFile(), 'foreign key name is missing on a join', 524);
            }
            if (count($table->foreignKeys ) != count($table->primaryKey)) {
                throw new ParserException($parser->getDaoFile(), 'foreign key name is missing on a join', 524);
            }
        }
        return $table;
    }


}