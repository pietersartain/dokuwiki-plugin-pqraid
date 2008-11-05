<?php
// must be run within Dokuwiki

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class action_plugin_pqraid extends DokuWiki_Action_Plugin {

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
        $this->Lexer->addSpecialPattern('~~pqraid~~',$mode,'plugin_pqraid');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        return array($match,$state,$pos);
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
	
		include_once "connect.php";
		include_once "timeFunc.php";

		include_once "borderFunc.php";
		include_once "calendar.php";

		if (isset($_GET['week'])) {
			// Set the raid week to the desired, or ...
			$week = $_GET['week'];
		} else {
			// Set it to the current week, relative to the raiding epoch
			$week = dateToWeek(time());
		}
	
		if($mode == 'xhtml'){
			$renderer->doc .= 
				'<script type="text/javascript">'.
				file_get_contents(DOKU_PLUGIN."pqraid/interface.js").
				'</script>'.getCalendar($week,getDb());
			return true;
		}
		return false;
	}

	/**
	* Register its handlers with the DokuWiki's event controller
	*/
	function register(&$controller) {
		$controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE',  $this, '_hookjs');
	}
 
	/**
	* Hook js script into page headers.
	*
	* @author Samuele Tognini <samuele@cli.di.unipi.it>
	*/
	function _hookjs(&$event, $param) {
		$event->data["script"][] = array ("type" => "text/javascript",
					"charset" => "utf-8",
					"_data" => "",
					"src" => DOKU_BASE."lib/plugins/pqraid/script.js"
					);
	}

}
?>
