<?
    GLOBAL $articleList;
    $articleList = array(
        array(
            'title'=>'Руководство, как используя приложение вы сможете оформить свои фотографии',
            'link'=>link::c('article', 'manual'),
            'group_id'=>1
        ),array(
            'title'=>'Примеры',
            'link'=>link::c('article', 'examples'),
            'group_id'=>1
        ),/*array(
            'title'=>'Калькулятор даты Православной пасхи',
            'link'=>link::c('article', 'Kal\'kulyator-daty-paskhi'),
            'group_id'=>1
        ),*/array(
            'title'=>'Как сделать заурядную фотку уникальной',
            'link'=>link::c('article', 'kak-sdelat-zauryadnuyu-fotku-unikalnoi'),
            'group_id'=>2
        ),array(
            'title'=>'Как оживить ваш фотоальбом',
            'link'=>link::c('article', 'kak-ozhivit-vash-fotoalbom'),
            'group_id'=>2
        ),array(
            'title'=>'Как создать GIF анимацию',
            'link'=>link::c('article', 'kak-sozdat-gif-animaciy'),
            'group_id'=>2
        ),array(
            'title'=>'Как сохранить жемчужины воспоминаний',
            'link'=>link::c('article', 'kak-sohranit-zhemchuzhiny-vospominanii'),
            'group_id'=>2
        ),array(
            'title'=>'Обработка фотографий онлайн',
            'link'=>link::c('article', 'obrabotka-fotografii-onlain'),
            'group_id'=>2
        ),array(
            'title'=>'Почему коллаж не удался?',
            'link'=>link::c('article', 'pochemu-kollazh-ne-udalsya'),
            'group_id'=>2
        ),array(
            'title'=>'Как правильно выбрать фотографию для коллажа',
            'link'=>link::c('article', 'kak-pravilno-vybrat-fotografiyu-dlya-kollazha'),
            'group_id'=>2
        ),array(
            'title'=>'Разрешение проблем',
            'link'=>link::c('article', 'board-troubleshooting'),
            'group_id'=>1
        ),array(
            'title'=>'Разрешение проблем в Yandex браузере',
            'link'=>link::c('article', 'support-yandex'),
            'group_id'=>1
        ),array(
            'title'=>'Скачать рамки в архиве',
            'link'=>link::c('article', 'download-frames-all'),
            'group_id'=>1
        ),array(
            'title'=>'О нас',
            'link'=>link::c('article', 'about'),
            'group_id'=>2
        )
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