<?
    global $_SERVER;
    $domain = 'oformi-foto.ru';
/*    define('mainURL', 'http://oformi-foto.ru/index_dev.php');
    $sid = 3;*/
    $cardLink =  'http://'.$domain.'?state=sid,'.$sid;
?>
<html>
<meta content="text/html; charset=windows-1251" http-equiv="content-type">
<head>
    <style type="text/css">
        body {
            font-family: Tahoma, Arial;
            font-size:13px;
        }
        .link {
            margin: 10px;
        }
        .text {
            padding: 20px;
            font-size:16px;
        }
        .text h4 {
            font-size:24px;
            font-weight: normal;
        }
        .hText {
            font-family: Tahoma, Arial;
            padding: 10px;
            color:#999999;
        }
    </style>
</head>
<body>
<div class="new-message notification">
    <table width="100%" style="font:Arial,Tahoma; font-size:14px" class="">
        <tbody>
        <tr>
            <td valign="top">
                <a href="<?=$cardLink?>" target="_blank"><img class="attachment" src="http://oformi-foto.ru/pj/images/75x75_cat.png" style="border:0"></a>
            </td>
            <td>
            <div style="color:#666666;font-size:18px">
                К вам из приложения <a href="http://my.mail.ru/apps/441805" style="text-decoration: none" target="_blank" class="object service">"Прикольное оформление ваших фотографий"</a> пришла открытка от <b class="actor"><?=$userName?></b><br>
            </div>
            <?if ($description) {?>
            <div class="text">
                <h4>Текст к открытке:</h4>
                <?=$description?>
            </div>
            <?}?>
            <div>
                <a href="<?=$cardLink?>" class="link" target="_blank"><img src="http://oformi-foto.ru/pj/images/vbutton.png"></a>
                <div class="hText">Если  у вас не получилось открыть открытку, скопируйте строку "<?=$cardLink?>" и поместите ее в адресную строку браузера (Internet Explorer, Mozilla FireFox, Opera и т.д.)</div>
            </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="hText">
            Это письмо сформировано автоматически, на него отвечать не нужно.
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>