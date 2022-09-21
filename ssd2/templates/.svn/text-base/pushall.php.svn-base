<?
    $pushall = $this->pushAll(isset($user)?$user:ss::getUser());
?>
<div class="pushall-widget">
    <?ss::injectLang('pushall_desc.html')?>
    <iframe frameborder="0" src="https://pushall.ru/widget.php?subid=<?=PUSHALLID?>" width="320" height="120" scrolling="no" style="overflow: hidden;">
    </iframe>
</div>
<?if (@$pushall['add_status']) {?>
<div><h2><?=$locale['SUBSREG']?></h2></div>
<?}?>