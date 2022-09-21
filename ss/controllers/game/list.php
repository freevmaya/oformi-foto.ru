<?
    function gameFromMenu() {
        /*
        $list  = array(
                'caption'=>'Лучший коллаж дня',
                'submenu'=>array(
                    link::c('game', 'gamelist')=>'Работы участников',
                    link::c('game', 'winners')=>'Победители'
                )
            );
            
        $groups = DB::asArray("SELECT gg.name as name, gg.group_id FROM ".DBPREF."gamegroups gg INNER JOIN ".DBPREF."game gm ON gm.group_id=gg.group_id AND (gm.state = 'active' || gm.state = 'victory') WHERE active=1");
        
        $list = array();
        foreach ($groups as $group) {
            $link = link::c('game', 'gamelist', $group['group_id'], 1);            
            
            $submenu = array(
                $link=>'Работы участников'
            );
            
            if ($contest_id = DB::one("SELECT contest_id FROM ".DBPREF."contest WHERE state='inactive' AND group_id={$group['group_id']} ORDER BY date DESC")) {            
                $submenu[link::c('game', 'winners', $group['group_id'], 1)]='Победители';                    
                $submenu[link::c('game', 'arhive', "contest-{$contest_id}-page-1")]='Архив';
            }                
                
            $list[$link] = array(
                'caption'=>$group['name'],
                'submenu'=>$submenu
            );  
        }
        */
        $list = array();
        $list[link::c('discussion', 'leaders')] = array(
            'caption'=>'Одноклассники',
            'submenu'=>array(
                link::c('discussion', 'leaders')=>'Лидеры',
                link::c('discussion', 'winners')=>'Победители'
            ));
            
        $list[link::c('discussion', 'bests')] = 'Лучшие';
            
        return $list;
    }
?>