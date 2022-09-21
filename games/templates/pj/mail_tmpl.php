<?
    global $_SERVER;
/*    define('mainURL', 'http://oformi-foto.ru/index_dev.php');
    $sid = 3;*/ 
?>
<table width="100%" style="font:Arial,Tahoma; font-size:14px">
    <tr>
        <td>
            <img src="http://<?=$_SERVER['SERVER_NAME']?>/pj/images/75x75_cat.png">
        </td>
        <td>
        К вам с сайта <a href="http://<?=$_SERVER['SERVER_NAME']?>" style="text-decoration: none" target="_blank">"Прикольное оформление ваших фотографий"</a> пришла открытка<br>
        <a href="http://<?=$_SERVER['SERVER_NAME'].'?state=sid,'.$sid?>" target="_blank">Посмотреть открытку</a><br><br>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="color:#888888;font-size:12px">
        Это письмо сформировано автоматически, на него отвечать не нужно.
        </td>
    </tr>
</table>