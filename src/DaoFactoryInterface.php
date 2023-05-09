<?php
/**
 *
 * @author      Laurent Jouanneau
 * @copyright   2021-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao;

use Jelix\Database\ConnectionInterface;
use Jelix\Database\ResultSetInterface;

/**
 * interface for dao factory classes
 */
interface DaoFactoryInterface
{
    /**
     * @param ConnectionInterface $conn the database connection
     */
    public function __construct(ConnectionInterface $conn);

    /**
     * @return string the dao name as returned by the DaoFileInterface object
     */
    public function getDaoName();

    /**
     * @return ConnectionInterface
     */
    public function getConnection();

    /**
     * @return array informations on tables
     * <pre> array (
     *   'name' => ' the table alias',
     *   'realname' => 'the real name of the table',
     *   'pk' => array ( list of primary keys name ),
     *   'fk' => array ( list of foreign keys name ),
     *   'fields' => array ( list of property name attached to this table )
     * )
     * </pre>
     */
    public function getTables();

    /**
     * @return string the id (alias or realname) of the primary table
     */
    public function getPrimaryTable();

    /**
     * informations on all properties.
     *
     * keys are property name, and values are an array like that :
     * <pre> array (
     *  'name' => 'name of property',
     *  'fieldName' => 'name of fieldname',
     *  'regExp' => NULL, // or the regular expression to test the value
     *  'required' => true/false,
     *  'isPK' => true/false, //says if it is a primary key
     *  'isFK' => true/false, //says if it is a foreign key
     *  'datatype' => '', // type of data : string
     *  'unifiedType'=> '' // the corresponding unified type
     *  'table' => 'grp', // alias of the table the property is attached to
     *  'updatePattern' => '%s',
     *  'insertPattern' => '%s',
     *  'selectPattern' => '%s',
     *  'sequenceName' => '', // name of the sequence when field is autoincrement
     *  'maxlength' => NULL, // or a number
     *  'minlength' => NULL, // or a number
     *  'ofPrimaryTable' => true/false
     *  'autoIncrement'=> true/false
     * ) </pre>
     *
     * @return array informations on all properties
     */
    public function getProperties();

    /**
     * list of id of primary properties.
     *
     * @return array list of properties name which contains primary keys
     */
    public function getPrimaryKeyNames();

    /**
     * Hook called during insert/update/delete
     *
     * @param DaoHookInterface $hook
     * @return void
     */
    public function setHook(DaoHookInterface $hook);

    /**
     * creates a record object for the dao.
     *
     * @return DaoRecordInterface
     */
    public function createRecord();
    
    /**
     * return all records.
     *
     * @return ResultSetInterface
     */
    public function findAll();

    /**
     * return the number of all records.
     *
     * @return int the count
     */
    public function countAll();
    
    /**
     * return the record corresponding to the given key.
     *
     * @param array|string $pk the primary key
     * @return DaoRecordInterface
     *
     * @throws Exception
     */
    public function get(...$pk);

    /**
     * delete a record corresponding to the given key.
     *
     * @param array|string $pk the primary key
     * @return int the number of deleted record
     * @throws Exception
     */
    public function delete(...$pk);

    /**
     * save a new record into the database
     * if the dao record has an autoincrement key, its corresponding property is updated.
     *
     * @param DaoRecordInterface $record the record to save
     *
     * @return int 1 if success (the number of affected rows). False if the query has failed.
     */
    public function insert($record);

    /**
     * save a modified record into the database.
     *
     * @param DaoRecordInterface $record the record to save
     *
     * @return int 1 if success (the number of affected rows). False if the query has failed.
     */
    public function update($record);

    /**
     * return all record corresponding to the conditions stored into the
     * DaoConditions object.
     * you can limit the number of results by given an offset and a count.
     *
     * @param DaoConditions $searchCond
     * @param int            $limitOffset
     * @param int            $limitCount
     *
     * @return ResultSetInterface
     */
    public function findBy(DaoConditions $searchCond, $limitOffset = 0, $limitCount = null);

    /**
     * return the number of records corresponding to the conditions stored into the
     * DaoConditions object.
     *
     * @param DaoConditions $searchCond
     * @param null|mixed     $distinct
     *
     * @return int the count
     */
    public function countBy(DaoConditions $searchCond, $distinct = null);

    /**
     * delete all record corresponding to the conditions stored into the
     * DaoConditions object.
     *
     * @param DaoConditions $searchCond
     *
     * @return int number of deleted rows
     */
    public function deleteBy(DaoConditions $searchCond);
}
