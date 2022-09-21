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
        'caption'=>'Home', 
        'align'=>'left',
        'title'=>'Information about the site, main page'
    ),
    link::c('app', 'view').'" class="app-link'=>array(
        'caption'=>'Create...', 
        'align'=>'left',
        'title'=>'application for decorating your photos',
        'submenu'=>array(
            link::c('app', 'view').'" class="app-link'=>'Postcard',
            link::c('app', 'gifavt').'" class="app-link'=>'GIF animator',
            link::c('app', 'clothing').'" class="app-link'=>'Reincarnation',
            link::c('app', 'tree')=>'Tree of Life',
            link::c('app', 'collages').'" class="app-link'=>'Collage (beta)',
            link::c('app', 'pjjs')=>'Framed photo',
            link::c('app', 'pixlr').'" class="app-link'=>'Photo editor',
//            link::c('app', 'anim').'" class="app-link'=>'Видео-клип из ваших фото',
//            link::c('holidays')=>'Открытку к празднику',
            link::c('app', 'coloring')=>'Black and white in color'/*,
            link::c('app', 'tree')=>'Генеалогическое древо'*/
        )
    ),
    link::c('catalog', 0)=>array(
        'caption'=>'Photo frames', 
        'align'=>'left',
        'title'=>'Catalog of frames',
        'submenu'=>catalogFromMenu()
    ),
    link::c('article', 'articles')=>array(
        'caption'=>'Usefull', 
        'align'=>'left',
        'title'=>'Articles dedicated to the design of photos',
        'submenu'=>articlesFromMenu()
    ),
    link::c('discussion', 'leaders')=>array(
        'caption'=>'Competition', 
        'align'=>'left',
        'title'=>'Competition for the best collage of the day',
        'submenu'=>array(
            link::c('discussion', 'leaders')=>array(
            'caption'=>'Classmates',
            'submenu'=>array(
                link::c('discussion', 'leaders')=>'Leaders',
                link::c('discussion', 'winners')=>'Winners'
            )),
            link::c('discussion', 'bests')=>'The best'        
        )
    ),
    link::c('user', 'login')=>array(
        'caption'=>'Login as...', 
        'align'=>'right',
        'title'=>'Login page, user authentication via social networks and OpenID'
    ),
    "setLang('{$lang}')"=>array(
        'caption'=>'<img src="'.$flashURLPath.ss::lang().'.png">', 
        'align'=>'right lang',
        'title'=>'language',
        'submenu'=>$langMenu
    )
);
?>