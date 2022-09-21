<?
$protocol = 'https';
$language = isset($_GET['lang'])?$_GET['lang']:'rus';

if ($language == 'ru') $language = 'rus';
else if ($language == 'en') $language = 'eng';

$charset  = 'utf-8';

header('Content-Type: text/html; charset='.$charset);

include('locale/'.$language.'.php'); 

$title       = $locale['TITLE']['LF'];
$description = $locale['DESC']['LF'];

include('include/left-listInitialize.php');
?>
<!DOCTYPE html>
<html>
<?
    if ($isDev) include('include/viewHead.php');
    else include('include/viewHeadMini.php');
    include('include/viewBody.php');
?>
</html>