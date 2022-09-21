<?
/*
    $start_alert = array(
        'title'=>'Внимание конкурс!',
        'image'=>'http://oformi-foto.ru/images/action01.jpg',
        'data'=>'Хотите получить профессиональный коллаж из своего фото?\nПригласите друзей и получите коллаж БЕСПЛАТНО!\nПодробности читайте в <b><a href="http://ok.ru/oformifoto/topic/63677627315584">официальной группе приложения</a></b>\n',
        'cancel_enabled'=>1,
        'event'=>'INVITEDIALOG'
    );
*/    
    
/*
Выбирает всех пользователей у кого есть сегодня приглашенные друзья

SELECT *, (SELECT COUNT(inviteUser) FROM pjok_invite WHERE user_id=ou.uid AND `date`=NOW()) AS inviteUsers  FROM `pjok_options` ou WHERE `visitDate` = NOW() ORDER BY inviteUsers
*/    
    
    $start_alert = null;
?>