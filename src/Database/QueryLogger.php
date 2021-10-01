<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2021 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Dao\Database;

/**
 * Query logger for JelixDatabase, including the dao name in the formatting
 * message.
 */
class QueryLogger extends \Jelix\Database\Log\QueryLogger
{
    public function getDao()
    {
        foreach ($this->trace as $t) {
            if (isset($t['class'])) {
                $dao = '';
                $class = $t['class'];
                // support of Jelix Dao
                if ($class == '\\Jelix\\Dao\\AbstractDaoFactory') {
                    if (isset($t['object'])) {
                        $class = get_class($t['object']);
                    } else {
                        $dao = 'unknow dao, \\Jelix\\Dao\\AbstractDaoFactory';
                    }
                }

                if (preg_match('/^(.+)(Mysql|Pgsql|Sqlite|Oci|Sqlsrv)Factory$/', $class, $m)) {
                    $dao = $m[1];
                }
                elseif (preg_match('/^cDao_(.+)_Jx_(.+)_Jx_(.+)$/', $class, $m)) {
                    $dao = $m[1].'~'.$m[2];
                }
                if ($dao && isset($t['function'])) {
                    $dao .= '::'.$t['function'].'()';
                }
                if ($dao) {
                    return $dao;
                }
            }
        }

        return '';
    }

    public function getFormatedMessage()
    {
        $message = $this->query."\n".$this->getTime()."ms \n";
        $dao = $this->getDao();
        if ($dao) {
            $message .= ', from dao:'.$dao."\n";
        }
        if ($this->query != $this->originalQuery) {
            $message .= 'Original query: '.$this->originalQuery."\n";
        }

        $traceLog = '';
        foreach ($this->trace as $k => $t) {
            $traceLog .= "\n\t${k}\t".(isset($t['class']) ? $t['class'].$t['type'] : '').$t['function']."()\t";
            $traceLog .= (isset($t['file']) ? $t['file'] : '[php]').' : '.(isset($t['line']) ? $t['line'] : '');
        }

        return $message.$traceLog;
    }
}
