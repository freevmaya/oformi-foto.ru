<?
	$default = 'cpaz';

	$size = array(
		(@$_GET['width']?$_GET['width']:1000), 
		(@$_GET['height']?$_GET['height']:90)
	);


	$advList = array(
		'cpaz'=> array(
			'size'=> array('x'=>728, 'y'=>90),
			'html'=>'templates/adv/cpazilla_728.html'
		),

		'cpaz_v'=> array(
			'size'=> array('x'=>120, 'y'=>600),
			'html'=>'templates/adv/cpazilla_120x600.html'
		),

		'yandex'=> array(
			'size'=>array('x'=>1000, 'y'=>90),
			'html'=>'templates/adv/yandex-adv-1000.html'
		),

		'yandex_s'=> array(
			'size'=>array('x'=>728, 'y'=>90),
			'html'=>'templates/adv/yandex-adv-1000.html'
		)
	);


	$adv = $advList[@$_GET['adv']?@$_GET['adv']:$default];
?>
<html>
<head>
</head>
<body style="width:<?=$size[0]?>px; height:<?=$size[1]?>px; margin:0px;">
	<div style="width:<?=$adv['size']['x']?>px; height: <?=$adv['size']['y']?>px; margin: 0 auto;">
<?
	include($adv['html']);
?>
	</div>
</body>
</html>