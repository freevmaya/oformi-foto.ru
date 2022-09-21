<?
    $charset = 'utf-8';
//    header('Content-Type: text/html; charset='.$charset);
?>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=<?=$charset?>" />
    <body>
        <table border="0px" width="100%" background="<?=$backURL?>">
            <tr>
                <td align="center">
                <table border="0px" width="740px" cellpadding="10px">
                    <tr>
                        <td colspan="2">
                            <h1><font face="Sans-serif" size="6"><?=$titleMail?></font></h1>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border="1px" bordercolor="#000" frame="border" cellpadding="10px" bgcolor="#EEE">
                                <tr>
                                <td>
                                <img src="<?=$imgURL?>">
                                </td>
                                </tr>
                            </table>
                        </td>
                        <td valign="top">
                            <p><font face="Tahoma" size="2">Создай открытку, календарик, этикетку со своим фото, сохрани на компьютер, отправь другу или размести в гостевой.<br><a href="<?=$link?>">Преврати свое фото в шедевр!</a></p></font>
                            <p><font face="Tahoma" size="2">Вы также можете посмотреть открытку по адресу: <a href="<?=$viewLink?>"><?=$viewLink?></a></a></p>
                        </td>
                    </tr>
                    <tr>
                    <td align="right" colspan="2">
                        <font face="Tahoma" size="2" color="#AAA">
<?if ($fromMail) {?>                        
                            Это письмо сформировано действием пользователя на сервисе создания открыток. Пользователь указал следующий адрес: <a href="mailto:<?=$fromMail?>"><?=$fromMail?></a> для связи с ним.
<?} else {?>
                            Это письмо сформировано действием пользователя на сервисе создания открыток, на него отвечать не надо!
<?}?>                            
                        </font>
                    </td>
                    </tr>
                </table>
                </td>
            </tr>
        </table>
    </body>
</html>