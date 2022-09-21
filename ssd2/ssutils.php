<?

define('MTAGS_REG', "/\[([^\]]+)[^\]]*\](.*?)\[\/\w+\]/i");

$nextWordIndex = 0;
if (!function_exists('mb_ucfirst') && extension_loaded('mbstring')) {
    function mb_ucfirst($str, $encoding='UTF-8') {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
               mb_substr($str, 1, mb_strlen($str), $encoding);
        return $str;
    }
}

function words($text, $minlen=4, $translit=false) {
    preg_match_all('/([^ \t\n\r]+)/i', $text, $items);
    $result = array();
    for ($i=0; $i<count($items[1]); $i++) {
        $word = trim($items[1][$i]);    
        if (mb_strlen($word) >=$minlen * 2) {
            if ($translit)
                $result[] = controller::translit($word);
            else $result[] = $word;        
        }     
    }        
    return $result;
} 

function limitWords($text, $limit=10, $endSeparate='...') {
    $words = words($text, 0);
    $add = ''; 
    if (count($words) > $limit) {
        array_splice($words, $limit);
        $add = $endSeparate;
    }
    return implode(' ', $words).$add;
} 

function resortWords($text, $minlen=0) {
    $items = words($text, $minlen);
    $result = '';    
    while (count($items) > 0) {
        $i = rand(0, count($items)-1);  
        $result .= ($result?' ':'').$items[$i];
        array_splice($items, $i, 1);                
    }       
    return $result;
}

function nextWord($words) {
    GLOBAL $nextWordIndex;
    $result = $words[$nextWordIndex % count($words)];
    $nextWordIndex++;
    return $result;
}

function injectWordLink($text) {
//        $a = explode(' ', $text);
    $words = preg_split('/[\s.,]+/', $text) ;
    $result = '';
    foreach ($words as $word) {
        $result .= $result?' ':'';                        
        if (strlen($word) > 3) {
            $link = MAINURL.'/'.FINDSELECTOR.'/'.controller::translit($word).'.html';
            $title = 'Поиск по слову: '.$word;
            $result .= "<a href=\"$link\" title=\"$title\" rel=\"nofollow\">$word</a>";
        } else $result .= $word;
    }
    
    return $result;
}

function textLimitIJ($text, $count, $tag, $attr='') {
    $result = '';
    if (mb_strlen($text, 'UTF-8') + 4 > $count) {
        $result = "<$tag class=\"tipz pointer\" title=\"Полный текст::$text\" {$attr}>".injectWordLink(mb_substr($text, 0, $count, 'UTF-8')).'...'."</$tag>";
    } else $result = "<$tag {$attr}>".injectWordLink($text)."</$tag>";            
    
    return $result;
}

function textLimit($text, $count, $tag="", $attr='') {
    $result = '';
    if ($text && (mb_strlen($text, 'UTF-8') + 4 > $count)) {
        $result = "<$tag class=\"tipz pointer\" title=\"Полный текст::$text\" {$attr}>".mb_substr($text, 0, $count, 'UTF-8').'...'."</$tag>";
    } else $result = $tag?("<$tag {$attr}>".$text."</$tag>"):$text;            
    
    return $result;
}

function otherArticles($words=null) {
    $whereWords = '';
    if ($words) {
        foreach ($words as $word) 
            $whereWords .= ($whereWords?' OR ':'')."`text` LIKE '%$word%'";
        
        $whereWords = 'OR ('.$whereWords.')';
    }
    $min = 2;
    $max = DB::line("SELECT MAX(`count`) AS `max` FROM `search_questions`");
    $max = ($max['max']>$min + 5)?($min + 5):$min;
    $query = "SELECT * FROM search_questions WHERE (`count`<=$max $whereWords) AND `text` NOT LIKE '%88%' ORDER BY `weight` DESC LIMIT 0,10";
    return DB::asArray($query);
}

function mysqlToDate($mysqlDate) {
    $d = explode('-', $mysqlDate);
    return $d[2].'.'.$d[1].((date('Y')==$d[0])?'':('.'.$d[0]));    
}

