<?
    $bannerData = array(
        json_decode(file_get_contents('http://elephant.fotostrana.ru/banner?adv_id=22307&ref_id=451859761&format=json&gid=1&place=600x90')),
        json_decode(file_get_contents('http://elephant.fotostrana.ru/banner?adv_id=22307&ref_id=989616825&format=json&gid=2&place=600x90'))
    );
    
    print_r($data);
?>
<div id="banners">
    <div id="banner0">
    </div>
    <div id="banner1">
    </div>
</div>
<script type="text/javascript">
    var s17;
    <?foreach ($bannerData as $key=>$item) {
        $layer = 'banner'.$key;
    ?>
        s17 = new SWFObject("<?=$item->source?>", "<?=$layer?>", "530", "79", "9");
        s17.addParam("allowScriptAccess","always");
        s17.addParam("scaleMode","noscale");
        s17.addParam("wmode","transparent");
        s17.addParam("flashVars","ref_id=451859761&link1=http://elephant.fotostrana.ru/<?=$item->follow?>");
        s17.write("<?=$layer?>");
    <?}?>    
</script>