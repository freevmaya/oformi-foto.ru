<table>
    <tr>
        <td>
<?
$colc = ceil(count($cats) / 4);
$ci = $colc - 1;
$part_name = '';
foreach ($cats as $i=>$cat) {
    if ($part_name != $cat['part_name']) {
        echo "<div><b>{$cat['part_name']}</b></div>";
        $part_name = $cat['part_name'];
    }
    echo '<div '.($cat['isGroup']?'class=""':'').'><input type="checkbox" name="cats[]" value="'.$cat['group_id'].'" '.($cat['isGroup']?'CHECKED':'').'>'.$cat['name'].'</div>';
    if ($ci <= $i) {
        $ci += $colc;
        echo '</td><td>';
    }
}
?>
        </td>
    </tr>
</table>