<?php
/**
 * @author      Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau
 * @contributor Philippe Villiers
 *
 * @copyright   2001-2005 CopixTeam, 2005-2025 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Parser;


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

    public $attributes = array();

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
            'updatepattern', 'insertpattern', 'selectpattern', 'comment',
            'jsontype', 'jsonobjectclass', 'jsonencoder', 'jsondecoder');

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
        } else if ($this->unifiedType == 'json') {
            $this->_parseJsonAttributes($aAttributes, $parser);
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

    protected function _parseJsonAttributes(\SimpleXMLElement $aAttributes, XMLDaoParser $parser)
    {
        $jsonClass = '';
        if (isset($aAttributes['jsontype'])) {
            $jsonType = (string) $aAttributes['jsontype'];
        }
        else {
            $jsonType = 'natural';
        }
        if (isset($aAttributes['jsonobjectclass'])) {
            $jsonClass = (string) $aAttributes['jsonobjectclass'];
            if ($jsonClass != '') {
                $jsonType="object";
            }
        }

        if ($jsonType != 'object' && $jsonClass != '') {
            throw new ParserException($parser->getDaoFile(), 'property "'.$this->fieldName.'" indicate a json class although json type is '.$jsonType, 536);
        }

        if ($jsonType == 'raw') {
            $this->attributes['jsonEncoder'] = '';
            $this->attributes['jsonDecoder'] = '';
            $this->attributes['jsonClass'] = '';
            return;
        }

        $jsonEncoder = '';
        $jsonDecoder = '';

        if (isset($aAttributes['jsonencoder'])) {
            $jsonEncoder = (string)$aAttributes['jsonencoder'];
        }
        if (isset($aAttributes['jsondecoder'])) {
            $jsonDecoder = (string)$aAttributes['jsondecoder'];
        }

        if ($jsonEncoder != '') {
            if (preg_match("/^([\w\\\\]+)?(\\-\\>|\\:\\:)(\w*)$/", $jsonEncoder, $matches)) {
                $encoderClass = $matches[1];
                if ($encoderClass == '') {
                    if ($jsonClass == '') {
                        throw new ParserException($parser->getDaoFile(), 'property "'.$this->fieldName.'": class name is missing into jsonencoder', 537);
                    }
                    $encoderClass = $jsonClass;
                    $jsonEncoder = $jsonClass.$jsonEncoder;
                }

                if ($matches[2] == '::') {
                    $jsonEncoder = 'call_user_func(\'' . $jsonEncoder . '\',%VALUE%)';
                } else {
                    if ($encoderClass == $jsonClass) {
                        // encoder cannot be an instance of $jsonClass, because the expression given
                        // to the encoder may be a complex expression (not a simple $record->jsonfield)
                        throw new ParserException($parser->getDaoFile(), 'property "'.$this->fieldName.'": encoder cannot be an instance of '.$jsonClass, 538);
                    }
                    else {
                        $jsonEncoder = '\\Jelix\\Dao\\Json\\JsonUtilities::encodeUsingExternalObjectMethod(\'' . $encoderClass . '\', \'' . $matches[3] . '\', %VALUE%)';
                    }
                }
            } else {
                // this is a simple function
                $jsonEncoder = $jsonEncoder . '(%VALUE%)';
            }
        } else {
            if ($jsonType == 'object') {
                $jsonEncoder = 'json_encode(%VALUE%, JSON_FORCE_OBJECT)';
            }
            else {
                $jsonEncoder = 'json_encode(%VALUE%)';
            }
        }

        if ($jsonDecoder != '') {
            if (preg_match("/^([\w\\\\]+)?(\\-\\>|\\:\\:)(\w*)$/", $jsonDecoder, $matches)) {
                $decoderClass = $matches[1];
                if ($decoderClass == '') {
                    if ($jsonClass == '') {
                        throw new ParserException($parser->getDaoFile(), 'property "'.$this->fieldName.'": class name is missing into jsonencoder', 537);
                    }
                    $decoderClass = $jsonClass;
                    $jsonDecoder = $jsonClass.$jsonDecoder;
                }
                if ($matches[2] == '::') {
                    $jsonDecoder = 'call_user_func(\'' . $jsonDecoder . '\',%FIELD%)';
                } else {
                    if ($decoderClass == $jsonClass) {
                        $jsonDecoder = '%FIELD%->' . $matches[3] . '(%FIELD%)';
                    } else {
                        $jsonDecoder = '\\Jelix\\Dao\\Json\\JsonUtilities::decodeToNewObjectUsingMethod(\'' . $decoderClass . '\', \'' . $matches[3] . '\', %FIELD%)';
                    }
                }
            } else {
                // this is a simple function
                $jsonDecoder = $jsonDecoder . '(%FIELD%)';
            }
        } else {
            if ($jsonType == 'object') {
                if ($jsonClass == '') {
                    $jsonDecoder = 'json_decode(%FIELD%, false, 512, JSON_FORCE_OBJECT)';
                }
                else {
                    $jsonDecoder = '\\Jelix\\Dao\\Json\\JsonUtilities::decodeToNewObject(\'' . $jsonClass . '\', %FIELD%)';
                }
            } else if ($jsonType == 'array') {
                $jsonDecoder = 'json_decode(%FIELD%, true)';
            } else {
                $jsonDecoder = 'json_decode(%FIELD%)';
            }
        }

        $this->attributes['jsonEncoder'] = $jsonEncoder;
        $this->attributes['jsonDecoder'] = $jsonDecoder;
        $this->attributes['jsonClass'] = $jsonClass;
    }
}
