<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 5/20/2017
 * Time: 11:41 AM
 */
require_once "M_DataTable.php";

class M_User extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "sys_user";
    }

    public function getFullUserInfo($uid) {
        $sql = "SELECT * FROM sys_user WHERE uid=" . $uid;

        $records = $this->executeSQLAsArray($sql);

        return sizeof($records) > 0 ? $records[0] : null;
    }


    public function addNewUser($userName, $userPwd, $fullName, $email) {
        $c_ip = get_client_ip();
        $sql = "insert into sys_user(user_name, user_pwd, email, real_name, c_ip) 
                values('{$userName}',md5('{$userPwd}'),'{$email}','{$fullName}','{$c_ip}')";

        return $this->executeSQL($sql);
    }


    public function updateUser($userID, $userPwd, $fullName, $email) {
        $c_ip = get_client_ip();
        $sql = "UPDATE sys_user 
                SET user_pwd=md5('{$userPwd}'), email='{$email}', real_name='{$fullName}', c_ip='{$c_ip}' 
                WHERE uid={$userID}";

        return $this->executeSQL($sql);
    }


    public function updatePassword($uid, $password) {
        $sql = "UPDATE sys_user SET user_pwd=md5('{$password}') WHERE uid='{$uid}'";
        return $this->executeSQL($sql);
    }


    public function getUsers_DT($params) {
        $fields = array(
            'index_no',
            'uid',
            'user_name',
            'user_type',
            'real_name',
            'email',
            'status',
            'created_at'
        );

        // SQL
        $sql_all = "SELECT uid, user_name, user_type, email, real_name, status, created_at 
                    FROM sys_user WHERE user_type NOT IN(" . USER_TYPE_WEBMASTER . ")";

        $filter = "";
        if(strlen($filter) > 0) { $sql_all .= " WHERE {$filter}"; }

        $data = array();
        $data['recordsTotal'] = $this->getCount($sql_all, 'uid');

        $tbl_alias = 'entire';

        ///////////////////////////////////////////////
        // Get Filtered Count
        ///////////////////////////////////////////////
        $sql_flt = "SELECT * FROM (".$sql_all.") ".$tbl_alias;
        $sql_flt = $this->appendFilterToSQL($params, $sql_flt, $tbl_alias, $fields, array('index_no'));

        $data['recordsFiltered'] = $this->getCount($sql_flt, 'uid');

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


    public function updateStatus($uid, $status) {
        $sql = "UPDATE sys_user SET status={$status} WHERE uid={$uid}";
        return $this->executeSQL($sql);
    }
}