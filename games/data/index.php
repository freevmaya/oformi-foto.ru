<?
header("Content-Type: text/javascript; charset=utf-8");
header('Cache-Control: no-store, no-cache, must-revalidate'); 
header('Cache-Control: post-check=0, pre-check=0', FALSE); 
header('Pragma: no-cache');

error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');

include_once('../../config/config.php');

define('ADMINPATH', MAINPATH.'games/admin/');
define('TEMPLATES_PATH', ADMINPATH.'templates/');
define('CONTROLLERS_PATH', ADMINPATH.'controllers/');

include_once(HOMEPATH.'/of-secrects.inc');
include_once(HOMEPATH.'/domains.inc');
include_once(INCLUDE_PATH.'/_dbu.php');
include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(INCLUDE_PATH.'/fdbg.php');
include_once(dirname(__FILE__).'/include/base_model.php');

$modelName = isset($_GET['model'])?$_GET['model']:'pj_model';
include_once(dirname(__FILE__).'/include/'.$modelName.'.php');

$ref = @$_SERVER['HTTP_REFERER'];

function getValues() {
    GLOBAL $_GET, $mysqli, $host, $user, $password, $dbname;
    $format = isset($_GET['format'])?$_GET['format']:'js';
    
    $mysqli = new mysqli($host, $user, $password, $dbname);
    $model = new dataModel($_GET);
    $data = $model->result();
    if ($format == 'xml') $result = $data;
    else $result = json_encode($data, JSON_INVALID_UTF8_IGNORE);
    if ($format == 'js') $result = 'var result='.$result.';';
    
    if ($mysqli) $mysqli->close();
    return $result;
}

function checkDomain($domain) {
    GLOBAL $domains;
    foreach ($domains as $cdom) {
        if (preg_match("/$cdom/", $domain) == 1) return true;
    }
    return false;
}

if ($ref) {
    $result = explode('/', $ref);
    if (checkDomain($result[2]))
        echo getValues();
    else echo "alert('Domain {$result[2]} is not allowed');";
} else echo getValues();
?>    
