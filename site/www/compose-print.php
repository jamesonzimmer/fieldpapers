<?php

    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).'/../lib');
    require_once 'init.php';
    require_once 'data.php';
    require_once 'lib.compose.php';
    
    enforce_master_on_off_switch($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    
    session_start();
    $dbh =& get_db_connection();
    remember_user($dbh);
    
    /**** ... ****/
    
    $user = cookied_user($dbh);
    $user_id = $user['id'];
    
    $is_json = false;

    foreach(getallheaders() as $header => $value)
    {
        if(strtolower($header) == 'content-type')
        {
            $is_json = preg_match('#\b(text|application)/json\b#i', $value);
        }
    }
    
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $dbh =& get_db_connection();
        
        $dbh->query('START TRANSACTION');
        
        if($is_json) {
            $json = json_decode(file_get_contents('php://input'), true);
            $print = compose_from_geojson($dbh, file_get_contents('php://input'));

        } else {
            $print = compose_from_postvars($dbh, $_POST, $user_id);
        }
        
        $dbh->query('COMMIT');
        
        $print_url = 'http://'.get_domain_name().get_base_dir().'/print.php?id='.urlencode($print['id']);
        header("Location: {$print_url}");
    }
    
?>