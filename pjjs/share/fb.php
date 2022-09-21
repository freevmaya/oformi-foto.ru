<?
    $url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'];
?>

<div style="float:right;width:85px; margin-right: 100px;">
    <div id="fb-root"></div>
    <div class="fb-share-button" data-href="<?=$url?>" data-width="80" data-type="box_count"></div>
</div>    

<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>