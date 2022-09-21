<?
$otherPages = otherArticles((isset($keywords) && is_array($keywords))?$keywords:null);
if (count($otherPages) > 0) { 
?>
<h2>Интересные разделы по этой теме:</h2>
<?=$this->title?textLimit($this->title, 60, 'h1'):''?>
<div class="post pages">
<?
foreach ($otherPages as $page) {
    $tText = implode('-', words($page['text'], 0, true));
?>
    <a href="<?=MAINURL.'/'.$tText?>.html"><?=$page['text']?></a>
<?}?>    
</div>
<?}?>