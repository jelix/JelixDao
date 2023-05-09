<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2023 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

/**
 * Context to load dao from the daos directory of a Jelix module.
 *
 * Daos file of a Jelix module have specific suffixes.
 */
class JelixModuleContext extends Context
{

    protected $daoXmlSuffix = '.dao.xml';
    protected $daoXmlSuffixRe = '/\\.dao\\.xml$/';
    protected $daoPhpSuffix = '.daorecord.php';
    protected $daoPhpSuffixRe = '/\\.daorecord\\.php$/';
}