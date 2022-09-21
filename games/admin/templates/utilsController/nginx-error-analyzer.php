<h1>Разбор ошибок на сервере error_log</h1>
<pre>
<?
$num=0;

if (isset($summary['error'])) {
    function itemsCmd($item1, $item2) {
        return $item1['count'] < $item2['count'];
    }
    uasort($summary['error'], 'itemsCmd');
?>
</pre>
<table class="report">
    <tr>
        <th>URL</th>
        <th>Count errors</th> 
        <th>ID</th> 
        <th>ext</th>  
        <th>action</th>  
        <th>check</th>
    </tr>
    <?
        foreach ($summary['error'] as $url=>$item) {
            if (!file_exists($item['pathFile'])) {        
            $num++;
    ?>
    <tr class="<?=($num%2==0)?'odd':''?>">
        <td><a href="<?=Admin::sheme().'://'.$url?>" target="_blank" title="<?=htmlentities($item['lines'][0])?>"><?=$url?></a>
        </td>
        <td><?=$item['count']?></td>
        <td><?=@$item['id']?></td>
        <td><?=@$item['ext']?></td>
        <td><?=actionLink($item)?></td>
        <td><?=checkLink($item)?></td>
    </tr>
    <?}}?>
</table>
<?}?>

<?if ($no_proclines) {?>
<h2>Необработанные строки</h2>
<pre>
    <?=$no_proclines?>
</pre>
<?}?>