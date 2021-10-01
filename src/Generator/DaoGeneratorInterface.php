<?php
/**
 * @contributor Laurent Jouanneau
 * @copyright  2021 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Generator;

use Jelix\Dao\Parser\XMLDaoParser;
use Jelix\Database\Schema\SqlToolsInterface;

/**
 * Interface for generators which creates php class from dao xml file.
 *
 * It is called by Compiler
 *
 * @see Compiler
 */
interface DaoGeneratorInterface
{
    /**
     * constructor.
     *
     * @param SqlToolsInterface     $tools
     * @param XMLDaoParser   $daoParser
     */
    public function __construct(
        SqlToolsInterface $tools,
        XMLDaoParser $daoParser
    );

    /**
     * build all classes.
     */
    public function buildClasses();
}
