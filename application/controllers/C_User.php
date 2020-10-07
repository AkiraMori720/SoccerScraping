<?php
header('Access-Control-Allow-Origin: *');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 5/16/2017
 * Time: 10:30 AM
 */

require_once "C_Super.php";


class C_User extends C_Super
{
    public function __construct()
    {
        parent::__construct();

        $this->lang->load('ui_user', $this->getCurrentLang());

        $this->load->model('Response', 'response');
    }


    /**
     * ------------------------------------------------------------------------
     *  index :
     * ========================================================================
     *
     *
     *
     * ------------------------------------------------------------------------
     */
    public function index() {
        if(is_Login($this->session)) {
            redirect('dash/index');
            return;
        }

        $data = $this->getViewConfigDataWith(
            array(

            ),
            array(
                "/bootstrap/js/validator.js"
            ),
            array(
                "/user/v_login.css"
            ),
            array(
                "/user/v_login.js"
            ),
            array()
        );

        $this->render('common/common','user/v_login', $data);
    }

    /**
     * ------------------------------------------------------------------------
     *  ajax_login :
     * ========================================================================
     *
     *
     *
     * ------------------------------------------------------------------------
     */
    public function ajax_login() {
        if(is_Login($this->session)) {
            echo $this->response->toJSON();
            return;
        }

        if( !isset($_POST['txtUserName']) || isEmptyString($_POST['txtUserName']) ||
            !isset($_POST['txtPassword']) || isEmptyString($_POST['txtPassword'])
        ) {
            $this->response->m_Code = RES_C_LOGIN_REQ_FIELDS;
            echo $this->response->toJSON();
            return;
        }

        $userName = $_POST['txtUserName'];
        $password= md5($_POST['txtPassword']);

        // Check User Email
        $regUser = $this->user->getByCond("user_name='$userName'");
        if(sizeof($regUser) == 0) {
            $this->response->m_Code = RES_C_LOGIN_USER_INVALID;
        }
        else if ($password != $regUser[0]['user_pwd']) {
            $this->response->m_Code = RES_C_LOGIN_USER_INVALID;
        }
        else if($regUser[0]['status'] == USER_STATUS_PENDING) {
            $this->response->m_Code = RES_C_LOGIN_USER_PENDING;
        }
        else if($regUser[0]['status'] == USER_STATUS_INACTIVE) {
            $this->response->m_Code = RES_C_LOGIN_USER_INACTIVE;
        }
        else {
            $uid = $regUser[0]['uid'];
            $userType = $regUser[0]['user_type'];
            $realName = $regUser[0]['real_name'];

            $data = array(
                'uid'       => $uid,
                'user_name' => $userName,
                'user_type' => $userType,
                'real_name' => $realName
            );

            if(isset($_POST['txtRemember'])) {
                $this->setRememberMeCookie($data);
            }

            // Write user info in session.
            $this->session->set_userdata( SESSION_KEY_USER, $data );

            // Log
            $this->logUserAction($userName, 'login');

            $this->response->setResponse( RES_C_SUCCESS, "Welcome to login." );

            $redirect_url = 'home/index';
            if(isAdmin($data)) {
                $redirect_url = 'admin/index';
            }
            $this->response->m_Data = array('redirect_url' => $redirect_url);
        }

        echo $this->response->toJSON();
    }


    /**
     * ------------------------------------------------------------------------
     *  logout :
     * ========================================================================
     *
     *
     *
     * ------------------------------------------------------------------------
     */
    public function logout() {
        $this->load->model('M_WL_User', 'user');

        // Log
        $this->logUserAction($this->getCurrentUser() != null ? $this->getCurrentUser()['user_name'] : '', 'logout');

        $this->clearSessionData(SESSION_KEY_USER);
        $this->clearRememberMeCookie();

        redirect("user/login");
    }

    /**
     * ------------------------------------------------------------------------
     *  _logout :
     * ========================================================================
     *
     *
     *
     * ------------------------------------------------------------------------
     */
    public function ajax_logout() {
        $this->load->model('M_WL_User', 'user');

        // Log
        $this->logUserAction($this->getCurrentUser() != null ? $this->getCurrentUser()['email'] : '', 'logout');

        $this->clearSessionData(SESSION_KEY_USER);
        $this->clearRememberMeCookie();

        $this->response->setResponse( 0, "" );

        echo $this->response->toJSON();
    }

    public function page_register() {
        if(is_Login($this->session)) {
            redirect('dash/index');
            return;
        }

        $data = $this->getViewConfigDataWith(
            array(

            ),
            array(
                "/bootstrap/js/validator.js"
            ),
            array(
                "/user/v_register.css"
            ),
            array(
                "/user/v_register.js"
            ),
            array()
        );

        $this->render('common/common','user/v_register', $data);
    }

