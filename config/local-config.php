<?
$host="127.0.0.1";
$user="root";
$password="";

define('DS', '/');
define('_dbname_default', '_request');
define('MAINPATH', $_SERVER['DOCUMENT_ROOT'].'/');
define('HOMEPATH', 'F:/OpenServer/domains');
define('LOGPATH', HOMEPATH.'/oformi-foto.ru/logs/');

define('_sql_i18n', true);
define('_file_log', LOGPATH.'errors.log');
define('_sql_log', false);
define('INCLUDE_PATH', MAINPATH.'games'.DS.'include');

$sheme = isset($_SERVER['HTTPS'])?'https://':'http://';
$ScriptURL = $sheme.getenv('SERVER_NAME').getenv('SCRIPT_NAME');
$mainURL = str_replace(basename($ScriptURL), '', $ScriptURL);
define('MAINCHARSET','UTF-8');
define('MAINURL', $sheme.$_SERVER['HTTP_HOST']);
define('MODEL_PATH', MAINPATH.'games'.DS.'models'.DS);
define('DATA_PATH', MAINPATH.'games'.DS.'data'.DS);
define('DATA_URL', MAINURL.DS.'games'.DS.'data'.DS);

define('CARDS_PATH', DATA_PATH.'images/cards'.DS);


$cadUrls = array($sheme.'pjof.ru/', $sheme.'clothof.ru/');
define('CARDS_URL', $cadUrls[rand(0, 1)]);

define('mainURL', $mainURL);
?>