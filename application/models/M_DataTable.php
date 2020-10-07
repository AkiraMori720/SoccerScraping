<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 5/27/2017
 * Time: 6:00 PM
 */
require_once "M_Super.php";

class M_DataTable extends M_Super
{
    public $m_allFields = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function getCount($sql, $fieldToCount, $dbConn = null) {
        if(strlen($sql) == 0 || strlen($fieldToCount) == 0) return 0;

        $sql = "SELECT count({$fieldToCount}) cnt_val FROM ({$sql}) cnt_data";
        $records = ($dbConn == null) ? $this->executeSQLAsArray($sql) : $dbConn->executeSQLAsArray($sql);

        return $records[0]['cnt_val'];
    }

    /**
     * Extract current draw number from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @return int
     */
    public function getCurrentDrawNo($data) {
        return array_key_exists('draw', $data) ? $data['draw'] : -1;
    }


    /**
     * Extract start index from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @return int
     */
    public function getPageStartIndex($data) {
        return array_key_exists('start', $data) ? $data['start'] : -1;
    }

    /**
     * Extract page size from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @return int
     */
    public function getPageSize($data) {
        return array_key_exists('length', $data) ? $data['length'] : -1;
    }


    /**
     * Extract sort fields from data in paging mode
     * --------------------------------------------------------------
     *
     * @param $data
     * @param $paramFields
     * @return array
     */
    public function getSortFields($data, $paramFields) {
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
    public function getSearchValue($data) {
        $search = null;

        if(array_key_exists('search', $data)) {
            $value = $data['search'];

            if(strlen($value['value']) > 0) { $search = $value['value']; }
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
        $search = $this->getEscapedStr($this->getSearchValue($params));

        $where = "";
        if(!isEmptyString($search)) {
//            $where = " WHERE " .$searchFields[3]." LIKE '%".$search."%' ";

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
                if (strlen($where) > 0) { $where .= " AND "; }
                else                    { $where = " WHERE "; }
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