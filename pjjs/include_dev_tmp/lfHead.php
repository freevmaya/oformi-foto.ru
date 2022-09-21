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
<script src="<?=$jaPath?>/mootools-core-1.4.5-c.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/mootools-more-1.4.0.1.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/mootools-tips.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/debug.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/base64.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/events.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/canvasSave.<?=$jsExt?>" type="text/javascript"></script>

<script src="<?=$jaPath?>/geom/matrix.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/geom/vector.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/geom/rectangle.<?=$jsExt?>" type="text/javascript"></script>

<script src="<?=$jaPath?>/canvas/utils.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/canvas/draw-object.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/canvas/draw-object-more.<?=$jsExt?>" type="text/javascript"></script> 
<script src="<?=$jaPath?>/canvas/gcanvas.<?=$jsExt?>" type="text/javascript"></script> 
<script src="<?=$jaPath?>/canvas/holes-image.<?=$jsExt?>" type="text/javascript"></script>
  
<script src="<?=$jaPath?>/pjApp/defImages.<?=$jsExt?>" type="text/javascript"></script> 
<script src="<?=$jaPath?>/lfApp/lfcanvas.<?=$jsExt?>" type="text/javascript"></script> 
<script src="<?=$jaPath?>/pjApp/baseapp.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/lfApp/lfApp.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/pjApp/partsList.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/lfApp/lfpartsList.<?=$jsExt?>" type="text/javascript"></script>  
<script src="<?=$jaPath?>/pjApp/textEditor.<?=$jsExt?>" type="text/javascript"></script>   
<script src="<?=$jaPath?>/lfApp/lfmenu.<?=$jsExt?>" type="text/javascript"></script>  
<script src="<?=$jaPath?>/pjApp/toast.<?=$jsExt?>" type="text/javascript"></script>    

<script src="<?=$jaPath?>/lfApp/lf_tmplList.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/locale/<?=$language?>.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/locale/bypass.<?=$jsExt?>" type="text/javascript"></script>

<script src="<?=$jaPath?>/mootools-slider.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/controls/drag.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/controls/basePanel.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/controls/color-panel.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/input/base-input.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/input/pc-input.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/input/tablet-input.<?=$jsExt?>" type="text/javascript"></script>
<script src="<?=$jaPath?>/canvas/filters/baseFilter.<?=$jsExt?>" type="text/javascript"></script> 
<script src="<?=$jaPath?>/canvas/filters/colorTransform.<?=$jsExt?>" type="text/javascript"></script>
<?=$head?>