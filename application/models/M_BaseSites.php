<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/17/2020
 * Time: 10:14 PM
 */

require_once "M_DataTable.php";
class M_BaseSites extends M_DataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->m_strTable = "base_sites";
    }
}