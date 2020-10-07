<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 10/17/2018
 * Time: 12:40 PM
 */
require_once "C_Super.php";


class C_Admin extends C_Super
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index() {
        redirect('admin/users');
    }

    public function page_users() {
        if(!isAdmin($this->m_curUser)) {
            redirect('dash/index');
            return;
        }

        $data = $this->getViewConfigDataWith(
            array(
				"/datatable/css/dataTables.bootstrap.min.css",
//                "/datatable/css/jquery.dataTables.min.css",
                "/bootstrap/css/responsive.bootstrap.min.css",
            ),
            array(
                "/datatable/js/jquery.dataTables.min.js",
				"/datatable/js/dataTables.bootstrap.min.js",
                "/datatable/js/dataTables.responsive.min.js",
            ),
            array(
                "/admin/v_users.css"
            ),
            array(
                "/admin/v_users.js"
            ),
            array( 'active_menu' => MENU_TOP_USER_LIST )
        );

        $this->renderWithNavBar('admin/v_users', $data);
    }

    public function x_users() {
        if(isAdmin($this->m_curUser)) {
            $data = $this->user->getUsers_DT($_POST);
            $this->response->m_Data = $data;
        }

        echo $this->response->toJSON();
    }

    public function x_upt_pwd() {
        if(isAdmin($this->m_curUser)) {
            $uid = getValueInArray($_POST, 'uid');
            $pwd = getValueInArray($_POST, 'password');

            $data = $this->user->updatePassword($uid, $pwd);
            $this->response->m_Data = $data;
        }

        echo $this->response->toJSON();
    }

    public function x_activate() {
        if(isAdmin($this->m_curUser)) {
            $userID = getValueInArray($_POST, 'user_id');
            $this->user->updateStatus($userID, USER_STATUS_ACTIVE);
        }

        echo $this->response->toJSON();
    }

    public function x_deactivate() {
        if(isAdmin($this->m_curUser)) {
            $userID = getValueInArray($_POST, 'user_id');
            $this->user->updateStatus($userID, USER_STATUS_INACTIVE);
        }

        echo $this->response->toJSON();
	}
}
