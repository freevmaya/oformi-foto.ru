<script type="text/javascript">
    window.addEvent('domready', function() {
        if (ch = $('currentHoliday')) {
            var bw = 280;
            var lw;
            var fw;
            function updateHBlock() {
                lw = $('wrapper').getCoordinates().left;
                if (lw >= bw * 0.5) {
                    if (!ch.hasClass('hdleft')) ch.addClass('hdleft');
                    fw = ch.getSize().x;
                    ch.setStyle('left', lw - fw);
                } else {
                    if (ch.hasClass('hdleft')) ch.removeClass('hdleft')
                    fw = 0;
                }
            }        
            
            window.addEvent('resize', function() {
                updateHBlock();
            });
            
            updateHBlock();
            ch.addEvent('mouseenter', function() {if (ch.getCoordinates().left < 0) ch.morph({left: 0});});
            ch.addEvent('mouseleave', function() {ch.morph({left: lw - fw});});
        }
    });
</script>
<?
    GLOBAL $locale;
    
    if (!isset($holidayNoWhere)) $holidayNoWhere = '';
    $date = date('Y-m-d');
    $holidays   = DB::asArray("SELECT *, `date`='$date' AS today FROM gpj_holiday WHERE (`date`>='$date' AND `date`<= NOW() + INTERVAL 5 DAY) $holidayNoWhere ORDER BY `date` LIMIT 0, 2");
    if (count($holidays) > 0) {
?>        
        <div id="currentHoliday">
            <h2><?=(count($holidays)==1)?$locale['CURRENT_HOLIDAY']:'<img src="'.SSURL.'/images/hhd.gif">'?></h2>
            <div id="holiday-block"><?
                foreach ($holidays as $item) {
                    $hlit = $item['holiday_id'];
                    $image = holidayImage($item);
                    
                    $spanClass = '';
                    $tlit = controller::translit($item['name']);
                    $link = MAINURL."/holidays/holiday-{$hlit}-{$tlit}.html";
                    $desc = limitWords(strip_tags($item['desc']), 20);
                    $date = $item['today']?$locale['TODAY']:todayDate($item['date']);
                    $name = "<b>{$date}</b> ".$item['name'];
                    if ($item['today']) $spanClass = 'class="today"';                   
                    echo "<span $spanClass>";
                    /*
                    if ($item['today']) {                                            
                        echo "<img src=\"{$image}\" alt=\"{$item['name']}\" align=\"left\">";
                        echo "<a href=\"{$link}\">{$name}</a>";    
                    }else {
                    */
                        echo "<a href=\"$link\" class=\"tipz\" title=\"{$locale['DESC']}::{$desc}\">{$name}</a>";    
                    //} 
                    echo '</span>';
                }                
            ?> 
            <span style="text-align:right"><a href="<?=MAINURL."/holidays/holiday.html";?>"><?=$locale['ALLHOLIDAYS']?>...</a></span>
            </div>  
        </div>
<?}
