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
        'caption'=>'Domov', 
        'align'=>'left',
        'title'=>'Informacije o spletnem mestu, domača stran'
    ),
    link::c('app', 'view').'" class="app-link'=>array(
        'caption'=>'Ustvariti...', 
        'align'=>'left',
        'title'=>'aplikacijo za okrasitev fotografij',
        'submenu'=>array(
            link::c('app', 'view').'" class="app-link'=>'Razglednica',
//            link::c('app', 'gifavt').'" class="app-link'=>'GIF аниматор',
            link::c('app', 'clothing').'" class="app-link'=>'Cool reinkarnacija',
            link::c('app', 'tree')=>'Drevo življenja',
            link::c('app', 'collages').'" class="app-link'=>'Kolaž (beta)',
            link::c('app', 'pjjs')=>'Fotografija v okvirju',
//            link::c('app', 'pixlr').'" class="app-link'=>'Фото-редактор',
//            link::c('app', 'anim').'" class="app-link'=>'Видео-клип из ваших фото',
            link::c('holidays')=>'Razglednica za praznik',
            link::c('app', 'coloring')=>'Črno-belo v barvo'/*,
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
        'caption'=>'Foto okvirji', 
        'align'=>'left cat',
        'title'=>'Katalog okvirjev',
        'submenu'=>catalogFromMenu()
    ),/*
    link::c('article', 'articles')=>array(
        'caption'=>'Koristno', 
        'align'=>'left',
        'title'=>'Članki, posvečeni fotografijiй',
        'submenu'=>articlesFromMenu()
    ),
    link::c('discussion', 'leaders')=>array(
        'caption'=>'Tekmovanje', 
        'align'=>'left',
        'title'=>'Tekmovanje v kolažu dneva',
        'submenu'=>array(
            link::c('discussion', 'leaders')=>array(
            'caption'=>'Sošolci',
            'submenu'=>array(
                link::c('discussion', 'leaders')=>'Voditelji',
                link::c('discussion', 'winners')=>'Zmagovalci'
            )),
            link::c('discussion', 'bests')=>'Najboljši'        
        )
    ),*/
    link::c('user', 'login')=>array(
        'caption'=>'Prijavite se kot...', 
        'align'=>'right',
        'title'=>'Stran za prijavo, identifikacija uporabnika prek družbenih omrežij in OpenID'
    ),
    "setLang('{$lang}')"=>array(
        'caption'=>'<img src="'.$flashURLPath.ss::lang().'.png">', 
        'align'=>'right lang',
        'title'=>'language',
        'submenu'=>$langMenu
    )
);
?>