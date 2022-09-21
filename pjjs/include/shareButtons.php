<?
/*
    GLOBAL $protocol;
    $curIp = str_replace('.', '', $_SERVER['REMOTE_ADDR']);
    $imgName = '';
    
    for ($i=1; $i<=strlen($curIp); $i++) {
        $num = substr($curIp, $i, 1);
        if (is_numeric($num) && ($num < 3))
            $imgName .= chr($num + 97);
       // else $imgName .= $num;
    }
    
    $imgName .= date('mdi');
    
    $head .= "<script src=\"".$jaPath."/pjApp/shareButtons.".$jsExt."\" type=\"text/javascript\"></script>
<script type=\"text/javascript\" src=\"{$protocol}://vk.com/js/api/share.js?90\" charset=\"{$charset}\"></script>
    ";
    
    //$shareButtons .= '<span id="mail"></span>';
    $shareButtons .= '<span id="vk"></span><span id="mm"></span><span id="fb"></span><span id="tw"></span>';
    
    $script = "
    window.addEvent(EVENTS.DOMREADY, function(e) {
        var pid = (function() {
            if (_app) {
                new shareButtons($('shareButtons'), _app, '".$baseURL."user.php', '".$imgName."');
                clearInterval(pid);
            }
        }).periodical(100);
    });
";
*/
?>