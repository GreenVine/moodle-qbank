<?php
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);

    require_once '../../config.php';

    if (isloggedin()) {
    	if (isset($_REQUEST['action']) && function_exists($_REQUEST['action'])) {
            $_REQUEST['action']();
        } else {
        	http_response_code(400);
       		exit('Bad Request.');
        }
    } else {
        http_response_code(403);
        exit('You are not logged in.');
    }

    function changeFontSize() {

    	if (isset($_REQUEST['size']) && !empty($_REQUEST['goto']) && is_numeric($_REQUEST['size']) && (int) $_REQUEST['size'] <= 4 && (int) $_REQUEST['size'] >= -4) {
    		@setcookie('editor_zoom', $_REQUEST['size'], time() + 3600 * 24 * 365 * 5); // 5 years

    		$moodle_url = new moodle_url('/mod/qbankeditor/' . substr($_REQUEST['goto'], 1));
            $site_url = $moodle_url->__toString();
    		header('Location: ' . $site_url);
    		exit;
    	} else {
    		http_response_code(400);
       		exit('Bad Request.');
    	}

    }
?>