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
        'caption'=>'Главная', 
        'align'=>'left',
        'title'=>'Информация о сайте, главная страница'
    ),
    link::c('app', 'view').'" class="app-link'=>array(
        'caption'=>'Создать...', 
        'align'=>'left',
        'title'=>'приложение для оформлений ваших фотографий',
        'submenu'=>array(
            link::c('app', 'view').'" class="app-link'=>'Открытку',
//            link::c('app', 'gifavt').'" class="app-link'=>'GIF аниматор',
            link::c('app', 'clothing').'" class="app-link'=>'Прикольное перевоплощение',
            link::c('app', 'tree')=>'Древо жизни',
            link::c('app', 'collages').'" class="app-link'=>'Коллаж (бета)',
            link::c('app', 'pjjs')=>'Фото в рамке',
//            link::c('app', 'pixlr').'" class="app-link'=>'Фото-редактор',
//            link::c('app', 'anim').'" class="app-link'=>'Видео-клип из ваших фото',
            link::c('holidays')=>'Открытку к празднику',
            link::c('app', 'coloring')=>'Черно-белое в цветное'/*,
            link::c('order', 'restore')=>array(
                'caption'=>'Заказать',
                'title'=>'Ваш заказ обработки фотографий',
                'submenu'=>array(
                    link::c('order', 'memorial')=>'Фотография для памятника',
                    link::c('order', 'restore')=>'Реставрация старой фотографии', 
                    link::c('order', 'sketch')=>'Настоящий рисунок с вашего фото', 
                    link::c('order', 'photocollage')=>'Взрывной фотомонтаж',
                    link::c('order', 'list')=>'Ваши заказы'
                )
            ),
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
        'caption'=>'Полезно', 
        'align'=>'left',
        'title'=>'Статьи посвященные оформлению фотографий',
        'submenu'=>articlesFromMenu()
    ),
    link::c('discussion', 'leaders')=>array(
        'caption'=>'Конкурс', 
        'align'=>'left',
        'title'=>'Конкурс на лучший коллаж дня',
        'submenu'=>array(
            link::c('discussion', 'leaders')=>array(
            'caption'=>'Одноклассники',
            'submenu'=>array(
                link::c('discussion', 'leaders')=>'Лидеры',
                link::c('discussion', 'winners')=>'Победители'
            )),
            link::c('discussion', 'bests')=>'Лучшие'        
        )
    ),
    link::c('user', 'login')=>array(
        'caption'=>'Войти как...', 
        'align'=>'right',
        'title'=>'Страница для входа, идентификации пользователя через социальные сети и OpenID'
    ),
    "setLang('{$lang}')"=>array(
        'caption'=>'<img src="'.$flashURLPath.ss::lang().'.png">', 
        'align'=>'right lang',
        'title'=>'language',
        'submenu'=>$langMenu
    )
);
?>