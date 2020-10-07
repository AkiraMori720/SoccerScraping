<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/24/2017
 * Time: 10:32 PM
 */

use \Defuse\Crypto\Core;
use \Defuse\Crypto\Crypto;
use \Defuse\Crypto\Key;
use Defuse\Crypto\Exception as Ex;

class MY_Model extends CI_Model
{
    public $m_strTable = '';

    public function __construct()
    {
        parent::__construct();
    }


    public function encryptedValue($val, $salt = '') {
        if(strlen($salt) == 0) {
            $salt = uniqid(mt_rand(), true);
        }
//        $result = hash_hmac('sha1', $val, $salt);

        $encrypted = Crypto::encryptWithPassword($val, $salt);

        return array("val" => $encrypted, "salt" => $salt);
    }


    public function decryptValue($val, $salt) {
        if($val == null || sizeof($val) == 0) {
            return "";
        }

        if($salt == null || sizeof($salt) == 0) {
            return "";
        }

        try {
            $decrypted = Crypto::decryptWithPassword($val, $salt);
        }
        catch(Ex\WrongKeyOrModifiedCiphertextException $ex) {
            $decrypted = "";
        }

        return $decrypted;
    }

    /**
     * ------------------------------------------------------------------------
     *  generateVerifyCode :
     * ========================================================================
     *
     *
     * @param int $start
     * @return bool|string
     *
     * ------------------------------------------------------------------------
     */
    public function generateVerifyCode($start = 0) {
        $random_hash = substr(md5(uniqid()),$start,8);
        return $random_hash;
    }


    /**
     * ------------------------------------------------------------------------
     *  all :
     * ========================================================================
     *
     *
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function all() {
        $sql = "SELECT * FROM $this->m_strTable";

        return $this->executeSQLAsArray($sql);
    }


    /**
     * ------------------------------------------------------------------------
     *  getByID :
     * ========================================================================
     *
     *
     * @param $id
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function getByID($id) {
        return $this->getByCond("id=$id");
    }


    /**
     * ------------------------------------------------------------------------
     *  getByCond :
     * ========================================================================
     *
     *
     * @param $sqlCond
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function getByCond($sqlCond) {
        $sql = "SELECT * FROM $this->m_strTable WHERE $sqlCond";

        return $this->executeSQLAsArray($sql);
    }


    /**
     * ------------------------------------------------------------------------
     *  insert :
     * ========================================================================
     *
     *
     * @param $sql
     * @param $values
     * @return int
     *
     * ------------------------------------------------------------------------
     */
    public function insert($sql, $values) {
        if($this->execSQLWithValues($sql, $values)) {
            return $this->db->insert_id();
        }

        return -1;
    }


    /**
     * ------------------------------------------------------------------------
     *  update :
     * ========================================================================
     *
     *
     * @param $sql
     * @param $values
     * @return bool
     *
     * ------------------------------------------------------------------------
     */
    public function update($sql, $values) {
        return $this->execSQLWithValues($sql, $values);
    }


    /**
     * ------------------------------------------------------------------------
     *  deleteByID :
     * ========================================================================
     *
     *
     * @param $id
     * @return bool
     *
     * ------------------------------------------------------------------------
     */
    public function deleteByID($id) {
        return $this->deleteByCond("id=$id");
    }


    /**
     * ------------------------------------------------------------------------
     *  deleteByCond :
     * ========================================================================
     *
     *
     * @param $sqlCond
     * @return bool
     *
     * ------------------------------------------------------------------------
     */
    public function deleteByCond($sqlCond) {
        $sql = "DELETE FROM $this->m_strTable WHERE $sqlCond";

        return $this->executeSQL($sql);
    }


    /**
     * ------------------------------------------------------------------------
     *  executeSQL :
     * ========================================================================
     *
     *
     * @param $sql
     * @return bool
     *
     * ------------------------------------------------------------------------
     */
    public function executeSQL($sql) {
        if(!$this->db->query($sql)) return false;

        return true;
    }


    /**
     * ------------------------------------------------------------------------
     *  executeSQLAsArray :
     * ========================================================================
     *
     *
     * @param $sql
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function executeSQLAsArray($sql) {
        return $this->db->query($sql)->result_array();
    }


    /**
     * ------------------------------------------------------------------------
     *  execSQLWithValues :
     * ========================================================================
     *
     *
     * @param $sql
     * @param array $values
     * @return bool
     *
     * ------------------------------------------------------------------------
     */
    public function execSQLWithValues($sql, $values = array()) {
        if($sql == null || strlen($sql) == 0 || sizeof($values) == 0 ) return false;

        if(!$this->db->query($sql, $values)) {
            return false;
        }

        return true;
    }


