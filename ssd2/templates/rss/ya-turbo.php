<?
    GLOBAL $locale;

    include_once(SSPATH.'helpers/templates.php');
?>
<rss xmlns:yandex="http://news.yandex.ru"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:turbo="http://turbo.yandex.ru"
     version="2.0">
    <channel>
        <title><?=$locale['DEFAULT_TITLE']?></title>
        <link><?=MAINURL?></link>
        <description><?=$locale['DEFAULT_DESC']?></description>
        <language>ru</language>
        <?
            foreach ($items as $item) {
                $trans = controller::translit($item['name']);
                $link = MAINURL."/template/".$item['tmpl_id']."-".$trans.".html";
                $link_png = MAINURL."/template/".$item['tmpl_id']."-png,".$trans.".html";
?>
        <item turbo="true">
            <link><?=$link?></link>
            <turbo:source></turbo:source>
            <turbo:topic><?=$item['name']?></turbo:topic>
            <pubDate><?=date("r", strtotime($item['insertTime']))?></pubDate>
            <author><?=$item['autor']?$item['autor']:'vmaya'?></author>
            <turbo:content>
                <![CDATA[
                <header>
                    <h1><?=$item['name']?></h1>
                    <figure>
                        <img src="<?=FRAMES_URLPREVIEW.$item['tmpl_id']?>.jpg"/>
                    </figure>
                    <menu>
                        <a href="<?=$link?>">Вставить ваше фото</a>
                        <a href="<?=$link_png?>">Скачать PNG шаблон</a>
                        <a href="<?=MAINURL?>/fotoramki.html">Все новые фоторамки</a>
                        <a href="<?=MAINURL?>/holidays/holiday.html">Все праздничные фоторамки</a>
                        <a href="<?=MAINURL?>/fotoramki/S+dnem+rozhdenija.html">С днем рождения</a>
                        <a href="<?=MAINURL?>/articles.html">Другие статьи</a>
                    </menu>
                </header>
                <h4>Новая фоторамка для вашего фото</h4>
                <p><?=str_replace("\n", "<br>", $item['desc'])?></p>
                <button formaction="<?=$link?>">Вставить ваше фото</button>
                ]]>
            </turbo:content>
        </item>
<?                
            }
        ?>
    </channel>
</rss>