function isToday($mysqlDate) {
    return $mysqlDate == date('Y-m-d', strtotime('NOW'));
}

function todayDate($mysqlDate) {
    GLOBAL $locale;
    if ($mysqlDate == date('Y-m-d', strtotime('NOW'))) return $locale['TODAY'];
    else if ($mysqlDate == date('Y-m-d', strtotime('+1 day'))) return $locale['TOMORROW'];    
    else if ($mysqlDate == date('Y-m-d', strtotime('-1 day'))) return $locale['YESTERDAY'];
    
    return mysqlToDate($mysqlDate);    
}

function dateEmpty($date) {
    if ($date) {
        $matches;
        return preg_match_all("/[1-9]+/", $date, $matches) > 0;
    } else return false;
}

function holidayImage($holiday, $bigImage=true, $fromMainDomain=true) {
    GLOBAL $sheme;
    if ($bigImage && $holiday['image'])                              
        $image = 'oformi-foto.ru/games/data/200/'.$holiday['image'].'.jpg';                    
    else $image = 'oformi-foto.ru/games/data/64/'.str_replace('.', '', $holiday['func']).'.jpg';
    
    if ($fromMainDomain) $image = $sheme.'oformi-foto.ru/getimage/getimage.php?src='.$image;
    else $image = $sheme.$image;
    
    return $image;
}


function parseTags($text, $paseModule=true) {

    GLOBAL $controller;
    $result = array();
    preg_match_all(MTAGS_REG, $text, $result);
    if (count($result) > 0) {
        $list = $result[0];
        $tags = $result[1];
        $texts = $result[2];
        foreach ($list as $i=>$item) {
            $taga   = explode(' ', $tags[$i]);
            $rep    = $texts[$i];
            $attr   = isset($taga[1])?trim($taga[1]):false;
            if ($taga[0] == 'a') {
                if ($attr) {
                    if (substr($attr, 0, 4) != 'http') $href = MAINURL."/{$attr}.html";
                    else $href = $attr;
                } else {
                    $href = MAINURL.'/'.str_replace(' ', '+', controller::translit(preg_replace('/\s/i', '+', strip_tags($texts[$i])))).'.html';
                }
                $rep = "<a href=\"{$href}\">{$texts[$i]}</a>";
            } else if ($taga[0] == 'img') {
                $align = '';
                if (isset($taga[2])) $align="align=\"$taga[2]\"";
                
                $imgurl = (preg_match_all("/http[s]*:\/\//i", $attr, $r)==0)?(MAINURL.'/'.$attr):$attr;
                
                $rep = "<img src=\"".$imgurl."\" alt=\"{$texts[$i]}\" $align>";
            } else if ($taga[0] == 'pimg') {
                $rep = postImage($attr, $texts[$i], true);
            } else if ($paseModule && ($taga[0] == 'module')) {
                $modulePath = TEMPLATES_PATH.$texts[$i].'.html';
                if (file_exists($modulePath)) {
                    ob_start();
                    if ($attr) eval($attr.';');
                    require TEMPLATES_PATH.$texts[$i].'.html';
                    $rep = ob_get_contents();
                    ob_end_clean();
                } else {
                    if (SSRELATIVE == 'ss/') $style = 'display:none';
                    else $style = "color:#AA0000";
                    $rep = "<div style=\"{$style}\">MODULE $rep NOT FOUND</div>";
                }
            } else $rep = '';
            $text = str_replace($item, $rep, $text);
        }
    }
    return $text;
}

function strip_all_tags($text) {
    return preg_replace(MTAGS_REG, '', strip_tags($text));    
}

function t($text_indent) {
    GLOBAL $mysqli;
    if (is_numeric($text_indent)) $query = "SELECT `text` FROM `gpj_texts` WHERE `text_id`={$text_indent} AND lang='".ss::lang()."'";
    else $query = "SELECT * FROM `gpj_texts` WHERE `translit`='".$mysqli->real_escape_string($text_indent)."' AND lang='".ss::lang()."'";
    return DB::line($query);
}