    /**
     * ------------------------------------------------------------------------
     *  execSQLWithValuesAsArray :
     * ========================================================================
     *
     *
     * @param $sql
     * @param array $values
     * @return array
     *
     * ------------------------------------------------------------------------
     */
    public function execSQLWithValuesAsArray($sql, $values = array()) {
        if($sql == null || strlen($sql) == 0 || sizeof($values) == 0 ) return array();

        return $this->db->query($sql, $values)->result_array();
    }

    /**
     * ------------------------------------------------------------------------
     *  getLastInsertedID :
     * ========================================================================
     *
     *
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function getLastInsertedID() {
        return $this->db->insert_id();
    }

    /**
     * ------------------------------------------------------------------------
     *  sqlAppendSetValues :
     * ========================================================================
     *
     *
     * @param $values
     * @param bool $append
     * @param string $seperator
     * @param bool $allowEmptyValue
     * @return string
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    protected function sqlAppendSetValues($values, $append = true, $seperator=',', $allowEmptyValue = false) {
        $sql = "";

        $count = 0;
        foreach ($values as $field => $value) {
            if(!isEmptyString($value) || $allowEmptyValue) {
                $value = $this->getEscapedStr($value);
                $sql .= ($count > 0 ? "{$seperator}" : "") . "`{$field}`='{$value}'";

                $count ++;
            }
        }

        return isEmptyString($sql) ? "" : (($append ? "{$seperator}" : "") . $sql);
    }


    /**
     * ------------------------------------------------------------------------
     *  get_enum_values :
     * ========================================================================
     *
     *
     * @param $table
     * @param $field
     * @return array
     *
     * ------------------------------------------------------------------------
     */
    public function get_enum_values( $table, $field )
    {
        $type = $this->db->query( "SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" )->row( 0 )->Type;
        preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
        $enum = explode("','", $matches[1]);

        return $enum;
    }


    /**
     * ------------------------------------------------------------------------
     *  getCurrentUser :
     * ========================================================================
     *
     *
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function getCurrentUser() {
        return $this->session->userdata[SESSION_KEY_USER];
    }


    public function getEscapedStr($str) {
        return $this->db->escape_str($str);
    }

	protected function getFileSize($bytes, $decimals = 2) {
		$sz = 'BKMGTP';
		$unit = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $unit)) . @$sz[$unit];
	}

	protected function get_file_mime_type($file_path) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($finfo, $file_path);
		finfo_close($finfo);

		$fileName = pathinfo($file_path, PATHINFO_BASENAME);
		$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
		$videoExtTypes = array(
			'mp4',
			'mov',
			'avi',
			'wmv',
			'mpeg',
			'mpg'
		);

		$videoMimeTypes = array(
			'video/mpeg',       // mpg
			'video/quicktime',  // mov
			'video/x-msvideo',  // avi
			'video/x-ms-wmv',   // wmv
			'video/x-ms-asf',
			'video/mp4',        // mp4
			'application/mp4',
			'video/x-m4v'
		);

		$audioExtTypes = array(
			'wav',
			'mp3',
			'wma'
		);

		$audioMimeTypes = array(
			'audio/x-wav',
			'audio/x-ms-wma',
			'video/x-ms-asf',
			'audio/mpeg'
		);

		$imageExtTypes = array(
			'jpg',
			'jpeg',
			'gif',
			'png'
		);

		$imageMimeTypes = array(
			'image/gif',
			'image/jpeg',
			'image/png',
			'image/x-png',
			'image/x-citrix-png'
		);

		$docExtTypes = array(
			'doc',
			'docx',
			'xls',
			'xlsx',
			'ppt',
			'pptx',
			'rtf',
			'pdf',
			'txt'
		);

		$docMimeTypes = array(
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.ms-powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'application/pdf',
			'application/rtf',
			'text/plain',
			'text/rtf'
		);


		$bNeedConvert = false;

		$fileGroup = 'unknown';
		if((in_array($fileExt, $videoExtTypes) || in_array($mime_type, $videoMimeTypes)) &&
			(!in_array($fileExt, $audioExtTypes))) {
			$fileGroup = 'video';

			$bNeedConvert = !in_array($mime_type, array('video/mp4','application/mp4'));
		}
		else if(in_array($fileExt, $audioExtTypes) || in_array($mime_type, $audioMimeTypes)) {
			$fileGroup = 'audio';
			if($mime_type == 'video/x-ms-asf') { $mime_type = 'audio/x-ms-wma'; }
			$bNeedConvert = strcmp(strtolower($fileExt), 'mp3') != 0;
		}
		else if(in_array($fileExt, $imageExtTypes) || in_array($mime_type, $imageMimeTypes)) {
			$fileGroup = 'picture';
		}
		else if(in_array($fileExt, $docExtTypes) || in_array($mime_type, $docMimeTypes)) {
			$fileGroup = 'document';
		}

		return array('type' => $fileGroup, 'ext' => $fileExt, 'mime_type' => $mime_type, 'need_convert' => $bNeedConvert );
	}
}
