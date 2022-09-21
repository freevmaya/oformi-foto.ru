<?
$protocol = isset($_SERVER['HTTPS'])?'https':'http';
$language = isset($_GET['lang'])?$_GET['lang']:'rus';

if ($language == 'ru') $language = 'rus';
else if ($language == 'en') $language = 'eng';

$charset  = 'utf-8';

header('Content-Type: text/html; charset='.$charset);

$isDev = isset($_GET['dev']);
$include_path = 'include';
 
include('locale/'.$language.'.php'); 
include($include_path.'/lfInitialize.php');

$title       = $locale['TITLE']['LF'];
$description = $locale['DESC']['LF'];
?>
<!DOCTYPE html>
<html>
<?
$isAndroid = strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false;
include($include_path.($isDev?'/lfHead.php':'/lfHeadMini.php'));
include($include_path.'/lfBody.php');
?>
</html>
