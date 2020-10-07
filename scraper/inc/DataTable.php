<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/6/2018
 * Time: 10:05 PM
 */

require_once "Constants.php";
require_once "Database.php";

class DataTable
{
    var $m_nPageSize = 50;
    var $m_DBConn = null;

    public function __construct()
    {
        $this->m_DBConn = new Database();
    }

    /**
     * ------------------------------------------------------------------------
     *  getCount :
     * ========================================================================
     *
     *
     * @param $sql
     * @param $fieldToCount
     * @return int
     * @throws Exception
     * Updated by C.R. 6/25/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getCount($sql, $fieldToCount) {
        if(strlen($sql) == 0 || strlen($fieldToCount) == 0) return 0;

        $sql = "SELECT count({$fieldToCount}) cnt_val FROM ({$sql}) cnt_data";
        $records = $this->m_DBConn->executeSQLAsArray($sql);

        return $records[0]['cnt_val'];
    }

    /**
     * Extract current draw number from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @return int
     */
    protected function getCurrentDrawNo($data) {
        $num = 1;

        if(array_key_exists('draw', $data))
            $num = $data['draw'];

        return $num;
    }


    /**
     * Extract start index from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @return int
     */
    protected function getPageStartIndex($data) {
        $start = -1;

        if(array_key_exists('start', $data))
            $start = $data['start'];

        return $start;
    }

    /**
     * Extract page size from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @return int
     */
    protected function getPageSize($data) {
        $pageSize = -1;

        if(array_key_exists('length', $data))
            $pageSize = $data['length'];

        return $pageSize;
    }


    /**
     * Extract sort fields from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @param $paramFields
     * @return array
     */
    protected function getSortFields($data, $paramFields) {
        $fields = array();

        if(array_key_exists('order', $data)) {
            $orders = $data['order'];
            foreach($orders as $order) {
                $fields[] = $paramFields[$order['column']]." ".$order['dir'];
            }
        }

        return $fields;
    }


    /**
     * Extract search keyword from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @return null
     */
    protected function getSearchValue($data) {
        $search = null;

        if(array_key_exists('search', $data)) {
            $value = $data['search'];

            if(strlen($value['value']) > 0) {
                $search = $value['value'];
            }
        }

        return $search;
    }


    /**
     * ------------------------------------------------------------------------
     *  appendFilterToSQL :
     * ========================================================================
     *
     *
     * @param $params
     * @param $sql
     * @param $tbl_alias
     * @param $searchFields
     * @param $avoidFields
     * @return string
     *
     * ------------------------------------------------------------------------
     */
    public function appendFilterToSQL($params, $sql, $tbl_alias, $searchFields, $avoidFields = array()) {
        $search = $this->getSearchValue($params);

        $where = "";
        if(!isEmptyString($search)) {
            $cond = "";
            for($i = 0; $i < sizeof($searchFields); $i++) {
                $field = $searchFields[$i];
                if(!in_array($field, $avoidFields) ) {
                    $cond .= (strlen($cond) > 0) ? " OR " : " ";
                    $cond .= $tbl_alias.".".$field." LIKE '%".$search."%' ";
                }
            }

            if(strlen($cond) > 0) { $where = " WHERE ".$cond; }
        }

        if(array_key_exists('columns', $params)) {
            $searches = $params['columns'];

            $search_cond = "";
            for($col = 0; $col < sizeof($searches); $col++ ) {
                $search_value = $searches[$col]['search']['value'];
                if(strlen($search_value) > 0) {
                    if (strlen($search_cond) > 0) $search_cond .= " AND ";
                    $search_cond .= $tbl_alias.".".$searchFields[$col] . " = '" . $search_value . "'";
                }
            }

            if(strlen($search_cond) > 0) {
                if (strlen($where) > 0) {
                    $where .= " AND ";
                }
                else {
                    $where = " WHERE ";
                }
                $where .= $search_cond;
            }
        }

        return $sql.$where;
    }


    /**
     * ------------------------------------------------------------------------
     *  appendOrderByToSQL :
     * ========================================================================
     *
     *
     * @param $params
     * @param $sql
     * @param $tbl_alias
     * @param $sortableFields
     * @param $defaultFields
     * @return string
     *
     * ------------------------------------------------------------------------
     */
    public function appendOrderByToSQL($params, $sql, $tbl_alias, $sortableFields, $defaultFields) {
        $sortFields = $this->getSortFields($params, $sortableFields);

        if(sizeof($sortFields) == 0) {
            $sortFields = $defaultFields;
        }

        if(sizeof($sortFields) > 0) {
            $sql.= " ORDER BY ";
            for($i = 0; $i < sizeof($sortFields); $i++) {
                $sql .= ($i > 0 ? "," : "");
                $sql .= $tbl_alias.".".$sortFields[$i];
            }
        }

        return $sql;
    }


    /**
     * ------------------------------------------------------------------------
     *  appendLimitToSQL :
     * ========================================================================
     *
     *
     * @param $params
     * @param $sql
     * @return string
     *
     * ------------------------------------------------------------------------
     */
    public function appendLimitToSQL($params, $sql) {
        $pageStart = $this->getPageStartIndex($params);
        $pageSize = $this->getPageSize($params);

        if ($pageSize == -1 ) return $sql;

        $sql .= ($pageStart > -1 ? " LIMIT ".abs($pageStart).",".($pageSize > -1 ? $pageSize : '50') : '');

        return $sql;
    }
}