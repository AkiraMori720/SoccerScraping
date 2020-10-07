<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/12/2020
 * Time: 12:01 AM
 */

require_once "M_DataTable.php";
class M_Referee extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "base_referee";
    }

    /**
     * ------------------------------------------------------------------------
     *  findRefereeByName :
     * ========================================================================
     *
     *
     * @param $name
     * @return null
     * Updated by C.R. 6/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function findRefereeByName($name) {
        $found = null;
        if(strlen($name) > 0) {
            $sql = "SELECT * FROM {$this->m_strTable} WHERE `referee_name`='" . $this->getEscapedStr($name) . "'";
            $records = $this->executeSQLAsArray($sql);

            if(sizeof($records) > 0) {
                $found = $records[0];
            }
            else {
                $records = $this->executeSQLAsArray("SELECT * FROM {$this->m_strTable}");
                foreach ($records as $record) {
                    $refName = $record['referee_name'];

                    $percent = 0;
                    similar_text(strtolower($name), strtolower($refName), $percent);
                    if($percent >= 90) {
                        $found = $record;
                        break;
                    }
                }
            }
        }

        return $found;
    }

    /**
     * ------------------------------------------------------------------------
     *  getRefereeDetailsBy :
     * ========================================================================
     *
     *
     * @param $date
     * @param $season
     * @param string $refereeID
     * @return array
     * Updated by C.R. 6/16/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getRefereeDetailsBy($date, $season, $refereeID = '')
    {
        $filter = "`match_date`<='{$date}' AND season='{$season}'";
        if (!isEmptyString($refereeID)) {
            $filter .= (isEmptyString($filter) ? '' : ' AND ') . "referee_id='{$refereeID}'";
        }

        $sql = "SELECT * FROM soccerbase_referee" . (isEmptyString($filter) ? '' : " WHERE {$filter}");
        $sql.= " ORDER BY referee_id, country, match_date desc";
        $records = $this->executeSQLAsArray($sql);

        $referees = array();
        foreach ($records as $record) {
            $country = getValueInArray($record, 'country');
            $referee_id = getValueInArray($record, 'referee_id');

            if(!isset($referees[$country])) {
                $referees[$country] = array();
            }

            if(!isset($referees[$country][$referee_id])) {
                $referees[$country][$referee_id] = array();
            }

            $referees[$country][$referee_id][] = array(
                'season'        => getValueInArray($record, 'season'),
                'division'      => getValueInArray($record, 'division'),
                'match_date'    => getValueInArray($record, 'match_date'),
                'home_team'     => getValueInArray($record, 'home_team'),
                'away_team'     => getValueInArray($record, 'away_team'),
                'match_result'  => removeLettersInScore(getValueInArray($record, 'match_result', '0-0')),
                'yellow_card'   => getValueInArray($record, 'yellow_card'),
                'red_card'      => getValueInArray($record, 'red_card'),
            );
        }

        return $referees;
    }
}