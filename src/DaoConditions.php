<?php
/**
 * @author     Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau, Julien Issler, Yannick Le Guédart, Philippe Villiers
 *
 * @copyright  2001-2005 CopixTeam, 2005-2020 Laurent Jouanneau
 * @copyright  2008 Thomas
 * @copyright  2008 Julien Issler, 2009 Yannick Le Guédart
 * @copyright  2013 Philippe Villiers
 * This classes was get originally from the Copix project (CopixDAOSearchConditions, Copix 2.3dev20050901, http://www.copix.org)
 * Some lines of code are copyrighted 2001-2005 CopixTeam (LGPL licence).
 * Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
 * and this classes was adapted for Jelix by Laurent Jouanneau
 *
 * @see     http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao;


/**
 * container for all criteria of a query.
 */
class DaoConditions
{
    /**
     * @var DaoCondition
     */
    public $condition;

    /**
     * the orders we wants the list to be.
     */
    public $order = array();

    /**
     * @var DaoCondition the condition we actually are browsing.
     */
    private $_currentCondition;

    /**
     * @param string $glueOp the logical operator which links each conditions : AND or OR
     */
    public function __construct($glueOp = 'AND')
    {
        $this->condition = new DaoCondition($glueOp);
        $this->_currentCondition = $this->condition;
    }

    /**
     * add an order clause.
     *
     * @param string $field_id    the property name used to order results
     * @param string $way         the order type : asc or desc
     * @param bool   $allowAnyWay true if the value of $way should be checked. Internal use.
     *                            Not recommended because it may cause security issues
     *
     * @throws Exception
     */
    public function addItemOrder($field_id, $way = 'ASC', $allowAnyWay = false)
    {
        if (!$allowAnyWay && strtoupper($way) != 'DESC' && strtoupper($way) != 'ASC') {
            throw new Exception('Invalid given operator "'.$way.'"', 503);
        }

        $this->order[$field_id] = $way;
    }

    /**
     * says if there are no conditions nor order.
     *
     * @return bool false if there isn't condition
     */
    public function isEmpty()
    {
        return $this->condition->isEmpty() &&
               (count($this->order) == 0);
    }

    /**
     * says if there are no conditions.
     *
     * @return bool false if there isn't condition
     *
     * @since 1.0
     */
    public function hasConditions()
    {
        return !$this->condition->isEmpty();
    }

    /**
     * starts a new condition group.
     *
     * @param string $glueOp the logical operator which links each conditions in the group : AND or OR
     *
     * @throws Exception
     */
    public function startGroup($glueOp = 'AND')
    {
        $glueOp = strtoupper($glueOp);
        if ($glueOp != 'AND' && $glueOp != 'OR') {
            throw new Exception('Invalid given operator "'.$glueOp.'"', 503);
        }
        $cond = new DaoCondition($glueOp, $this->_currentCondition);
        $this->_currentCondition = $cond;
    }

    /**
     * ends a condition group.
     */
    public function endGroup()
    {
        if ($this->_currentCondition->parent !== null) {
            if (!$this->_currentCondition->isEmpty()) {
                $this->_currentCondition->parent->group[] = $this->_currentCondition;
            }
            $this->_currentCondition = $this->_currentCondition->parent;
        }
    }

    /**
     * adds a condition.
     *
     * @param string $field_id      the property name on which the condition applies
     * @param string $operator      the sql operator
     * @param string $value         the value which is compared to the property
     * @param string $field_pattern the pattern to use on the property (WHERE clause)
     * @param bool   $foo           parameter for internal use : don't use it or set to false
     *
     * @throws Exception
     */
    public function addCondition($field_id, $operator, $value, $field_pattern = '%s', $foo = false)
    {
        $operator = trim(strtoupper($operator));
        if (preg_match('/^[^\w\d\s;\(\)]+$/', $operator) ||
            in_array($operator, array('LIKE', 'NOT LIKE', 'ILIKE', 'IN', 'NOT IN',
                'IS', 'IS NOT', 'IS NULL', 'IS NOT NULL', 'MATCH', 'REGEXP',
                'NOT REGEXP', '~', '!~', '~*', '!~*', 'RLIKE', 'SOUNDS LIKE',
                'BETWEEN', ))
        ) {
            $this->_currentCondition->conditions[] = array(
                'field_id' => $field_id,
                'field_pattern' => $field_pattern,
                'value' => $value,
                'operator' => $operator, 'isExpr' => $foo, );
        } else {
            throw new Exception('Invalid given operator "'.$operator.'"', 503);
        }
    }
}
