<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/17/2020
 * Time: 10:14 PM
 */

require_once "M_DataTable.php";
class M_BaseSeasons extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "base_seasons";
    }

    /**
     * ------------------------------------------------------------------------
     *  saveSeason :
     * ========================================================================
     *
     *
     * @param $newSeason
     * Updated by C.R. 8/8/2020
     *
     * ------------------------------------------------------------------------
     */
    public function saveSeason($newSeason) {
        if(!isEmptyString($newSeason)) {
            $newSeason = $this->getEscapedStr($newSeason);

            // Insert or update clubs
            $sql = "INSERT IGNORE INTO {$this->m_strTable} SET season='{$newSeason}'";
            $this->executeSQL($sql);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  setActiveSeason :
     * ========================================================================
     *
     *
     * @param $season
     * Updated by C.R. 8/8/2020
     *
     * ------------------------------------------------------------------------
     */
    public function setActiveSeason($season) {
        if(!isEmptyString($season)) {
            $season = $this->getEscapedStr($season);

            $sql = "UPDATE {$this->m_strTable} SET `status`='inactive' WHERE season<>'{$season}'";
            $this->executeSQL($sql);

            $sql = "UPDATE {$this->m_strTable} SET `status`='active' WHERE season='{$season}'";
            $this->executeSQL($sql);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  getActiveSeason :
     * ========================================================================
     *
     *
     * @return string
     * Updated by C.R. 8/8/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getActiveSeason() {
        $sql = "SELECT season FROM {$this->m_strTable} WHERE `status`='active' ORDER BY season DESC LIMIT 1";
        $records = $this->executeSQLAsArray($sql);

        return sizeof($records) > 0 ? $records[0]['season'] : '';
    }
}