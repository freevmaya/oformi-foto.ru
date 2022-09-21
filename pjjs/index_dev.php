<?
$protocol = isset($_SERVER['HTTP_HTTPS'])?'https':'http';
$language = isset($_GET['lang'])?$_GET['lang']:'rus';

if ($language == 'ru') $language = 'rus';
else if ($language == 'en') $language = 'eng';

$charset  = 'utf-8';

header('Content-Type: text/html; charset='.$charset);

include('locale/'.$language.'.php'); 
include('include/lfInitialize.php');

$title       = $locale['TITLE']['LF'];
$description = $locale['DESC']['LF'];
?>
<!DOCTYPE html>
<html>
<?
$isAndroid = strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false;
include('include/lfHead.php');
include('include/lfBody.php');
?>
</html>