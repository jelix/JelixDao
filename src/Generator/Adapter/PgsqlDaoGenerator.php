<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2007-2023 Laurent Jouanneau
 *
 * @see      https://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Generator\Adapter;

/**
 * driver for JelixDao compiler.
 */
class PgsqlDaoGenerator extends \Jelix\Dao\Generator\AbstractDaoGenerator
{
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';

    protected function buildUpdateAutoIncrementPK($pkai)
    {
        $table = $this->_dataParser->getTables()[$this->_dataParser->getPrimaryTable()];
        $sequence = $pkai->sequenceName;
        if ($table->schema) {
            $sequence = $table->schema.'.'.$sequence;
        }
        return '          $record->'.$pkai->name.'= $this->_conn->lastInsertId(\''.$sequence.'\');';
    }

    protected function getAutoIncrementPKField($using = null)
    {
        if ($using === null) {
            $using = $this->_dataParser->getProperties();
        }

        $tb = $this->_dataParser->getTables()[$this->_dataParser->getPrimaryTable()];

        foreach ($using as $id => $field) {
            if (!$field->isPK) {
                continue;
            }
            if ($field->autoIncrement) {
                if (!strlen($field->sequenceName)) {
                    $field->sequenceName = $tb->realName.'_'.$field->name.'_seq';

                }

                return $field;
            }
        }

        return null;
    }

    protected function buildRecordModifierFunctionBody()
    {
        $bodySrc = parent::buildRecordModifierFunctionBody();
        $binFields = $this->_getPropertiesBy('BinaryField');
        if ($binFields) {
            foreach ($binFields as $field) {
                $bodySrc[] = '    if ($record->'.$field->name.' !== null) { $record->'.$field->name.' = $rs->unescapeBin($record->'.$field->name.');}';
            }
        }
        return $bodySrc;
    }
}
