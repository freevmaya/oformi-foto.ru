<?
    global $_SERVER;
/*    define('mainURL', 'http://oformi-foto.ru/index_dev.php');
    $sid = 3;*/ 
    $cardLink =  'http://'.$_SERVER['SERVER_NAME'].'?state=sid,'.$sid;    
?>
<html>
<body>
<div class="new-message notification">
    <table width="100%" style="font:Arial,Tahoma; font-size:14px">
        <tbody>
        <tr>
            <td>
                <img class="attachment" src="http://<?=$_SERVER['SERVER_NAME']?>/pj/images/75x75_cat.png">
            </td>
            <td>
            К вам с сайта <a href="<?=$cardLink?>" style="text-decoration: none" class="object service" target="_blank">"Прикольное оформление ваших фотографий"</a> пришла открытка<br>
            <a href="<?=$cardLink?>" class="action-link" target="_blank">Посмотреть открытку</a><br><br>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="color:#888888;font-size:12px">
            Это письмо сформировано автоматически, на него отвечать не нужно.
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>