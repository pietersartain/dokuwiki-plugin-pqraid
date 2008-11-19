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
		include_once "timeFunc.php";
		include_once "borderFunc.php";
		include_once "calendar.php";
		include_once "csc.php";

		if($mode == 'xhtml'){
			switch ($data) {
				case "calendar":
					if (isset($_GET['week'])) {
						// Set the raid week to the desired, or ...
						$week = $_GET['week'];
					} else {
						// Set it to the current week, relative to the raiding epoch
						$week = dateToWeek(getToday());
					}
					$renderer->doc .= getCalendar($week,$INFO['perm'],getDb());
					break;
				case "csceditor":
					$renderer->doc .= getCSCEditor(getDb());
				break;
			}
			return true;
		}
		return false; 
    }
}
?>
