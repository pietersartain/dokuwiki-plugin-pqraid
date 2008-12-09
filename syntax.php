<?php
// must be run within Dokuwiki

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_pqraid extends DokuWiki_Syntax_Plugin {

/*
    var $_auth = null;        // auth object
    var $_start = 0;          // index of first user to be displayed
    var $_pagesize = 20;      // number of users to list on one page
    var $_filter = array();   // user selection filter(s)
*/
    /**
     * Constructor
     */
/*    function syntax_plugin_pqraid(){
        global $auth;

        $this->setupLocale();

        if (!isset($auth)) {
          $this->disabled = $this->lang['noauth'];
        } else if (!$auth->canDo('getUsers')) {
          $this->disabled = $this->lang['nosupport'];
        } else {

          // we're good to go
          $this->_auth = & $auth;

        }
    }
*/

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Pieter Sartain',
            'email'  => 'pesartain@googlemail.com',
            'date'   => '2008-02-01',
            'name'   => 'PQ Raid Plugin',
            'desc'   => '',
            'url'    => 'http://www.pesartain.com',
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    function getSort(){
        return 32;
    }
   
    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{pqraid>.*?\}\}',$mode,'plugin_pqraid');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {
        $match = substr($match, 9, -2);
		return $match;		
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
	
		global $INFO;
	
		include_once "connect.php";
//		include_once "borderFunc.php";

		if($mode == 'xhtml'){
			switch ($data) {
				case "calendar":
					if (isset($_GET['week'])) {
						// Set the raid week to the desired, or ...
						$week = $_GET['week'];
					} else {
						// Set it to the current week, relative to the raiding epoch
						include_once "timeFunc.php";
						$week = dateToWeek(getToday());
					}
					include_once "calendar.php";
					$renderer->doc .= getCalendar($week,$INFO['perm'],getDb());
					break;
				case "csceditor":
					include_once "csc.php";
					$renderer->doc .= getCSCEditor(getDb());
					break;
				case "achievementeditor":
/*					if (isset($_GET['addnew'])) {
						$addnew = $_GET['addnew'];
					} else {
						$addnew = 0;
					} */
					include_once "achievements.php";
					$renderer->doc .= getAchievements(getDb());
					break;
/*				case "clist":
					$user_list = $this->_auth->retrieveUsers($this->_start, $this->_pagesize, $this->_filter);
					
					foreach($user_list as $user => $userinfo){
//						$renderer->doc .= print_r($user)."<br>";
						$renderer->doc .= $userinfo['mail']."<br>";
					}
*/					break;
				
			}
			return true;
		}
		return false; 
    }
}
?>
