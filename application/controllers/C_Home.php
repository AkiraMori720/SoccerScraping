<?php

require_once "C_Super.php";

class C_Home extends C_Super
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index() {
        if(!is_Login($this->session)) {
            redirect('user/index');
            return;
        }

        $data = $this->getViewConfigDataWith(
            array(
//				"/datatable/css/dataTables.bootstrap.min.css",
                "/datatable/css/jquery.dataTables.min.css",
                "/bootstrap/css/responsive.bootstrap.min.css",
            ),
            array(
                "/datatable/js/jquery.dataTables.min.js",
//				"/datatable/js/dataTables.bootstrap.min.js",
                "/datatable/js/dataTables.responsive.min.js",
            ),
            array(
                "/home/v_component.css"
            ),
            array(
                "/home/v_component.js"
            ),
            array( 'active_menu' => MENU_TOP_DASHBOARD )
        );

        $this->renderWithNavBar('home/v_component', $data);
    }
}