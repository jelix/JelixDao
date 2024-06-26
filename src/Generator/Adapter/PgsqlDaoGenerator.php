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

    protected function buildEndOfClass()
    {
        $fields = $this->_getPropertiesBy('BinaryField');
        if (count($fields)) {
            $src = '    protected function finishInitResultSet($rs) {
        parent::finishInitResultSet($rs);
        $rs->addModifier(array($this, \'unescapeRecord\'));
    }'."\n";

            // we build the callback function for the resultset, to unescape
            // binary fields.
            $src .= 'public function unescapeRecord($record, $resultSet) {'."\n";
            foreach ($fields as $f) {
                $src .= '$record->'.$f->name.' = $resultSet->unescapeBin($record->'.$f->name.");\n";
            }
            $src .= '}';

            return $src;
        }

        return '';
    }
}
