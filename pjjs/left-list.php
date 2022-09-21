<?
$protocol = isset($_SERVER['HTTP_HTTPS'])?'https':'http';
include('include/left-listInitialize.php');

header('Content-Type: text/html; charset='.$charset);
$title = 'Прикольное оформление ваших фотографий';
?>
<!DOCTYPE html>
<html>
<?
$agent = $_SERVER['HTTP_USER_AGENT'];
$isAndroid = strpos($agent, 'Android');
if ($isAndroid) {
?>
<script type="text/javascript">
    document.location.href="index.php";
</script>
<?} else {
    include('include/left-listHead.php');
    include('include/left-listBody.php');
}?>
</html>