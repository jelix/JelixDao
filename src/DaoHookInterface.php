<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2021 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Dao;

interface DaoHookInterface
{
    const EVENT_BEFORE = 0;
    const EVENT_AFTER = 1;

    /**
     * call before and after an insert
     *
     * @param string $daoName the dao file descriptor
     * @param DaoRecordInterface $record the record to insert
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     * @return void
     */
    public function onInsert(string $daoName, DaoRecordInterface $record, $when);

    /**
     * call before and after an update
     *
     * @param string $daoName the dao file descriptor
     * @param DaoRecordInterface $record the record to update
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     * @return void
     */
    public function onUpdate(string $daoName, DaoRecordInterface $record, $when);

    /**
     * call before and after a delete
     *
     * @param string $daoName the dao file descriptor
     * @param array $keys the key of the record to delete
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     * @param int|null|false $result the number of affected rows. False if the query has failed.
     * @return void
     */
    public function onDelete(string $daoName, $keys, $when, $result = null);

    /**
     * call before and after a delete
     *
     * @param string $daoName the dao file descriptor
     * @param DaoConditions $searchCond the conditions to delete records
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     * @param int|null|false $result the number of affected rows. False if the query has failed.
     * @return void
     */
    public function onDeleteBy(string $daoName, DaoConditions $searchCond, $when, $result = null);


    /**
     * @param string $daoName
     * @param string $methodName
     * @param array  $parameters
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     *
     * @return void
     */
    public function onCustomMethod(string $daoName, string $methodName, $parameters, $when);
}
