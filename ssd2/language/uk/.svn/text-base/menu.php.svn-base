<?
GLOBAL $menuList, $root, $LANGINSTALL;

include_once(SSPATH.'articles/'.$root->lang().'/list.php');
include_once(SSPATH.'controllers/catalog/list.php');

$langMenu = array();
$flashURLPath = MAINURL.'/images/flags/16/';
 
foreach ($LANGINSTALL as $lang) {
    $langMenu["setLang('{$lang}')"] = array('caption'=>'<img src="'.$flashURLPath.$lang.'.png"> '.$lang);
}


$menuList = array(
    link::c('article', 'home')=>array(
        'caption'=>'Головна', 
        'align'=>'left',
        'title'=>'Інформація про сайт, головна сторінка'
    ),
    link::c('app', 'view').'" class="app-link'=>array(
        'caption'=>'Cтворити...', 
        'align'=>'left',
        'title'=>'додаток для оформлень ваших фотографій',
        'submenu'=>array(
            link::c('app', 'view').'" class="app-link'=>'Листівку',
            link::c('app', 'gifavt').'" class="app-link'=>'GIF аніматор',
            link::c('app', 'clothing').'" class="app-link'=>'Прикольне перевтілення',
            link::c('app', 'tree')=>'Дерево життя',
            link::c('app', 'collages').'" class="app-link'=>'Колаж (бета)',
            link::c('app', 'pjjs')=>'Фото в рамці',
            link::c('app', 'pixlr').'" class="app-link'=>'Фото-редактор',
//            link::c('app', 'anim').'" class="app-link'=>'Видео-клип из ваших фото',
            link::c('holidays')=>'Листівку до свята',
            link::c('app', 'coloring')=>'Чорно-біле в кольорове'/*,
            link::c('app', 'tree')=>'Генеалогическое древо'*/
        )
    ),
    link::c('catalog', 0)=>array(
        'caption'=>'Фоторамки', 
        'align'=>'left',
        'title'=>'Каталог рамок',
        'submenu'=>catalogFromMenu()
    ),
    link::c('article', 'articles')=>array(
        'caption'=>'Корисно', 
        'align'=>'left',
        'title'=>'Статті присвячені оформленню фотографій',
        'submenu'=>articlesFromMenu()
    ),
    link::c('discussion', 'leaders')=>array(
        'caption'=>'Конкурс', 
        'align'=>'left',
        'title'=>'Конкурс на кращий колаж дня',
        'submenu'=>array(
            link::c('discussion', 'leaders')=>array(
            'caption'=>'Однокласники',
            'submenu'=>array(
                link::c('discussion', 'leaders')=>'Лідери',
                link::c('discussion', 'winners')=>'Переможці'
            )),
            link::c('discussion', 'bests')=>'Кращі'        
        )
    ),
    link::c('user', 'login')=>array(
        'caption'=>'Ввійти як...', 
        'align'=>'right',
        'title'=>'Сторінка для входу, ідентифікації користувача через соціальні мережі та OpenID'
    ),
    "setLang('{$lang}')"=>array(
        'caption'=>'<img src="'.$flashURLPath.ss::lang().'.png">', 
        'align'=>'right lang',
        'title'=>'language',
        'submenu'=>$langMenu
    )
);
?>