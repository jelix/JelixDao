<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2021 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

interface DaoRecordInterface
{
    const ERROR_REQUIRED = 1;
    const ERROR_BAD_TYPE = 2;
    const ERROR_BAD_FORMAT = 3;
    const ERROR_MAXLENGTH = 4;
    const ERROR_MINLENGTH = 5;

    public function setFactory(DaoFactoryInterface $factory);

    /**
     * @return string the dao name as returned by the DaoFileInterface object
     */
    public function getDaoName();

    /**
     * @return array informations on all properties
     *
     * @see DaoFactoryInterface::getProperties()
     */
    public function getProperties();

    /**
     * @return string[] list of properties name which contains primary keys
     *
     * @see DaoFactoryInterface::getPrimaryKeyNames()
     */
    public function getPrimaryKeyNames();

    /**
     * check values in the properties of the record, according to the dao definition.
     *
     * @return array|false list of errors or false if ok
     */
    public function check();

    /**
     * set values on the properties which correspond to the primary
     * key of the record
     * This method accept a single or many values as parameter.
     */
    public function setPk(...$pk);

    /**
     * return the value of fields corresponding to the primary key.
     *
     * @return mixed the value or an array of values if there is several  pk
     */
    public function getPk();

    /**
     * save the record.
     *
     * @return int 1 if success (the number of affected rows). False if the query has failed.
     */
    public function save();
}
