<div class="mail">
<?
//    ini_set('filter.default', 'PHP_INI_ALL');
    $email = @$_POST['email'];
    $sysMessage = '';
    $rmail = '/.+@.+\..+/i';
    $isdata = ($email > '');
    $titleMail = isset($_POST['titleMail'])?addslashes(strip_tags($_POST['titleMail'])):'Оцените мое фото в рамке!';
    $name = isset($_POST['name'])?addslashes(strip_tags($_POST['name'])):'';
    $fromMail = @$_POST['fromMail'];
    
    if ($isdata && (preg_match($rmail, $email) != 1)) {
        $isdata = false;
        $sysMessage = 'Неверный формат адреса электронной почты (e-mail).<br>Должно быть например так: username@gmail.com';
    };
    if ($isdata) {
        $name = $name?$name:'сервис открыток oformi-foto.ru';
        $charset = 'utf-8';
        $subject = "Вам $name отправил открытку";
        $viewLink = $baseURL.'user.php?img='.$img;
        $imgURL = $baseURL."images/users/$img.jpg";
        $backURL = $baseURL.'images/from-mail.gif';
        
    
        ob_start();
        include(dirname(__FILE__).'/mail-tmpl.php');
//        include('mail-tmpl.php');
        $message = ob_get_contents();
        ob_end_clean();
        
        // Дополнительные заголовки
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= "To: user <$email>\r\n";
        $headers .= "From: oformi-foto.ru <oformi.foto4@gmail.com>\r\n";
        
        if (mail($email, $subject, $message, $headers)) {        
            ?><h2>Ваша открытка отправлена!</h2><?
        } else {
            ?><h2 style="color:#F00;">Ошибка при отправке!</h2><?
        }    
    } else { 
?>
<form method="POST" action="">
    <h3>Настройки письма</h3>
    <table>
        <tr>
            <td>
                e-mail адресата:
            </td>
            <td class="par" title="Электронный адрес получателя открытки, обязательно для заполнения">
                <input type="text" name="email" value="<?=$email?>" size="13"/>(обязательно)
            </td>
        </tr>
        <tr>
            <td>
                ваше имя:
            </td>
            <td class="par" title="Имя отправителя которое будет отображаться в заголовке и тексте письма, не обязательно для заполнения">
                <input type="text" name="name" value="<?=$name?>" size="24"/>
            </td>
        </tr>
        <tr>
            <td>
                заголовок письма:
            </td>
            <td class="par" title="Заголовок в письме">
                <input type="text" name="titleMail" value="<?=$titleMail?>" size="24"/>
            </td>
        </tr>
        <tr>
            <td>
                обратный адрес:
            </td>
            <td class="par" title="Ваш электронный адрес для обратной связи, не обязательно для заполнения">
                <input type="text" name="fromMail" value="<?=$fromMail?>" size="24"/>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="submit" onclick="(function(){this.destroy()).delay(200, this); return true;">
            </td>
        </tr>
    </table>
    <?if ($sysMessage) {?>
    <div class="sysMessage"><?=$sysMessage?>
    </div>
    <?}?>
</form>
<?
    }
?>
</div>