function tHtml($text_indent) {
    $result = '';
    if ($text = t($text_indent)) $result = '<div class="post"><h2>'.$text['title'].'</h2>'.parseTags($text['text']).'</div>';        
    return $result;
}

function postImage($relativeURL, $alt, $return=false) {
    GLOBAL $locale;
    $result = '<div class="post-image"><img src="'.MAINURL.$relativeURL.'" alt="'.$alt.'"><a href="#" class="img-button" title="'.$locale['SHOWHIDE'].'"></a></div>';
    if (!$result) echo $result;
    return $result;
}

function genderText($male, $female, $user) {
    return ($user['gender']=='female')?$female:$male;
}

function parseDesc($desc, $paseModule=true) {
    $list = array();
    preg_match_all('/<a(.*?)>(.*?)<\/a>/i', $desc, $list);
    
    if (count($list) > 1) {
        for ($i=0; $i<count($list[1]); $i++) {
            $source = $list[0][$i];
            $inTa = trim($list[1][$i]);
            if (stripos($inTa, 'href') === false) {
                $word = trim($list[2][$i]);
                $ta = controller::translit($inTa?$inTa:$word);
                $link = MAINURL.'/'.FINDSELECTOR.'/'.$ta.'.html';
                $desc = str_replace($source, "<a href=\"$link\" rel=\"nofollow\">{$word}</a>", $desc);
            }
        } 
    }
    
    return parseTags($desc, $paseModule);
}

function isCurrentLink($link) {
    GLOBAL $_SERVER;
    $_SERVER['REQUEST_URI'];    
    return stripos($_SERVER['REQUEST_URI'], $link);
}

function quickLinks($qlg_name) { 
    GLOBAL $locale;
    if (ss::lang() == 'ru') {
        if (!isset($qlg_name)) $qlg_name = 'login';
         
        $quick_links = DB::asArray("SELECT l.url, l.title, l.name, g.title AS groupTitle FROM 
            gpj_quicklinks_items i INNER JOIN gpj_quicklinks l ON i.item_id = l.id
            INNER JOIN gpj_quicklinks_group g ON i.group_id = g.id 
         WHERE g.name='{$qlg_name}'");
         if (count($quick_links) > 0) { 
    ?>
<div class="quick-links <?=$qlg_name?>">
    <h3><?=$quick_links[0]['groupTitle']?></h3>
    <?
        foreach ($quick_links as $link) 
            if (!isCurrentLink($link['url'])) {
        $url = (strpos($link['url'], 'html://') === false)?(MAINURL.'/'.$link['url']):$link['url'];
    ?>
    <a href="<?=$url?>" title="<?=$link['title']?>"><?=$link['name']?></a>
    <?}?>        
</div>
    <?
        }
    }
}

function refreshAvatar($source, $uid, $avatarURL) {
    $result = 'exists';
    if ($avatarURL) {
        $imagePath = AVAPATH.$source.'/'.$uid;
        $avaRefresh = !file_exists($imagePath);
        if (!$avaRefresh) 
            $avaRefresh = filesize(AVAPATH.'default') == filesize($imagePath); // Обновить аватарку если изображение аватарки по умолчанию
            
        if ($avaRefresh) {
            if ($image = file_get_contents($avatarURL))
                $result = file_put_contents($imagePath, $image)?'loaded':'error';
        }
    }
    return false;
}

function indexOfLike($list, $value) {
    foreach ($list as $i=>$pattern) 
        if (preg_match($pattern, $value) == 1) return $i;
    
    return -1; 
}

function isBot() {
    GLOBAL $_SERVER;
    
    $fingers = array(
        '/yandex\.ru/', '/yandex\.com/', '/google\.ru/', '/google\.com/', 
        '/rambler\.ru/', '/mail\.ru/', '/bing\.com/', '/ask\.com/'
    );
    
    return indexOfLike($fingers, $_SERVER['HTTP_USER_AGENT']) > -1;
}

?>