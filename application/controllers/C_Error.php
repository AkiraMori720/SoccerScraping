<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 11/20/2017
 * Time: 8:52 PM
 */
require_once "C_Super.php";

class C_Error extends C_Super
{
    public function __construct()
    {
        parent::__construct();
    }

    function page_404() {
        // Configure Data
        $data = $this->getViewConfigDataWith(
            array(

            ),
            array(

            ),
            array(
                "/errors/page_404.css"
            ),
            array(
                "/errors/page_404.js"
            ),
            array()
        );

        // Render Page
        if(is_Login($this->session)) {
            $this->renderWithNavBar('errors/page_404', $data);
        }
        else {
            $this->render('common/common','errors/page_404', $data);
        }
    }

    function page_error() {
        // Configure Data
        $data = $this->getViewConfigDataWith(
            array(

            ),
            array(

            ),
            array(
                "/errors/page_error.css"
            ),
            array(
                "/errors/page_error.js"
            ),
            array(
                'act_url'  => isset($_POST['txtActionURL']) ? $_POST['txtActionURL'] : '',
                'err_code' => isset($_POST['txtErrorCode']) ? $_POST['txtErrorCode'] : '',
                'err_msg'  => isset($_POST['txtErrorMsg']) ? $_POST['txtErrorMsg'] : '',
            )
        );

        // Render Page
        if(is_Login($this->session)) {
            $this->renderWithNavBar('errors/page_error', $data);
        }
        else {
            $this->render('common/common','errors/page_error', $data);
        }
    }


    public function ajax_add_error() {
        $this->load->model('Response', 'response');

        if(!isset($_POST['actionUrl']) || isEmptyString($_POST['actionUrl'])) {
            $this->response->m_Code = RES_C_UNKNOWN;
            echo $this->response->toJSON();
            return;
        }

        $this->load->model('M_TrackError', 'trackError');
        $response = $this->trackError->addNew($_POST);

        echo $response->toJSON();
    }
}