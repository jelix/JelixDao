<?php
/**
 * @author      Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau
 * @contributor Philippe Villiers
 *
 * @copyright   2001-2005 CopixTeam, 2005-2023 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Parser;

use Jelix\Database\Schema\AbstractSqlTools;

/**
 * Container for properties of a dao property.
 *
 */
class DaoProperty
{
    /**
     * the name of the property of the object.
     */
    public $name = '';

    /**
     * the name of the field in table.
     */
    public $fieldName = '';

    /**
     * give the regular expression that needs to be matched against.
     *
     * @var string
     */
    public $regExp;

    /**
     * says if the field is required when doing a check.
     *
     * @var bool
     */
    public $required = false;

    /**
     * says if the value of the field is required when construct SQL conditions.
     *
     * @var bool
     */
    public $requiredInConditions = false;

    /**
     * Says if it's a primary key.
     *
     * @var bool
     */
    public $isPK = false;

    /**
     * Says if it's a foreign key.
     *
     * @var bool
     */
    public $isFK = false;

    public $datatype;

    public $unifiedType;

    public $table;
    public $updatePattern = '%s';
    public $insertPattern = '%s';
    public $selectPattern = '%s';
    public $sequenceName = '';

    /**
     * the maxlength of the key if given.
     *
     * @var int
     */
    public $maxlength;
    public $minlength;

    public $ofPrimaryTable = true;

    public $defaultValue;

    public $autoIncrement = false;

    /**
     * comment field / eg : use to form's label.
     *
     * @var string
     */
    public $comment = '';

    /**
     * constructor.
     *
     * @param \SimpleXMLElement $aAttributes xml element from which attributes should be read
     * @param XMLDaoParser $parser      the parser on the dao file
     *
     * @throws ParserException
     *
     * @internal param array $attributes list of attributes of a simpleXmlElement
     */
    public function __construct(\SimpleXMLElement $aAttributes, XMLDaoParser $parser)
    {
        $needed = array('name', 'fieldname', 'table', 'datatype', 'required',
            'minlength', 'maxlength', 'regexp', 'sequence', 'default', 'autoincrement', );

        // Allowed attributes names
        $allowed = array('name', 'fieldname', 'table', 'datatype', 'required',
            'minlength', 'maxlength', 'regexp', 'sequence', 'default', 'autoincrement',
            'updatepattern', 'insertpattern', 'selectpattern', 'comment', );

        foreach ($aAttributes as $attributeName => $attributeValue) {
            if (!in_array($attributeName, $allowed)) {
                throw new ParserException($parser->getDaoFile(), 'attribute "'.$attributeName.'" is unknown on tag "property"', 517);
            }
        }

        $params = $parser->getAttr($aAttributes, $needed);

        if ($params['name'] === null) {
            throw new ParserException($parser->getDaoFile(), 'attribute "name" is missing on tag "property"', 512);
        }
        $this->name = $params['name'];

        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $this->name)) {
            throw new ParserException($parser->getDaoFile(), 'invalid syntax in the property name "'.$this->name.'", it must respect php syntax', 532);
        }

        $this->fieldName = $params['fieldname'] !== null ? $params['fieldname'] : $this->name;
        $this->table = $params['table'] !== null ? $params['table'] : $parser->getPrimaryTable();

        $tables = $parser->getTables();

        if (!isset($tables[$this->table])) {
            throw new ParserException($parser->getDaoFile(), 'unknown table name on the property "'.$this->name.'"', 531);
        }

        $this->required = $this->requiredInConditions = $parser->getBool($params['required']);
        $this->maxlength = $params['maxlength'] !== null ? intval($params['maxlength']) : null;
        $this->minlength = $params['minlength'] !== null ? intval($params['minlength']) : null;
        $this->regExp = $params['regexp'];
        $this->autoIncrement = $parser->getBool($params['autoincrement']);

        if ($params['datatype'] === null) {
            throw new ParserException($parser->getDaoFile(), 'attribute "datatype" is missing on tag "property"', 512);
        }
        $params['datatype'] = trim(strtolower($params['datatype']));

        if ($params['datatype'] == '') {
            throw new ParserException($parser->getDaoFile(), 'the value "'.$params['datatype'].'" of the "'.$this->fieldName.'" attribute on the "property" element is not valid ', 516);
        }
        $this->datatype = strtolower($params['datatype']);

        $ti = $parser->getContext()->getDbTools()->getTypeInfo($this->datatype);
        $this->unifiedType = $ti[1];
        if (!$this->autoIncrement) {
            $this->autoIncrement = $ti[6];
        }

        if ($this->unifiedType == 'integer' || $this->unifiedType == 'numeric') {
            if ($params['sequence'] !== null) {
                $this->sequenceName = $params['sequence'];
                $this->autoIncrement = true;
            }
        } elseif ($this->autoIncrement) {
            throw new ParserException($parser->getDaoFile(), 'property "'.$this->fieldName.'" non numeric cannot be auto incremented', 535);
        }

        $pkeys = array_map('strtolower', $tables[$this->table]->primaryKey);
        $this->isPK = in_array(strtolower($this->fieldName), $pkeys);
        if (!$this->isPK && $this->table == $parser->getPrimaryTable()) {
            foreach ($tables as $tableName => $table) {
                if ($tableName == $this->table) {
                    continue;
                }
                if ($table->foreignKeys) {
                    $fkeys = array_map('strtolower', $table->foreignKeys);
                    if (in_array(strtolower($this->fieldName), $fkeys)) {
                        $this->isFK = true;

                        break;
                    }
                }
            }
        } else {
            $this->required = true;
            $this->requiredInConditions = true;
        }

        if ($this->autoIncrement) {
            $this->required = false;
            $this->requiredInConditions = true;
        }

        if ($params['default'] !== null) {
            $this->defaultValue = $parser->getContext()->getDbTools()->stringToPhpValue($this->unifiedType, $params['default']);
        }

        // insertpattern is allowed on primary keys noy autoincremented
        if ($this->isPK && !$this->autoIncrement && isset($aAttributes['insertpattern'])) {
            $this->insertPattern = (string) $aAttributes['insertpattern'];
        }
        if ($this->isPK) {
            $this->updatePattern = '';
        }
        // we ignore *pattern attributes on PK and FK fields
        if (!$this->isPK && !$this->isFK) {
            if (isset($aAttributes['updatepattern'])) {
                $this->updatePattern = (string) $aAttributes['updatepattern'];
            }

            if (isset($aAttributes['insertpattern'])) {
                $this->insertPattern = (string) $aAttributes['insertpattern'];
            }

            if (isset($aAttributes['selectpattern'])) {
                $this->selectPattern = (string) $aAttributes['selectpattern'];
            }
        }

        // no update and insert patterns for field of external tables
        if ($this->table != $parser->getPrimaryTable()) {
            $this->updatePattern = '';
            $this->insertPattern = '';
            $this->required = false;
            $this->requiredInConditions = false;
            $this->ofPrimaryTable = false;
        } else {
            $this->ofPrimaryTable = true;
        }

        // field comment
        if (isset($aAttributes['comment'])) {
            $this->comment = (string) $aAttributes['comment'];
        }
    }
}
