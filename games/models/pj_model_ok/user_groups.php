<?
    define('DAYINTERVAL',3);
    define('DAYINTERVALCREATE', 30);
    define('MTPREF', 'z0_');
    
    function userGroup($prefix) {
        return 
            array(
                array(
                    'name'=>'Горячие пользователи (зашедшие по последней, '.DAYINTERVAL.'-х дневной нотификации)',
                    'table'=>$prefix.'_refplace',
                    'move_table'=>MTPREF."{$prefix}_users_hot",
                    'where'=>'refplace=2 AND date>DATE_ADD(NOW(), INTERVAL -'.DAYINTERVAL.' DAY)',
                    'color'=>'#FF0000'
                ),array(
                    'name'=>'На заходящие '.DAYINTERVAL.' дня',
                    'table'=>$prefix.'_options',
                    'move_table'=>MTPREF."{$prefix}_users_hot",
                    'where'=>'refplace=2 AND date<=DATE_ADD(NOW(), INTERVAL -'.DAYINTERVAL.' DAY)',
                    'color'=>'#FF0000'
                ),  
                array(
                    'name'=>'Новые пользователи, '.DAYINTERVAL.'-х дневние',
                    'table'=>$prefix.'_options',
                    'where'=>'`createDate`>=DATE_ADD(NOW(), INTERVAL -'.DAYINTERVAL.' DAY)',
                    'color'=>'#666600'
                ), 
                array(
                    'name'=>'Пользователи зашедшие за последние '.DAYINTERVAL.' дня',
                    'table'=>$prefix.'_options',
                    'where'=>'`visitDate`>=DATE_ADD(NOW(), INTERVAL -'.DAYINTERVAL.' DAY)',
                    'color'=>'#00FF00'
                ), 
                array(
                    'name'=>'Активные пользователи (создали от '.DAYINTERVALCREATE.', зашли от '.DAYINTERVAL.' дня, зашли не один раз)',
                    'table'=>$prefix.'_options',
                    'move_table'=>MTPREF.$prefix.'_users_active',
                    'where'=>'`createDate`>=DATE_ADD(NOW(), INTERVAL -'.DAYINTERVALCREATE.' DAY) AND `visitDate`>=DATE_ADD(NOW(), INTERVAL -'.DAYINTERVAL.' DAY) AND `createDate` < `visitDate`',
                    'color'=>'#00FF88'
                ), 
                array(
                    'name'=>'Медленные пользователи (создали от 120 до 14, зашли от 30 до 7 дней, зашли не один раз)',
                    'table'=>$prefix.'_options',
                    'move_table'=>MTPREF.$prefix.'_users_large',
                    'where'=>'(`createDate` >= DATE_ADD( NOW( ) , INTERVAL -120 DAY) AND `createDate` < DATE_ADD( NOW( ) , INTERVAL -14 DAY)) AND (`visitDate` > DATE_ADD( NOW( ) , INTERVAL -30 DAY) AND `visitDate` < DATE_ADD( NOW( ) , INTERVAL -7 DAY)) AND `createDate` < `visitDate`',
                    'color'=>'#66FF00'
                ), 
                array(
                    'name'=>'Давние пользователи (создали до 60, зашли от 60 до 30 дней, зашли не один раз)',
                    'table'=>$prefix.'_options',
                    'move_table'=>MTPREF.$prefix.'_users_long',
                    'where'=>'(`createDate` <= DATE_ADD( NOW( ) , INTERVAL -60 DAY)) AND (`visitDate` > DATE_ADD( NOW( ) , INTERVAL -60 DAY) AND `visitDate` < DATE_ADD( NOW( ) , INTERVAL -30 DAY)) AND `createDate` < `visitDate`',
                    'color'=>'#88EE00'
                ), 
                array(
                    'name'=>'Пользователи отправляющие открытки (последние 7 дней)',
                    'table'=>"(SELECT uid FROM {$prefix}_send WHERE `date`>=NOW() - INTERVAL 7 DAY GROUP BY uid) as sc",
                    'move_table'=>MTPREF.$prefix.'_users_send',
                    'where'=>'',
                    'color'=>'#0033FF'
                ),
                array(
                    'name'=>'Пользователи сохраняющие открытки (последние 7 дней)',
                    'table'=>"(SELECT uid FROM `{$prefix}_save` WHERE `date`>=NOW() - INTERVAL 7 DAY GROUP BY uid) as su",
                    'move_table'=>MTPREF.$prefix.'_users_save',
                    'where'=>'',
                    'color'=>'#0055EE'
                ),
                array(
                    'name'=>'Платящие пользователи',
                    'table'=>"(SELECT user_id AS uid FROM `{$prefix}_transaction` GROUP BY `user_id`) as t",
                    'move_table'=>MTPREF.$prefix.'_users_paid',
                    'where'=>'',
                    'color'=>'#EE00EE'
                ),
                array(
                    'name'=>'Пользователи у которых есть входящие, непросмотренные открытки',
                    'table'=>"(SELECT inCount-receiveCount AS inCard, uid FROM `pjok_options` WHERE `visitDate`>=DATE_ADD(NOW(), INTERVAL-12 MONTH) AND `visitDate`<=DATE_ADD(NOW(), INTERVAL-6 MONTH)) AS ic",
                    'move_table'=>MTPREF.$prefix.'_users_inbox',
                    'where'=>'ic.inCard>0',
                    'color'=>'#EE00EE'
                )/*, 
                array(
                    'name'=>'Те кто просматривают категорию (более четырех раз)',
                    'table'=>"(SELECT * FROM (SELECT uid, COUNT(uid) AS `count` FROM {$prefix}_stat WHERE ctype=5 AND value=%s GROUP BY `uid`) AS st WHERE st.`count` > 4) AS sc",
                    'move_table'=>MTPREF.$prefix.'_stat',
                    'where'=>'',
                    'color'=>'#0088FF',
                    'values'=>array(
                        'Категория'=>array('2014', '2', '3', '1')
                    )
                )*/,
                array(
                    'name'=>'Все пользователи',
                    'table'=>$prefix.'_options',
                    'where'=>'',
                    'color'=>'#558844'
                )
            );
    }  
?>