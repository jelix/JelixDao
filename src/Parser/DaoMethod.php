<?php
/**
 * @package     jelix
 * @subpackage  dao
 *
 * @author      Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau
 * @contributor Olivier Demah
 * @contributor Philippe Villiers
 *
 * @copyright   2001-2005 CopixTeam, 2005-2020 Laurent Jouanneau, 2010 Olivier Demah, 2013 Philippe Villiers
 * This class was get originally from the Copix project (CopixDAODefinitionV1, Copix 2.3dev20050901, http://www.copix.org)
 * Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
 * Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
 *
 * @see      https://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao\Parser;

use Jelix\Dao\DaoConditions;

/**
 * containers for properties of dao method.
 *
 */
class DaoMethod
{
    public $name;
    public $type;
    public $distinct = false;
    public $eventBeforeEnabled = false;
    public $eventAfterEnabled = false;

    /**
     * @var DaoConditions
     */
    private $_conditions;

    /**
     * @var string[]
     */
    private $_parameters = array();
    private $_parametersDefaultValues = array();
    private $_limit;
    private $_values = array();

    /**
     * @var XMLDaoParser
     */
    private $_parser;

    private $_procstock;
    private $_body;



    /**
     * @param \SimpleXMLElement $method the xml element describing the method to generate
     * @param XMLDaoParser      $parser the parser on a dao file
     *
     * @throws ParserException
     */
    public function __construct(\SimpleXMLElement $method, XMLDaoParser $parser)
    {
        $this->_parser = $parser;

        $params = $parser->getAttr($method, array('name', 'type', 'call', 'distinct',
            'eventbefore', 'eventafter', 'groupby', ));

        if ($params['name'] === null) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'attribute "name" is missing on tag "method"',
                512
            );
        }

        $this->name = $params['name'];
        $this->type = $params['type'] ? strtolower($params['type']) : 'select';

        if (isset($method->parameter)) {
            foreach ($method->parameter as $param) {
                $attr = $param->attributes();
                if (!isset($attr['name'])) {
                    throw new ParserException(
                        $this->_parser->getDaoFile(),
                        'method '.$this->name.', parameter name is missing',
                        540
                    );
                }
                if (!preg_match('/[a-zA-Z_][a-zA-Z0-9_]*/', (string) $attr['name'])) {
                    throw new ParserException(
                        $this->_parser->getDaoFile(),
                        'in the method '.$this->name.', the sign $ in the parameter name '.$attr['name'].' is no authorized',
                        565
                    );
                }
                $this->_parameters[] = (string) $attr['name'];
                if (isset($attr['default'])) {
                    $this->_parametersDefaultValues[(string) $attr['name']] = (string) $attr['default'];
                }
            }
        }

        if ($this->type == 'sql') {
            if ($params['call'] === null) {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'procedure call name is missing on method '.$this->name,
                    541
                );
            }
            $this->_procstock = $params['call'];

            return;
        }

        if ($this->type == 'php') {
            if (isset($method->body)) {
                $this->_body = (string) $method->body;
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method PHP "'.$this->name.'": body is missing',
                    542
                );
            }

            return;
        }

        $this->_conditions = new DaoConditions();
        if (isset($method->conditions)) {
            $this->_parseConditions($method->conditions[0], false);
        }

        if ($this->type == 'update' || $this->type == 'delete') {
            if ($params['eventbefore'] == 'true') {
                $this->eventBeforeEnabled = true;
            }
            if ($params['eventafter'] == 'true') {
                $this->eventAfterEnabled = true;
            }
        }

        if ($this->type == 'update') {
            if ($this->_parser->hasOnlyPrimaryKeys()) {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'update method "'.$this->name.'" is forbidden because the main table contains only primary keys',
                    564
                );
            }

            if (isset($method->values, $method->values[0]->value)) {
                foreach ($method->values[0]->value as $val) {
                    $this->_addValue($val);
                }
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'" of "update" type, should contains a "value" tag',
                    543
                );
            }

            return;
        }

        if ($params['distinct'] != '') {
            if ($this->type == 'select') {
                $this->distinct = $this->_parser->getBool($params['distinct']);
            } elseif ($this->type == 'count') {
                $props = $this->_parser->getProperties();
                if (!isset($props[$params['distinct']])) {
                    throw new ParserException(
                        $this->_parser->getDaoFile(),
                        'method "'.$this->name.'", unknown property ("'.$params['distinct'].'")',
                        547
                    );
                }
                $this->distinct = $params['distinct'];
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'the "distinct" attribute is not allowed on the "<method name="'.$this->name.'">" element in this context',
                    515
                );
            }
        }

        if ($this->type == 'count') {
            return;
        }

        if (isset($method->order, $method->order[0]->orderitem)) {
            foreach ($method->order[0]->orderitem as $item) {
                $this->_addOrder($item);
            }
        }

        if ($params['groupby'] != '') {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'the "groupby" attribute is not allowed on the "<method name="'.$this->name.'">" element',
                514
            );
        }

        if (isset($method->limit)) {
            if (isset($method->limit[1])) {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'only one "limit" tag is allowed in the "'.$this->name.'" method',
                    513
                );
            }
            if ($this->type == 'select') {
                $this->_addLimit($method->limit[0]);
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'", limit tag is allowed only on a select method',
                    544
                );
            }
        }
    }

    /**
     * @return DaoConditions
     */
    public function getConditions()
    {
        return $this->_conditions;
    }

    /**
     * @return string[]
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    public function getParametersDefaultValues()
    {
        return $this->_parametersDefaultValues;
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    public function getValues()
    {
        return $this->_values;
    }

    public function getProcStock()
    {
        return $this->_procstock;
    }

    public function getBody()
    {
        return $this->_body;
    }

    /**
     * @param \SimpleXMLElement $conditions
     * @param bool             $subCond
     */
    private function _parseConditions(\SimpleXMLElement $conditions, $subCond = true)
    {
        if (isset($conditions['logic'])) {
            $kind = strtoupper((string) $conditions['logic']);
        } else {
            $kind = 'AND';
        }

        if ($subCond) {
            $this->_conditions->startGroup($kind);
        } else {
            $this->_conditions->condition->glueOp = $kind;
        }

        foreach ($conditions->children() as $op => $cond) {
            if ($op != 'conditions') {
                $this->_addCondition($op, $cond);
            } else {
                $this->_parseConditions($cond);
            }
        }

        if ($subCond) {
            $this->_conditions->endGroup();
        }
    }

    private $_op = array('eq' => '=', 'neq' => '<>', 'lt' => '<', 'gt' => '>', 'lteq' => '<=', 'gteq' => '>=',
        'like' => 'LIKE', 'notlike' => 'NOT LIKE', 'ilike' => 'ILIKE', 'isnull' => 'IS NULL', 'isnotnull' => 'IS NOT NULL', 'in' => 'IN', 'notin' => 'NOT IN',
        'binary_op' => 'dummy', );
    // 'between'=>'BETWEEN',  'notbetween'=>'NOT BETWEEN',

    private $_attrcond = array('property', 'pattern', 'expr', 'operator', 'dbtype'); //, 'min', 'max', 'exprmin', 'exprmax'

    private function _addCondition($op, $cond)
    {
        $attr = $this->_parser->getAttr($cond, $this->_attrcond);

        $field_id = ($attr['property'] !== null ? $attr['property'] : '');

        if (!isset($this->_op[$op])) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", unknown condition "'.$op.'"',
                546
            );
        }

        $operator = $this->_op[$op];

        $props = $this->_parser->getProperties();

        if (!isset($props[$field_id])) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", unknown property "'.$field_id.'"',
                547
            );
        }

        $field_pattern = ($attr['pattern'] !== null ? $attr['pattern'] : '');

        if ($this->type == 'update') {
            if ($props[$field_id]->table != $this->_parser->getPrimaryTable()) {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'conditions on foreign keys are not allowed on this type of method "'.$this->name.'"',
                    548
                );
            }
        }

        if (isset($cond['value'])) {
            $value = (string) $cond['value'];
        } else {
            $value = null;
        }

        if ($value !== null && $attr['expr'] !== null) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", condition "'.$op.'": no value and expression at the same time',
                549
            );
        }
        if ($value !== null) {
            $dbType = '';
            if ($op == 'isnull' || $op == 'isnotnull') {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'", value or expr are not allowed on condition "'.$op.'"',
                    550
                );
            }
            if ($op == 'binary_op') {
                if (!isset($attr['operator']) || empty($attr['operator'])) {
                    throw new ParserException(
                        $this->_parser->getDaoFile(),
                        'method "'.$this->name.'", condition "'.$op.'": operator is missing',
                        567
                    );
                }

                if (isset($attr['dbtype']) && !empty($attr['dbtype'])) {
                    $dbType = $attr['dbtype'];
                }
                $operator = $attr['operator'];
            }
            $this->_conditions->addCondition($field_id, $operator, $value, $field_pattern, false, $dbType);
        } elseif ($attr['expr'] !== null) {
            $dbType = '';
            if ($op == 'isnull' || $op == 'isnotnull') {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'", value or expr are not allowed on condition "'.$op.'"',
                    550
                );
            }
            if (($op == 'in' || $op == 'notin') && !preg_match('/^\$[a-zA-Z0-9_]+$/', $attr['expr'])) {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'", condition "'.$op.'", the expression for the in/notin operator should be a simple parameter name',
                    560
                );
            }
            if ($op == 'binary_op') {
                if (!isset($attr['operator']) || empty($attr['operator'])) {
                    throw new ParserException(
                        $this->_parser->getDaoFile(),
                        'method "'.$this->name.'", condition "'.$op.'": operator is missing',
                        567
                    );
                }
                if (isset($attr['dbtype']) && !empty($attr['dbtype'])) {
                    $dbType = $attr['dbtype'];
                }
                $operator = $attr['operator'];
            }
            $this->_conditions->addCondition($field_id, $operator, $attr['expr'], $field_pattern, true, $dbType);
        } else {
            if ($op != 'isnull' && $op != 'isnotnull') {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    ' method "'.$this->name.'", value or expression is missing on condition "'.$op.'"',
                    551
                );
            }
            $this->_conditions->addCondition($field_id, $operator, '', $field_pattern, false);
        }
    }

    private function _addOrder($order)
    {
        $attr = $this->_parser->getAttr($order, array('property', 'way'));

        $way = ($attr['way'] !== null ? $attr['way'] : 'ASC');

        if (substr($way, 0, 1) == '$') {
            if (!in_array(substr($way, 1), $this->_parameters)) {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'",  unknown parameter "'.$way.'" in the orderitem tag',
                    563
                );
            }
        }

        if ($attr['property'] != '') {
            $prop = $this->_parser->getProperties();
            if (isset($prop[$attr['property']])) {
                $this->_conditions->addItemOrder($attr['property'], $way, true);
            } elseif (substr($attr['property'], 0, 1) == '$') {
                if (!in_array(substr($attr['property'], 1), $this->_parameters)) {
                    throw new ParserException(
                        $this->_parser->getDaoFile(),
                        'method "'.$this->name.'",  unknown parameter "'.$way.'" in the orderitem tag',
                        563
                    );
                }
                $this->_conditions->addItemOrder($attr['property'], $way, true);
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'", property on item order "'.$attr['property'].'" is unknown',
                    552
                );
            }
        } else {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", orderitem tag is missing',
                553
            );
        }
    }

    private function _addValue($attr)
    {
        if (isset($attr['value'])) {
            $value = (string) $attr['value'];
        } else {
            $value = null;
        }

        $attr = $this->_parser->getAttr($attr, array('property', 'expr'));

        $prop = $attr['property'];
        $props = $this->_parser->getProperties();

        if ($prop === null) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'",  unknown property "'.$prop.'" on a <value> tag',
                554
            );
        }

        if (!isset($props[$prop])) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'",  unknown property "'.$prop.'" on a <value> tag',
                554
            );
        }

        if ($props[$prop]->table != $this->_parser->getPrimaryTable()) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", the property "'.$prop.'" should be owned by the primary table ',
                555
            );
        }

        if ($props[$prop]->isPK) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", primary key properties like "'.$prop.'" are not allowed in value tag',
                556
            );
        }

        if ($value !== null && $attr['expr'] !== null) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", value or expression is missing on a value tag',
                557
            );
        }
        if ($value !== null) {
            $this->_values[$prop] = array($value, false);
        } elseif ($attr['expr'] !== null) {
            $this->_values[$prop] = array($attr['expr'], true);
        } else {
            $this->_values[$prop] = array('', false);
        }
    }

    private function _addLimit($limit)
    {
        $attr = $this->_parser->getAttr($limit, array('offset', 'count'));

        $offset = $attr['offset'];
        $count = $attr['count'];

        if ($offset === null) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", attribute "offset" is missing on tag "limit"',
                512
            );
        }
        if ($count === null) {
            throw new ParserException(
                $this->_parser->getDaoFile(),
                'method "'.$this->name.'", attribute "count" is missing on tag "limit"',
                512
            );
        }

        if (substr($offset, 0, 1) == '$') {
            if (in_array(substr($offset, 1), $this->_parameters)) {
                $offsetparam = true;
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'", unknown parameter "'.$offset.'" in the limit tag',
                    558
                );
            }
        } else {
            if (is_numeric($offset)) {
                $offsetparam = false;
                $offset = intval($offset);
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'", bad value "'.$offset.'" on limit tag',
                    559
                );
            }
        }

        if (substr($count, 0, 1) == '$') {
            if (in_array(substr($count, 1), $this->_parameters)) {
                $countparam = true;
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method "'.$this->name.'", unknown parameter "'.$count.'" in the limit tag',
                    558
                );
            }
        } else {
            if (is_numeric($count)) {
                $countparam = false;
                $count = intval($count);
            } else {
                throw new ParserException(
                    $this->_parser->getDaoFile(),
                    'method"'.$this->name.'", bad value "'.$count.'" on limit tag',
                    559
                );
            }
        }
        $this->_limit = compact('offset', 'count', 'offsetparam', 'countparam');
    }
}
