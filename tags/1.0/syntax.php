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
		
		if (substr_count($match,'&') > 0) {
	        list($module,$character) = explode('&',$match);
			return array($module,$character);
		} else {
			return array($match,null);
		}
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
	
		list($module,$character) = $data;

		global $INFO;	
		global $USERINFO;
		
		if(isset($USERINFO)) {
			$logged_in=1;
		} else {
			$logged_in=0;
		}

/*
		echo $logged_in;		
		print_r($USERINFO);
*/

// Determine if the logged in user is in the "user" group
/*
		foreach($INFO['userinfo']['grps'] as $grp) {
			if ($grp == 'user') {
				$logged_in=1;
			}
		}
*/

		include_once "connect.php";
//		include_once "borderFunc.php";

		if($mode == 'xhtml'){
			switch ($module) {

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
			
					if ($logged_in) {
						include_once "csc.php";
						$renderer->doc .= getCSCEditor(getDb());
					} else {
						$renderer->doc .= 'You must be logged in to use the CSC editor.';
					}				
					break;

				case "achievementeditor":
/*					if (isset($_GET['addnew'])) {
						$addnew = $_GET['addnew'];
					} else {
						$addnew = 0;
					} */	

					if ($logged_in && $INFO['isadmin']) {
						include_once "achievements.php";
						$renderer->doc .= getAchievements(getDb());
					} else {
						$renderer->doc .= 'You must be logged in and an Administrator to use the achievement editor.';
					}	
					break;
					
				case "achievements":
					include_once "achievements.php";
					$renderer->doc .= showAchievements(getDb(),$character);
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
