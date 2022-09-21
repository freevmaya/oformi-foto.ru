<?
    GLOBAL $articleList;
    $articleList = array(
        array(
            'title'=>'Guide, how to use the application you will be able to draw your photos',
            'link'=>link::c('article', 'manual'),
            'group_id'=>1
        ),array(
            'title'=>'Examples',
            'link'=>link::c('article', 'examples'),
            'group_id'=>1
        ),/*array(
            'title'=>'Калькулятор даты Православной пасхи',
            'link'=>link::c('article', 'Kal\'kulyator-daty-paskhi'),
            'group_id'=>1
        ),array(
            'title'=>'How to make a mediocre picture unique',
            'link'=>link::c('article', 'kak-sdelat-zauryadnuyu-fotku-unikalnoi'),
            'group_id'=>2
        ),array(
            'title'=>'How to spice up your photo album',
            'link'=>link::c('article', 'kak-ozhivit-vash-fotoalbom'),
            'group_id'=>2
        ),*/array(
            'title'=>'How to create a GIF animation',
            'link'=>link::c('article', 'kak-sozdat-gif-animaciy'),
            'group_id'=>2
        )/*,array(
            'title'=>'How to save the pearls of memories',
            'link'=>link::c('article', 'kak-sohranit-zhemchuzhiny-vospominanii'),
            'group_id'=>2
        )*/,array(
            'title'=>'Processing photos online',
            'link'=>link::c('article', 'obrabotka-fotografii-onlain'),
            'group_id'=>2
        )/*,array(
            'title'=>'Why the collage failed?',
            'link'=>link::c('article', 'pochemu-kollazh-ne-udalsya'),
            'group_id'=>2
        )*/,array(
            'title'=>'How to choose the right photo for a collage',
            'link'=>link::c('article', 'kak-pravilno-vybrat-fotografiyu-dlya-kollazha'),
            'group_id'=>2
        )/*,array(
            'title'=>'Troubleshooting',
            'link'=>link::c('article', 'board-troubleshooting'),
            'group_id'=>1
        )*/
    );
    
    
    
    $query = "SELECT `group_id`, `type`, `translit`, `title`, `text_id` FROM `gpj_texts` WHERE `inmenu`=1 AND lang='".ss::lang()."'";
    $addList = DB::asArray($query, null, true);
    if (count($addList) > 0) {
        $addMenu = array();
        
        foreach ($addList as $item) { 
            $addMenu[] = array(
                'title'=>$item['title'],
                'link'=>link::c('article', $item['translit']),
                'group_id'=>$item['group_id']//(($item['type']=='article') || ($item['type']=='file'))?1:2
            );
        }
        
        $articleList = array_merge($addMenu, $articleList);
    }
    
    function articlesFromMenu() {
        GLOBAL $articleList;
        $list = array_merge($articleList);
        $menu = array();
        $groups = DB::asArray('SELECT tg.*, n.name FROM gpj_textgroups tg LEFT JOIN of_names n ON n.name_id = tg.name_id AND n.lang=\''.ss::lang().'\' WHERE active = 1', null, true);
        foreach ($groups as $g=>$group) {
            $mgroup = array(
                'caption'=>$group['name'],
                'submenu'=>array()
            );
            foreach ($list as $ai=>$item) {
                if ($item['group_id'] == $group['group_id']) {
                    $mgroup['submenu'][$item['link']] = $item['title'];
                    unset($list[$ai]);
                }
            }
                
            if (count($mgroup['submenu']) > 0) $menu[link::c('article', 'articles-'.$group['group_id'])] = $mgroup;
        }
        
        foreach ($list as $item) 
            $menu[$item['link']] = $item['title'];
        
        return $menu;
    };
    
?>