    public function x_register() {
        $userName = getValueInArray($_POST, "txtUserName");
		$password = getValueInArray($_POST,"txtPassword");
		$repeatpwd= getValueInArray($_POST,"txtRepeatPwd");
		$userEmail= getValueInArray($_POST,"txtEmail");
		$realName = getValueInArray($_POST,"txtRealName");
		$captcha  = getValueInArray($_POST,"txtCaptcha");

		$captchaOrg = $this->getSessionData(SESSION_CAPTCHA_WORD);

		if( isEmptyString($userName) ||
            isEmptyString($password) ||
            isEmptyString($userEmail) ||
            isEmptyString($realName) ||
            isEmptyString($captcha) ) {
            $this->response->m_Code = RES_C_REG_REQ_FIELDS;
        }
        else if($password != $repeatpwd) {
            $this->response->m_Code = RES_C_REG_WRONG_PASSWORD;
        }
        else if(strtoupper($captcha) != $captchaOrg) {
            $this->response->m_Code = RES_C_REG_WRONG_CAPTCHA;
        }
        else {
		    if(sizeof($this->user->getByCond("user_name='{$userName}'")) > 0) {
                $this->response->m_Code = RES_C_REG_EXIST_USER;
            }
            else if(sizeof($this->user->getByCond("email='{$userEmail}'")) > 0) {
                $this->response->m_Code = RES_C_REG_EXIST_EMAIL;
            }
            else if(!$this->user->addNewUser($userName, $password, $realName, $userEmail)) {
                $this->response->m_Code = RES_C_REG_FAILED_BY_ERR;
            }
        }

        echo $this->response->toJSON();
    }

    public function x_get_captcha() {
        $this->load->helper('captcha');

        $cap_data = $this->generateCaptcha();

        $this->response->m_Data = array('captcha_img' => $cap_data['image']);

        // Echo Result
        echo $this->response->toJSON();
    }

    public function generateCaptcha($length = 4) {
        $this->clearCurCaptchaData();

        // codeigniter captcha helper (system/helpers/captcha_helper.php) uses php gd library
        // php.ini uncomment the extension=php_gd2.dll (Windows server)
        // apt-get install php5-gd (Linux server)
        // restart web server

        $cap_options = array(
            'word'          => '',
            'img_path'      => './assets/temp/captcha/',
            'img_url'       => base_url() . 'assets/temp/captcha/',
            'font_path'     => FCPATH . 'assets/fonts/captcha.ttf',
            'img_width'     => '100',
            'img_height'    => '30',
            'expiration'    => 3600,
            'word_length'   => $length,
            'font_size'     => 16,
            // White background and border, black text and red grid
            'colors'        => array(
                'background' => array(255, 255, 255),
                'border' => array(255, 255, 255),
                'text' => array(0, 0, 0),
                'grid' => array(255, 40, 40)
            )
        );

        $cap_data = create_captcha($cap_options);

        $this->setSessionData(SESSION_CAPTCHA_WORD, $cap_data['word']);
        $this->setSessionData(SESSION_CAPTCHA_FILE, $cap_data['filename']);

        return $cap_data;
    }

    public function clearCurCaptchaData() {
        // delete previous created captcha image
        if (isset($_SESSION[SESSION_CAPTCHA_FILE])) {
            $file_path = FCPATH . 'assets/temp/captcha/' . $_SESSION[SESSION_CAPTCHA_FILE];
            if (file_exists($file_path))
                unlink($file_path);
        }

        $this->clearSessionData(SESSION_CAPTCHA_WORD);
        $this->clearSessionData(SESSION_CAPTCHA_FILE);
    }

    public function page_setting() {
        $userInf = $this->user->getFullUserInfo($this->m_curUser['uid']);

        $data = $this->getViewConfigDataWith(
            array(

            ),
            array(
                "/bootstrap/js/validator.js"
            ),
            array(
                "/user/v_setting.css"
            ),
            array(
                "/user/v_setting.js"
            ),
            array( 'user_data' => $userInf, 'active_menu' => MENU_TOP_USER_SETTING )
        );

        $this->renderWithNavBar('user/v_setting', $data);
    }

    public function x_update_info() {
        $userID = $this->m_curUser['uid'];

        $curPasswd= getValueInArray($_POST, "txtCurPwd");
        $password = getValueInArray($_POST,"txtPassword");
        $repeatpwd= getValueInArray($_POST,"txtRepeatPwd");
        $userEmail= getValueInArray($_POST,"txtEmail");
        $realName = getValueInArray($_POST,"txtRealName");
        $captcha  = getValueInArray($_POST,"txtCaptcha");

        $captchaOrg = $this->getSessionData(SESSION_CAPTCHA_WORD);

        if( isEmptyString($curPasswd) ||
            isEmptyString($userEmail) ||
            isEmptyString($realName) ||
            isEmptyString($captcha) ) {
            $this->response->m_Code = RES_C_REG_REQ_FIELDS;
        }
        else if($password != $repeatpwd) {
            $this->response->m_Code = RES_C_REG_WRONG_PASSWORD;
        }
        else if(strtoupper($captcha) != $captchaOrg) {
            $this->response->m_Code = RES_C_REG_WRONG_CAPTCHA;
        }
        else {
            if(isEmptyString($password)) {
                $password = $curPasswd;
            }

            if(sizeof($this->user->getByCond("email='{$userEmail}' AND uid != {$userID}")) > 0) {
                $this->response->m_Code = RES_C_REG_EXIST_EMAIL;
            }
            else if(!$this->user->updateUser($userID, $password, $realName, $userEmail)) {
                $this->response->m_Code = RES_C_REG_FAILED_BY_ERR;
            }
        }

        echo $this->response->toJSON();
    }

    public function x_deactivate() {
        $uid = $this->m_curUser['uid'];

        if(!isAdmin($uid)) {
            $this->user->updateStatus($uid, 0);
        }
        else {
            $this->response->setResponse( LANG_CANT_CLOSE, "Can't close this account!" );
        }

        echo $this->response->toJSON();
    }

    public function x_check_login() {
        $result = false;
        if(is_Login($this->session)) {
            $result = true;
        }

        $this->response->setResponse( 0, "" );
        $this->response->m_Data = array('login' => $result);

        $this->printResponse();
    }
}