<?php

/*
 * For CLI Purposes
 */
$opt = getopt('p:');
if ($opt !== false && isset($opt['p'])) {
    $_SERVER['TYPE'] = 'cli';
    $_SERVER['HTTP_HOST'] = 'api.simpleapp';
    $_SERVER['REQUEST_URI'] = '/' . $opt['p'];
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

include './protected/config/common.conf.php';
include './protected/config/routes.conf.php';
include './protected/config/db.conf.php';

#Just include this for production mode
//include $config['BASE_PATH'].'deployment/deploy.php';
include $config['BASE_PATH'] . 'Doo.php';
include $config['BASE_PATH'] . 'app/DooConfig.php';

# Uncomment for auto loading the framework classes.
//spl_autoload_register('Doo::autoload');

Doo::conf()->set($config);

// For 4.3.0 <= PHP <= 5.4.0
if (!function_exists('http_response_code')) {

    function http_response_code($newcode = NULL) {
        static $code = 200;
        if ($newcode !== NULL) {
            header('X-PHP-Response-Code: ' . $newcode, true, $newcode);
            if (!headers_sent())
                $code = $newcode;
        }
        return $code;
    }

}

try {
    # remove this if you wish to see the normal PHP error view.
    //include $config['BASE_PATH'] . 'diagnostic/debug.php';
    # database usage
    //Doo::useDbReplicate();	#for db replication master-slave usage
    //Doo::db()->setMap($dbmap);
    Doo::db()->setDb($dbconfig, $config['APP_MODE']);
//    Doo::db()->sql_tracking = true; #for debugging/profiling purpose

    Doo::app()->route = $route;

    # Uncomment for DB profiling
    //Doo::logger()->beginDbProfile('doowebsite');
    Doo::app()->run();
    //Doo::logger()->endDbProfile('doowebsite');
    //Doo::logger()->rotateFile(20);
    //Doo::logger()->writeDbProfiles();
} catch (Exception $e) {
    $response = array(
        'desc' => "$e",
        'code' => '500'
    );
    http_response_code(500);
    echo json_encode($response, true);
}
?>