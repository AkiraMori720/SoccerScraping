<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 5/28/2020
 * Time: 4:49 PM
 */

require_once "C_Super.php";
class C_Match extends C_Super
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_BaseCountry', 'baseCountry');
        $this->load->model('M_BaseLeagues', 'baseLeagues');

        $this->load->model('M_OddsPortal', 'oddsportal');
        $this->load->model('M_SoccerVista', 'soccervista');
        $this->load->model('M_MatchFinal', 'matchFinal');
        $this->load->model('M_Similarity', 'similarityObj');

        $this->load->model('M_MatchFinalSummary', 'matchSummary');
    }

    public function index() {
        $data = $this->getViewConfigDataWith(
            array(
                "/select2/css/select2.min.css",
                "/intl-tel-input/css/intlTelInput.css",
                "/bootstrap-multiselect/css/bootstrap-multiselect.css",
                "/datepicker/datepicker3.css",
                "/datatable/css/dataTables.bootstrap.min.css",
                "/bootstrap/css/responsive.bootstrap.min.css",
            ),
            array(
                "/bootstrap/js/validator.js",
                "/datatable/js/jquery.dataTables.min.js",
                "/datatable/js/dataTables.bootstrap.min.js",
                "/datatable/js/dataTables.responsive.min.js",
                "/select2/js/select2.min.js",
                "/intl-tel-input/js/intlTelInput-jquery.min.js",
                "/intl-tel-input/js/utils.js",
                "/bootstrap-multiselect/js/bootstrap-multiselect.js",
                "/datepicker/bootstrap-datepicker.js",
            ),
            array(
                "/match/v_index.css"
            ),
            array(
                "/match/v_index.js"
            ),
            array(
                'active_menu'       => MENU_TOP_MATCHES,
                'countries'         => $this->baseCountry->all(),
                'favor_countries'   => $this->baseLeagues->getCountries()
            )
        );

        $this->renderWithNavBar('match/v_index', $data);
    }

    /**
     * ------------------------------------------------------------------------
     *  x_list_oddsportal :
     * ========================================================================
     *
     *
     * Updated by C.R. 6/2/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_list_oddsportal() {
        $data = $this->oddsportal->getMatches_DT($_POST);
        $this->response->m_Data = $data;

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  x_list_soccervista :
     * ========================================================================
     *
     *
     * Updated by C.R. 6/2/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_list_soccervista() {
        $data = $this->soccervista->getMatches_DT($_POST);
        $this->response->m_Data = $data;

        $this->printResponse();
    }


    /**
     * ------------------------------------------------------------------------
     *  x_list_qualified :
     * ========================================================================
     *
     *
     * Updated by C.R. 5/29/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_list_qualified() {
        $data = $this->matchFinal->getMatches_DT($_POST);
        $this->response->m_Data = $data;

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  x_export_qualified :
     * ========================================================================
     *
     *
     * Updated by C.R. 6/2/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_export_qualified() {
        $dateType = getValueInArray($_POST, 'dateType', 'daily');

        $date = '';
        if($dateType == 'daily') {
            $date = getValueInArray($_POST, 'date');
        }
        else if($dateType == 'weekly') {
            $value = getValueInArray($_POST, 'week');
            $date = sprintf("%s week%d", date('Y'), $value);
        }
        else if($dateType == 'monthly') {
            $value = getValueInArray($_POST, 'month');
            $date = sprintf("%s-%02d", date('Y'), $value);
        }

        $matches = $this->matchFinal->getMatchesForExcel($_POST);

        // Create excel file
        $docTitle = "Qualified-Matches";

        $exportedFile = $this->exportDataToExcel(
            ROOT_PATH . "/" . TEMP_PATH,
            $docTitle,
            array(
                "index", "competition", "match_time", "match", "result",
                "odds_1", "odds_x", "odds_2", "bookmark",
                "soccervista_1x2", "soccervista_goal", "soccervista_cs",
                "windrawwin_1x1", "windrawwin_cs",
                "predictz_result", "predictz_score", "",
                "soccerway_link"
            ),
            $matches,
            ROOT_PATH . "/" . XLS_TPL_PATH . "/matches.xlsx",
            4
        );

        ///////////////////////////////////////
        // Download excel file
        ///////////////////////////////////////

        // Create file name to download
        $downName = "{$docTitle}(" . $date . ")";

        if(!isEmptyString($exportedFile)) {
            $this->response->m_Data = array(
                'file' => $exportedFile,
                'name' => $downName,
                'doDelete' => 1
            );
        }
        else {
            $this->response->setResponse(RES_C_NO_XLS, lang(RES_C_NO_XLS));
        }

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  x_list_analyzed :
     * ========================================================================
     *
     *
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_list_analyzed() {
        $data = $this->matchSummary->getMatches_DT($_POST);
        $this->response->m_Data = $data;

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  x_export_analyzed :
     * ========================================================================
     *
     *
     * Updated by C.R. 7/1/2020
     *
     * ------------------------------------------------------------------------
     */
    public function x_export_analyzed() {
        $dateType = getValueInArray($_POST, 'dateType', 'daily');

        $date = '';
        if($dateType == 'daily') {
            $date = getValueInArray($_POST, 'date');
        }
        else if($dateType == 'weekly') {
            $value = getValueInArray($_POST, 'week');
            $date = sprintf("%s week%d", date('Y'), $value);
        }
        else if($dateType == 'monthly') {
            $value = getValueInArray($_POST, 'month');
            $date = sprintf("%s-%02d", date('Y'), $value);
        }

        $matches = $this->matchSummary->getMatchesForExcel($_POST);

        // Create excel file
        $docTitle = "Analyzed-Matches";

        $exportedFile = $this->exportDataToExcel(
            ROOT_PATH . "/" . TEMP_PATH,
            $docTitle,
            array(
                "index_no", "roypick_grp", "roypick_no",
                "match_at", "competition", "match_time", "match_team", "match_result",
                "match_odds_1", "match_odds_x", "match_odds_2", "match_bookmark",
                "match_sv_1x2", "match_sv_ou", "match_sv_cs",
                "match_wdw_1x2", "match_wdw_cs",
                "match_rp2_1x2", "match_rp2_cs", "match_p_idx", "match_sw_link",
                "picks_avg", "picks_fz", "picks_1x2",
                "sv_1x2", "sv_cs1",
                "wdw_1x2", "wdw_cs2",
                "prdz_1x2", "prdz_cs3",
                "roy_1x2", "roy_cs4", "roy_percent", "roy_sg", "roy_cs5", "roy_1", "roy_x", "roy_2",
                "index_no", "competition", "match_team",
                "result_1", "result_2",
                "c_spic1", "c_spic1_p", "c_spic2", "c_spic2_p", "c_spic3", "c_spic3_p", "c_spic4", "c_spic4_p",
                "rfz_o15", "rfz_o25", "rfz_cs4", "rfz_cs5", "rfz_scrd", "rfz_concd", "rfz_bts", "rfz_sg2",
                "rfz_sg3", "rfz_cs1", "rfz_cs2", "rfz_cs3", "rfz_25", "rfz_sg",
                "e_picks_avg", "e_picks_1x2",
                "first_r", "first_p",
                "second_r", "second_p",
                "third_r", "third_p",
                "fourth_r", "fourth_p",
                "p_odds_1", "p_odds_x", "p_odds_2",
                "p_roy1_1", "p_roy1_x", "p_roy1_2",
                "p_roy2_1", "p_roy2_x", "p_roy2_2",
                "p_roy3_1", "p_roy3_x", "p_roy3_2",
                "p_roy4_1", "p_roy4_x", "p_roy4_2"
            ),
            $matches,
            ROOT_PATH . "/" . XLS_TPL_PATH . "/analyzed.xlsx",
            4
        );

        ///////////////////////////////////////
        // Download excel file
        ///////////////////////////////////////

        // Create file name to download
        $downName = "{$docTitle}(" . $date . ")";

        if(!isEmptyString($exportedFile)) {
            $this->response->m_Data = array(
                'file' => $exportedFile,
                'name' => $downName,
                'doDelete' => 1
            );
        }
        else {
            $this->response->setResponse(RES_C_NO_XLS, lang(RES_C_NO_XLS));
        }

        $this->printResponse();
    }
}