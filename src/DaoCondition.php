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
 * content a sub group of conditions.
 */
class DaoCondition
{
    /**
     * the parent group if any.
     *
     * @var DaoCondition
     */
    public $parent;

    /**
     * the conditions in this group.
     *
     * @var array contains these items:
     *            - field_id
     *            - field_pattern
     *            - value
     *            - operator
     *            - isExpr
     *            - dbType ('' or 'mysql', 'pgsql' ...)
     */
    public $conditions = array();

    /**
     * the sub groups.
     *
     * @var DaoCondition[]
     */
    public $group = array();

    /**
     * the kind of group (AND/OR).
     *
     * @var string
     */
    public $glueOp;

    public function __construct($glueOp = 'AND', ?DaoCondition $parent = null)
    {
        $this->parent = $parent;
        $this->glueOp = $glueOp;
    }

    public function isEmpty()
    {
        return empty($this->conditions) && empty($this->group);
    }
}
