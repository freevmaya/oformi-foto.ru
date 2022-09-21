<?
$id = @$_GET['adv_id'];
$data=require(HOMEPATH.'/vmaya/games/banners/data.php');

if ($id) $list = array($data[$id]);
else {
	$list = array();
	while (count($list) < 5) {
		$rndid = rand(0, count($data) - 1);
		$list[] = $data[$rndid];
		array_splice($data, $rndid, 1);
	}
}
return $list;
?>