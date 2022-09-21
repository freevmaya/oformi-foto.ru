<?

define('TMPLURL', 'http://pj.1gb.ru/pj/jpg_preview/');
define('MAXCOUNT', 30);

$fileURL = 'http://pj.1gb.ru/games/data/temp_storage.json';

$json = json_decode(file_get_contents($fileURL));
//$date = @filectime($fileURL);

$tmpls = array();
foreach ($json->templates as $tmpl) {
    if (is_array($tmpl->id)) {
        for ($i=$tmpl->id[0];$i<$tmpl->id[1];$i++) $tmpls[] = array('tid'=>$i, 'gid'=>$tmpl->group);
    } else $tmpls[] = array('tid'=>$tmpl->id, 'gid'=>$tmpl->group);
}

$tmpls = array_reverse($tmpls);

$count = count($tmpls);
if ($count > MAXCOUNT) $count = MAXCOUNT;
    
function itemTemplate($innerHtml) {
    echo "<table><tr><td>$innerHtml</td></tr></table>";
}    
function tmplEcho($tid, $gid) {
    itemTemplate("<a href=\"http://oformi-foto.ru/pjjs/$tid.html\" target=\"_blank\"><img src=\"".TMPLURL."/i$tid.jpg\"></a>");
}    
?>
<html>
<head>
<title></title>
<style>
    body {
        min-width:300px;
        font-family:Arial,Tahoma;
        margin: 0px;
    }
    
    .wrapper {
        font-size: 11px;
    }
    
    .wrapper span {
        display: inline-block;
        width: 130px;
        text-align: right;
    }
    
    #newTemplates {
        width: 590px;
    }
    
    #newTemplates table {
        display: block;
        width: 80px;
        height: 80px;
        border: 2px solid #DDD;
        margin: 2px;
        padding: 5px;
        text-align: center;
        vertical-align: middle;
        float: left;
        background: #EEE;
    }
    
    #newTemplates table:hover {
        border: 2px solid #0F0;
    }
    
    #newTemplates img {
        max-width: 80px;
        max-height: 60px;
        margin: auto;
    }
    
    #newTemplates a {
        text-decoration: none;
        font-size: 12px;                                 
    }
    
    .slider {
        width       : 575px;
        overflow-x  : scroll;
    }
</style>
<body>
     <div class="wrapper">
        <div>
            <b>Свежие поступления</b>. Кликните на заинтересовавшем вас фото-оформлении
        </div>
        <div class="slider">
        <div id="newTemplates" style="width:<?=(($count + 1) * 105)?>px">
<?
    foreach ($tmpls as $n=>$tmpl) {
        tmplEcho($tmpl['tid'], $tmpl['gid']);
        if ($n == $count) break;
    }
    itemTemplate('<a href="http://oformi-foto.ru/pjjs.htm" target="_blank">Хочу больше шаблонов!</a>');        
?>        
        </div>
        </div>
    </div>
</body>
</html>