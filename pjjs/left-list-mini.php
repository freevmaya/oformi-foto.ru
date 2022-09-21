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
if (strpos($agent, 'Android') !== false) {
?>
<script type="text/javascript">
    document.location.href="index.php";
</script>
<?} else {
    include('include/left-listHeadMini.php');
    include('include/left-listBody.php');
}?>
</html>