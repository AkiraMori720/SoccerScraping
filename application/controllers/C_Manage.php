<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/18/2020
 * Time: 12:49 AM
 */

require_once "C_Super.php";
class C_Manage extends C_Super
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_BaseSeasons', 'baseSeasons');
        $this->load->model('M_BaseCountry', 'baseCountry');
        $this->load->model('M_BaseLeagues', 'baseLeagues');
        $this->load->model('M_BaseClubs', 'baseClubs');

        $this->load->model('M_Similarity', 'similarityObj');
    }

    public function index() {
        redirect('manage/country');
    }

    /**
     * ------------------------------------------------------------------------
     *  page_season :
     * ========================================================================
     *
     *
     * Updated by C.R. 8/13/2020
     *
     * ------------------------------------------------------------------------
     */
    public function page_season() {
        if(!isAdmin($this->m_curUser)) {
            redirect('dash/index');
            return;
        }

        $data = $this->getViewConfigDataWith(
            array(
                "/select2/css/select2.min.css",
                "/bootstrap-multiselect/css/bootstrap-multiselect.css",
                "/datatable/css/dataTables.bootstrap.min.css",
                "/bootstrap/css/responsive.bootstrap.min.css",
            ),
            array(
                "/bootstrap/js/validator.js",
                "/datatable/js/jquery.dataTables.min.js",
                "/datatable/js/dataTables.bootstrap.min.js",
                "/datatable/js/dataTables.responsive.min.js",
                "/select2/js/select2.min.js",
                "/bootstrap-multiselect/js/bootstrap-multiselect.js",
            ),
            array(
                "/manage/v_season.css"
            ),
            array(
                "/manage/v_season.js"
            ),
            array(
                'seasons' => $this->baseSeasons->all(),
                'active_menu' => MENU_TOP_MANAGE_SEASON
            )
        );

        $this->renderWithNavBar('manage/v_season', $data);
    }

    public function x_season_add() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $season = getValueInArray($_POST, 'season');

            try {
                $this->baseSeasons->saveSeason($season);
            }
            catch(Exception $e) {
                $this->response->setResponse(-1, $e->getMessage());
            }
        }

        $this->printResponse();
    }

    public function x_season_del() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $id = getValueInArray($_POST, 'id');

            try {
                $this->baseSeasons->deleteByID($id);
            }
            catch(Exception $e) {
                $this->response->setResponse(-1, $e->getMessage());
            }
        }

        $this->printResponse();
    }

    public function x_season_select() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $season = getValueInArray($_POST, 'season');

            try {
                $this->baseSeasons->setActiveSeason($season);
            }
            catch(Exception $e) {
                $this->response->setResponse(-1, $e->getMessage());
            }
        }

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  page_country :
     * ========================================================================
     *
     *
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function page_country() {
        if(!isAdmin($this->m_curUser)) {
            redirect('dash/index');
            return;
        }

        $data = $this->getViewConfigDataWith(
            array(
                "/select2/css/select2.min.css",
                "/bootstrap-multiselect/css/bootstrap-multiselect.css",
                "/datatable/css/dataTables.bootstrap.min.css",
                "/bootstrap/css/responsive.bootstrap.min.css",
            ),
            array(
                "/bootstrap/js/validator.js",
                "/datatable/js/jquery.dataTables.min.js",
                "/datatable/js/dataTables.bootstrap.min.js",
                "/datatable/js/dataTables.responsive.min.js",
                "/select2/js/select2.min.js",
                "/bootstrap-multiselect/js/bootstrap-multiselect.js",
            ),
            array(
                "/manage/v_country.css"
            ),
            array(
                "/manage/v_country.js"
            ),
            array(
                'seasons' => $this->baseSeasons->all(),
                'active_menu' => MENU_TOP_MANAGE_COUNTRY
            )
        );

        $this->renderWithNavBar('manage/v_country', $data);
    }

    public function x_country_list() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $this->response->m_Data = $this->baseCountry->getList_DT($_POST);
        }

        $this->printResponse();
    }

    public function x_country_save() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            try {
                $this->response->m_Data = array('id' => $this->baseCountry->saveCountry($_POST));
            }
            catch(Exception $e) {
                $this->response->setResponse(-1, $e->getMessage());
            }
        }

        $this->printResponse();
    }

    public function x_country_del() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $id = getValueInArray($_POST, 'id');

            try {
                $this->baseCountry->deleteByID($id);
            }
            catch(Exception $e) {
                $this->response->setResponse(-1, $e->getMessage());
            }
        }

        $this->printResponse();
    }

    public function x_country_import_prev() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $season = getValueInArray($_POST, 'season');

            if(!isEmptyString($season)) {
                try {
                    $this->baseCountry->importBySeason($season);
                } catch (Exception $e) {
                    $this->response->setResponse(-1, $e->getMessage());
                }
            }
        }

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  page_league :
     * ========================================================================
     *
     *
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function page_league() {
        if(!isAdmin($this->m_curUser)) {
            redirect('dash/index');
            return;
        }

        $data = $this->getViewConfigDataWith(
            array(
                "/select2/css/select2.min.css",
                "/bootstrap-multiselect/css/bootstrap-multiselect.css",
                "/datatable/css/dataTables.bootstrap.min.css",
                "/bootstrap/css/responsive.bootstrap.min.css",
            ),
            array(
                "/bootstrap/js/validator.js",
                "/datatable/js/jquery.dataTables.min.js",
                "/datatable/js/dataTables.bootstrap.min.js",
                "/datatable/js/dataTables.responsive.min.js",
                "/select2/js/select2.min.js",
                "/bootstrap-multiselect/js/bootstrap-multiselect.js",
            ),
            array(
                "/manage/v_league.css"
            ),
            array(
                "/manage/v_league.js"
            ),
            array(
                'active_menu' => MENU_TOP_MANAGE_LEAGUE,

                'seasons'   => $this->baseSeasons->all(),
                'countries' => $this->baseCountry->allCountries(),

                'leagues_oddsportal' => $this->baseLeagues->getLeaguesBySite('oddsportal'),
                'leagues_soccervista'=> $this->baseLeagues->getLeaguesBySite('soccervista'),
                'leagues_soccerbase' => $this->baseLeagues->getLeaguesBySite('soccerbase'),
                'leagues_soccerway'  => $this->baseLeagues->getLeaguesBySite('soccerway'),
                'leagues_windrawwin' => $this->baseLeagues->getLeaguesBySite('windrawwin'),
                'leagues_predictz'   => $this->baseLeagues->getLeaguesBySite('predictz')
            )
        );

        $this->renderWithNavBar('manage/v_league', $data);
    }

    public function x_league_list() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $this->response->m_Data = $this->baseLeagues->getRecommendList_DT($_POST);
        }

        $this->printResponse();
    }

    public function x_league_save() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            try {
                $this->response->m_Data = array('id' => $this->baseLeagues->saveRecommendLeague($_POST));
            }
            catch(Exception $e) {
                $this->response->setResponse(-1, $e->getMessage());
            }
        }

        $this->printResponse();
    }

    public function x_league_del() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $id = getValueInArray($_POST, 'id');

            try {
                $this->baseLeagues->deleteRecommendLeague($id);
            }
            catch(Exception $e) {
                $this->response->setResponse(-1, $e->getMessage());
            }
        }

        $this->printResponse();
    }

    public function x_league_import_prev() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $season = getValueInArray($_POST, 'season');

            if(!isEmptyString($season)) {
                try {
                    $this->baseLeagues->importRecommendLeaguesBySeason($season);
                } catch (Exception $e) {
                    $this->response->setResponse(-1, $e->getMessage());
                }
            }
        }

        $this->printResponse();
    }

    /**
     * ------------------------------------------------------------------------
     *  page_club :
     * ========================================================================
     *
     *
     * Updated by C.R. 6/18/2020
     *
     * ------------------------------------------------------------------------
     */
    public function page_club() {
        if(!isAdmin($this->m_curUser)) {
            redirect('dash/index');
            return;
        }

        $data = $this->getViewConfigDataWith(
            array(
                "/select2/css/select2.min.css",
                "/bootstrap-multiselect/css/bootstrap-multiselect.css",
                "/datatable/css/dataTables.bootstrap.min.css",
                "/bootstrap/css/responsive.bootstrap.min.css",
            ),
            array(
                "/bootstrap/js/validator.js",
                "/datatable/js/jquery.dataTables.min.js",
                "/datatable/js/dataTables.bootstrap.min.js",
                "/datatable/js/dataTables.responsive.min.js",
                "/select2/js/select2.min.js",
                "/bootstrap-multiselect/js/bootstrap-multiselect.js",
            ),
            array(
                "/manage/v_club.css"
            ),
            array(
                "/manage/v_club.js"
            ),
            array(
                'active_menu' => MENU_TOP_MANAGE_CLUB,

                'leagues' => $this->baseLeagues->getRecommendLeagues(),
                'seasons' => $this->baseSeasons->all()
            )
        );

        $this->renderWithNavBar('manage/v_club', $data);
    }

    public function x_club_list() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $this->response->m_Data = $this->baseClubs->getUsedClubList_DT($_POST);
        }

        $this->printResponse();
    }

    public function x_club_save() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $season     = getValueInArray($_POST, 'season');
            $country    = getValueInArray($_POST, 'country');

            $oddsportal = getValueInArray($_POST, 'oddsportal');
            $soccervista= getValueInArray($_POST, 'soccervista');
            $soccerway  = getValueInArray($_POST, 'soccerway');
            $predictz   = getValueInArray($_POST, 'predictz');
            $windrawwin = getValueInArray($_POST, 'windrawwin');
            $soccerbase = getValueInArray($_POST, 'soccerbase');

            if(isEmptyString($season) || isEmptyString($country) || isEmptyString($oddsportal)) {
                $this->response->setResponse(-1, "Missing Values!");
            }
            else {
                $this->similarityObj->updateTeamSimilarity(
                    array(
                        $country => array(
                            $oddsportal => array(
                                'soccervista'   => $soccervista,
                                'soccerway'     => $soccerway,
                                'predictz'      => $predictz,
                                'windrawwin'    => $windrawwin,
                                'soccerbase'    => $soccerbase,
                            )
                        )
                    )
                );
            }
        }

        $this->printResponse();
    }

    public function x_club_del() {
        if(!isAdmin($this->m_curUser)) {
            $this->response->setResponse(-1, "No permission!");
        }
        else {
            $id = getValueInArray($_POST, 'id');

            try {
                $this->similarityObj->deleteTeamSimilarity($id);
            }
            catch(Exception $e) {
                $this->response->setResponse(-1, $e->getMessage());
            }
        }

        $this->printResponse();
    }
}