<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/18/2020
 * Time: 12:54 AM
 */

require_once "M_DataTable.php";
class M_BaseCountry extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "base_country";
    }

    /**
     * ------------------------------------------------------------------------
     *  allCountries :
     * ========================================================================
     *
     *
     * @return mixed
     * Updated by C.R. 8/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function allCountries() {
        $sql = "SELECT country, MAX(iso2_code) iso2_code FROM {$this->m_strTable} WHERE season IN (SELECT season FROM base_seasons WHERE `status`='active') GROUP BY country";
        return $this->executeSQLAsArray($sql);
    }

    /**
     * ------------------------------------------------------------------------
     *  saveCountry :
     * ========================================================================
     *
     *
     * @param $params
     * @return mixed|string
     * @throws Exception
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveCountry($params) {
        $id = getValueInArray($params, 'id');

        $season     = getValueInArray($params, 'season');
        $country    = getValueInArray($params, 'country');
        $iso2Code   = getValueInArray($params, 'iso2_code');
        $oddsportal = getValueInArray($params, 'oddsportal');
        $soccervista= getValueInArray($params, 'soccervista');
        $soccerway  = getValueInArray($params, 'soccerway');
        $predictz   = getValueInArray($params, 'predictz');
        $windrawwin = getValueInArray($params, 'windrawwin');
        $soccerbase = getValueInArray($params, 'soccerbase');

        if( isEmptyString($season) || isEmptyString($country) || isEmptyString($iso2Code) || isEmptyString($oddsportal) ) {
            throw new Exception("Missing values!");
        }

        $values = array(
            'iso2_code'  => $iso2Code,
            'oddsportal' => $oddsportal,
            'soccervista'=> $soccervista,
            'soccerway'  => $soccerway,
            'predictz'   => $predictz,
            'windrawwin' => $windrawwin,
            'soccerbase' => $soccerbase
        );

        if(!isEmptyString($id)) {
            $sql = "UPDATE {$this->m_strTable} SET " . $this->sqlAppendSetValues($values, false);
            $sql.= " WHERE id='{$id}'";

            $this->executeSQL($sql);
        }
        else {
            $values['season']  = $season;
            $values['country'] = $country;

            $sql = "INSERT INTO {$this->m_strTable} SET " . $this->sqlAppendSetValues($values, false);
            $this->executeSQL($sql);
            $id = $this->getLastInsertedID();
        }

        return $id;
    }

    /**
     * ------------------------------------------------------------------------
     *  importBySeason :
     * ========================================================================
     *
     *
     * @param $season
     * Updated by C.R. 8/12/2020
     *
     * ------------------------------------------------------------------------
     */
    public function importBySeason($season) {
        $tmp = explode('/', $season);
        $year = $tmp[0];
        $prevYear = $year - 1;

        $prevSeason = $prevYear . "/" . substr($year, 2);

        $escaped = $this->getEscapedStr($prevSeason);
        $sql = "SELECT country, iso2_code, oddsportal, soccervista, soccerway, predictz, windrawwin, soccerbase FROM {$this->m_strTable} WHERE season='{$escaped}'";
        $records = $this->executeSQLAsArray($sql);

        foreach ($records as $record) {
            $record['season'] = $season;
            $sql = "INSERT IGNORE INTO {$this->m_strTable} SET " . $this->sqlAppendSetValues($record, false);
            $this->executeSQL($sql);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  getList_DT :
     * ========================================================================
     *
     *
     * @param $params
     * @return array
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getList_DT($params) {
        $fields = array(
            'index_no',
            'country',
            'iso2_code',
            'oddsportal',
            'soccervista',
            'soccerway',
            'predictz',
            'windrawwin',
            'soccerbase',
            'id'
        );

        // SQL
        $sql_all = "SELECT * 
                    FROM {$this->m_strTable}";

        $filter = "";
        if(isset($params['season'])) {
            $value = $this->getEscapedStr(trim($params['season']));
            if(strlen($value) > 0) { $filter .= (strlen($filter) > 0 ? " AND " : "")."`season`='{$value}'"; }
        }

        if(strlen($filter) > 0) { $sql_all .= " WHERE {$filter}"; }

        $data = array();
        $data['recordsTotal'] = $this->getCount($sql_all, 'id');

        $tbl_alias = 'entire';

        ///////////////////////////////////////////////
        // Get Filtered Count
        ///////////////////////////////////////////////
        $sql_flt = "SELECT * FROM (".$sql_all.") ".$tbl_alias;
        $sql_flt = $this->appendFilterToSQL($params, $sql_flt, $tbl_alias, $fields, array('index_no'));

        $data['recordsFiltered'] = $this->getCount($sql_flt, 'id');

        ///////////////////////////////////////////////
        // Get Records of current page
        ///////////////////////////////////////////////
        $sql_flt = $this->appendOrderByToSQL($params, $sql_flt, $tbl_alias, $fields, array('index_no'));
        // Add Limitation; Default page size 50
        $sql_flt = $this->appendLimitToSQL($params, $sql_flt);

        $records = $this->executeSQLAsArray($sql_flt);

        $startNo = $this->getPageStartIndex($params);
        for($i = 0; $i < sizeof($records); $i++) {
            $records[$i]['index_no'] = $startNo + $i + 1;
        }

        $data['data'] = $records;

        // Current Draw
        $data['draw'] = $this->getCurrentDrawNo($params);

        return $data;
    }

    /**
     * ------------------------------------------------------------------------
     *  getCountries :
     * ========================================================================
     *
     *
     * @param string $selectedCountry
     * @return mixed
     * Updated by C.R. 6/22/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getCountries($selectedCountry = '')
    {
        $sql = "SELECT * FROM {$this->m_strTable}";
        if (!isEmptyString($selectedCountry)) {
            $selectedCountry = $this->getEscapedStr($selectedCountry);
            $sql .= " WHERE country = '{$selectedCountry}'";
        }

        return $this->executeSQLAsArray($sql);
    }
}