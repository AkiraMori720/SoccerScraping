<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2/16/2017
 * Time: 6:03 PM
 */
class Response
{
    public $m_Code = RES_C_SUCCESS;
    public $m_Msg  = "";
    public $m_Data = null;

    public function __construct()
    {
        ;
    }

    public function toJSON() {
        $data = array();

        if(strlen($this->m_Msg) == 0) {
            if(lang($this->m_Code) != null) {
                $this->m_Msg = lang($this->m_Code);
            }
        }

        $data['code'] = $this->m_Code;
        $data['msg']  = $this->m_Msg;
        $data['data'] = ($this->m_Data == null) ? "" : $this->m_Data;

        return json_encode($data);
    }

    public function setResponse($code, $msg, $data = null)
    {
        $this->m_Code = $code;
        $this->m_Msg = $msg;
        if ($data != null) {
            $this->m_Data = $data;
        }
    }

    public function isSuccess()
    {
        return $this->m_Code == RES_C_SUCCESS;
    }
}