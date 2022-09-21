<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$charset?>">
<title><?=$title?></title>
<meta name="description" content="<?=$description?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

<link href="styles/styles-lf.css" rel="stylesheet" />
<link href="styles/styles-slider-lf.css" rel="stylesheet" />

<script stype="text/javascript">
    var HOSTURL='<?=$hostURL;?>';
    var PROTOCOL='<?=$protocol?>';
    var VER='<?=$v?>'; 
    var MAXSAVEADV = 0;
    var ISDEV = <?=@$isDev?1:0?>;
</script>

<!--[if IE]><script type="text/javascript" src="ie/excanvas.<?=$jsExt?>"></script><![endif]-->
<script src="<?=$jaPath?>/locale/<?=$language?>.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/lfmini.<?=$jsExt?>" type="text/javascript"></script>
<?=$head?>