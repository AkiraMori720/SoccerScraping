<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2/16/2017
 * Time: 6:08 PM
 */
require_once "Constants.php";

class Database
{
    var $m_Conn = null;

    const DB_MAIN         = 'main';

    private $m_DbConf = array(
        'main' => array(
            'host' => 'localhost',
            'name' => 'soccer_bet',
            'user' => 'root',
            'pwd'  => '',
        ),
    );


    public function __construct()
    {
    }

    public function getConfig($dbConf = '') {
        if(isEmptyString($dbConf)) {
            $dbConf = Database::DB_MAIN;
        }

        if(!isset($this->m_DbConf[$dbConf])) {
            return null;
        }

        return $this->m_DbConf[$dbConf];
    }

    /**
     * ------------------------------------------------------------------------
     *  executeSQL :
     * ========================================================================
     *
     *
     * @param $sql
     * @return bool
     * @throws Exception
     *
     * ------------------------------------------------------------------------
     */
    public function executeSQL($sql) {
        if ($this->m_Conn == null) {
            return false;
        }

        $success = $this->m_Conn->query($sql);
        if ($success === TRUE) {
            ;
        }
        else {
            throw new Exception("Error description: " . mysqli_error($this->m_Conn));
        }

        return true;
    }

    /**
     * ------------------------------------------------------------------------
     *  executeSQLAsArray :
     * ========================================================================
     *
     *
     * @param $sql
     * @return array|null
     * @throws Exception
     *
     * ------------------------------------------------------------------------
     */
    public function executeSQLAsArray($sql) {
        if ( $this->m_Conn == null) {
            // The connection failed. What do you want to do?
            // You might want to show them something nice, but we will simply exit
            return null;
        }

        if (!$result =  $this->m_Conn->query($sql)) {
            throw new Exception("Error description: " . mysqli_error($this->m_Conn));
        }

        if ($result->num_rows === 0) {
            // Oh, no rows! Sometimes that's expected and okay, sometimes
            return array();
        }

        $resultSet = array();
        while ($actor = $result->fetch_assoc()) {
            $resultSet[] = $actor;
        }

        $result->free();

        return $resultSet;
    }

    /**
     * ------------------------------------------------------------------------
     *  insertSQL :
     * ========================================================================
     *
     *
     * @param $sql
     * @return bool
     * @throws Exception
     *
     * ------------------------------------------------------------------------
     */
    public function insertSQL($sql) {
        if ($this->m_Conn == null) {
            // The connection failed. What do you want to do?
            // You might want to show them something nice, but we will simply exit
            return false;
        }

        $success = $this->m_Conn->query($sql);

        if ($success === TRUE) {
            ;
        }
        else {
            throw new Exception("Error description: " . mysqli_error($this->m_Conn));
        }

        $insertedIDs =  $this->m_Conn->insert_id;

        return $success;
    }

    /**
     * ------------------------------------------------------------------------
     *  getIdAfterInsertSQL :
     * ========================================================================
     *
     *
     * @param $sql
     * @return int|null
     * @throws Exception
     *
     * ------------------------------------------------------------------------
     */
    public function getIdAfterInsertSQL($sql) {
        if($this->m_Conn == null) return -1;

        $success =  $this->m_Conn->query($sql);

        $insertedIDs = null;
        if ($success === TRUE) {
            $insertedIDs =  $this->m_Conn->insert_id;
        }
        else {
            echo $sql . PHP_EOL;
            throw new Exception("Error description: " . mysqli_error($this->m_Conn));
        }

        return $insertedIDs;
    }

    /**
     * ------------------------------------------------------------------------
     *  openDB :
     * ========================================================================
     *
     *
     * @param string $dbConf
     * @throws Exception
     *
     * ------------------------------------------------------------------------
     */
    public function openDB($dbConf = '') {
        if(isEmptyString($dbConf)) {
            $dbConf = Database::DB_MAIN;
        }

        if(!isset($this->m_DbConf[$dbConf])) {
            new Exception("Invalid DB info.");
        }

        $conf = $this->m_DbConf[$dbConf];

        $mysqli = new mysqli($conf['host'], $conf['user'], $conf['pwd'], $conf['name']);

        if ($mysqli->connect_errno) {
            // The connection failed. What do you want to do?
            // You might want to show them something nice, but we will simply exit
            throw new Exception("Error description: Connection error");
        }
        else {
            $this->m_Conn = $mysqli;
        }
    }


    public function closeDB() {
        if($this->m_Conn != null) {
            $this->m_Conn->close();
            $this->m_Conn = null;
        }
    }


    public function getConn() {
        return $this->m_Conn;
    }

    /**
     * ------------------------------------------------------------------------
     *  sqlAppendSetValues :
     * ========================================================================
     *
     *
     * @param $values
     * @param bool $useFieldName
     * @param bool $append
     * @param null $avoidFields
     * @param string $separator
     * @param bool $allowEmptyValue
     * @return string
     * Updated by C.R. 6/30/2020
     *
     * ------------------------------------------------------------------------
     */
    public function sqlAppendSetValues($values, $append = true, $useFieldName = true, $avoidFields=null, $separator=',', $allowEmptyValue = false) {
        $sql = "";

        $count = 0;
        foreach ($values as $field => $value) {
            if($avoidFields != null) {
                if(!is_array($avoidFields)) {
                    $avoidFields = explode(",", $avoidFields);
                }
            }
            else {
                $avoidFields = array();
            }

            if(in_array($field, $avoidFields)) {
                continue;
            }

            if(!isEmptyString($value) || $allowEmptyValue) {
                $value = mysqli_escape_string($this->m_Conn, $value);
                $sql .= ($count > 0 ? "{$separator}" : "");
                $sql .= ($useFieldName ? "`{$field}`=" : "") . "'{$value}'";

                $count ++;
            }
        }

        return isEmptyString($sql) ? "" : (($append ? "{$separator}" : "") . $sql);
    }

    /**
     * ------------------------------------------------------------------------
     *  getEscapedStr :
     * ========================================================================
     *
     *
     * @param $str
     * @return string
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function getEscapedStr($str) {
        return mysqli_escape_string($this->m_Conn, $str);
    }
}
