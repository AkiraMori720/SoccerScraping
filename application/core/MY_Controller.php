<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 8/24/2017
 * Time: 10:11 PM
 */

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Get a hex-encoded representation of the key:
        $key = bin2hex($this->encryption->create_key(16));

        // Put the same value in your config with hex2bin(),
        // so that it is still passed as binary to the library:
        $this->config->set_item('encryption_key', hex2bin($key));

        $this->load->model('M_User', 'user');

        if($this->getCurrentLang() == '') {
            $this->setLanguage(ENGLISH);
        }

		$this->lang->load('error_message', $this->getCurrentLang());
		$this->lang->load('ui_common', $this->getCurrentLang());
		$this->lang->load('ui_user', $this->getCurrentLang());

        $this->checkRememberMe();
    }

    /////////////////////////////////////////////////////////////////////////////////////
    public function clearRememberMeCookie()
    {
        if (isset($_COOKIE[SESSION_KEY_USER])) {
            setcookie(SESSION_KEY_USER, "", time()-3600, '/');
            unset ($_COOKIE[SESSION_KEY_USER]);
        }
    }

    public function setRememberMeCookie($user_info)
    {
        $cookieVal = encrypt(json_encode($user_info), getServerIpAddr());

        setcookie(SESSION_KEY_USER, $cookieVal, time() + (86400 * 7), '/'); // 86400 = 1 day
    }

    public function checkRememberMe()
    {
        if (isset($_COOKIE[SESSION_KEY_USER]) && !empty($_COOKIE[SESSION_KEY_USER]) &&
            !isset($_SESSION[SESSION_KEY_USER]))
        {
            $data = json_decode(decrypt($_COOKIE[SESSION_KEY_USER], getServerIpAddr()));

            if (isset($data->uid)) {
                $usr_id = (string)$data->uid;


                $result = $this->user->getByCond("uid=$usr_id");

                if (sizeof($result) == 1) {
                    $row = $result[0];
                    $_SESSION[SESSION_KEY_USER] = array(
                        'uid'       => $row['uid'],
                        'user_name'  => $row['user_name'],
                        'user_type'  => $row['user_type'],
                        'real_name'  => $row['real_name']
                    );
                }
            }
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  change_lang :
     * ========================================================================
     *
     *
     *
     * ------------------------------------------------------------------------
     */
    public function change_lang() {
        $newLang = $_POST['optLang'];
        $curURL  = $_POST['txtCurURL'];

        $this->setLanguage($newLang);

        redirect($curURL);
    }


    /**
     * ------------------------------------------------------------------------
     *  setLanguage :
     * ========================================================================
     *
     *
     * @param string $language
     *
     * ------------------------------------------------------------------------
     */
    public function setLanguage($language = '') {
        if(strlen($language) == 0) {
            $gi = geoip_open(ASSETS_PATH."/db/GeoIP.dat", GEOIP_STANDARD);
            $code = geoip_country_code_by_addr($gi, get_client_ip());
            geoip_close($gi);

//            if($code == 'HK' || $code == 'HKG' ||
//               $code == 'CN' || $code == 'CHN' ||
//               $code == 'TW' || $code == 'TWN' ||
//               $code == 'MO' || $code == 'MAC') {
//                $this->session->set_userdata(SESSION_KEY_LANG, CHINESE);
//            }
//            else
                if($code == 'US' || $code == 'USA' ||
                $code == 'VI' || $code == 'VIR' ||
                $code == 'GB' || $code == 'GBR' ||
                $code == 'NZ' || $code == 'NZL' ||
                $code == 'AU' || $code == 'AUS' ||
                $code == 'CA' || $code == 'CAN') {
                $this->session->set_userdata(SESSION_KEY_LANG, ENGLISH);
            }
            else {
                $this->session->set_userdata(SESSION_KEY_LANG, ENGLISH);
            }
        }
        else {
            $this->session->set_userdata(SESSION_KEY_LANG, $language);
        }
    }

    /**
     * ------------------------------------------------------------------------
     *  getCurrentLang :
     * ========================================================================
     *
     *
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function getCurrentLang() {
        $curLang = '';
        if( $this->session->has_userdata(SESSION_KEY_LANG) ) {
            $curLang = $this->session->userdata(SESSION_KEY_LANG);
        }

        return $curLang;
    }

    /**
     * ------------------------------------------------------------------------
     *  get_key :
     * ========================================================================
     *
     *
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function get_key() {
        return $this->config->item('encryption_key');
    }


    /**
     * ------------------------------------------------------------------------
     *  getViewConfigDataWith :
     * ========================================================================
     *
     *
     * @param $plug_css
     * @param $plug_js
     * @param $inc_css
     * @param $inc_js
     * @param $data
     * @return array
     *
     * ------------------------------------------------------------------------
     */
    public function getViewConfigDataWith( $plug_css, $plug_js, $inc_css, $inc_js, $data ) {
        $configData = array();

        $view_config = array();

        $view_config['plug_css'] = $plug_css;
        $view_config['plug_js'] = $plug_js;

        $view_config['other_css'] = $inc_css;
        $view_config['other_js'] = $inc_js;

        $configData['config'] = $view_config;

        $configData['data'] = $data;

        return $configData;
    }


    /**
     * ------------------------------------------------------------------------
     *  render :
     * ========================================================================
     *
     *
     * @param $mainView
     * @param $subView
     * @param $data
     *
     * ------------------------------------------------------------------------
     */
    public function render($mainView, $subView, $data) {
        $data['view'] = $subView;
        $this->load->view($mainView, $data);
    }


    /**
     * ------------------------------------------------------------------------
     *  renderWithNavBar :
     * ========================================================================
     *
     *
     * @param $view
     * @param $data
     *
     * ------------------------------------------------------------------------
     */
    public function renderWithNavBar($view, $data) {

        $data['config']['other_css'] = array_unique(
            array_merge(
                array(

                ),
                $data['config']['other_css']
            )
        );

        $data['config']['other_js'] = array_unique(
            array_merge(
                array(

                ),
                $data['config']['other_js']
            )
        );

        // If common user
        if(is_Login($this->session)) {

        }

        $this->render('common/main', $view, $data);
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
        return isset($this->session->userdata[SESSION_KEY_USER]) ? $this->session->userdata[SESSION_KEY_USER] : null;
    }




    /**
     * ------------------------------------------------------------------------
     *  sendEmail :
     * ========================================================================
     *
     *
     * @param $to
     * @param $subject
     * @param $body
     * @return mixed
     *
     * ------------------------------------------------------------------------
     */
    public function sendEmail($to, $subject, $body) {
        $from_email = WEB_MASTER_MAIL;
        $to_email = $to;

        $this->email->from($from_email, 'WebMaster');
        $this->email->to($to_email);
        $this->email->subject($subject);
        $this->email->message($body);

        //Send mail
        return $this->email->send();
    }

    public function logUserAction($email, $action) {
        $data['ip_addr']= get_client_ip();
        $data['user']   = $email;
        $data['action'] = $action;
        if(isDevVersion()) { log_to_file( $data, "log ". getDateTime("Y-m-d") .".txt" ); }
    }


    public function setSessionData($key, $val) {
        $this->clearSessionData($key);
        $_SESSION[$key] = $val;
    }

    public function getSessionData($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }

    public function clearSessionData($key) {
        $this->session->unset_userdata($key);
    }


    public function printResponse() {
        echo $this->response->toJSON();
    }
}
