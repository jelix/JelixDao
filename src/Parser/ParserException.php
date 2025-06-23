<?php

/**
 * @author     Laurent Jouanneau
 * @contributor Laurent Jouanneau
 *
 * @copyright   2005-2020 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Parser;

use Jelix\Dao\DaoFileInterface;

/**
 * Exception for Dao compiler.
 *
 */
class ParserException extends \jDaoXmlException
{
    /**
     * @var DaoFileInterface
     */
    protected $daoFile;

    public function __construct(DaoFileInterface $daoFile, $message = "", $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->daoFile = $daoFile;

        $this->message = $message . ' (dao: '.$daoFile->getName().', file: '.$daoFile->getPath().')';
    }

    public function getDao()
    {
        return $this->daoFile;
    }
}
