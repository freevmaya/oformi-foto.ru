<div style="float:right;width:100px; margin-right: 50px;">
    <div id="ok_shareWidget"></div>
</div>
    
<script>
!function (d, id, did, st) {
  var js = d.createElement("script");
  js.src = "http://connect.ok.ru/connect.js";
  js.onload = js.onreadystatechange = function () {
  if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
    if (!this.executed) {
      this.executed = true;
      setTimeout(function () {
        OK.CONNECT.insertShareWidget(id,did,st);
      }, 0);
    }
  }};
  d.documentElement.appendChild(js);
}(document,"ok_shareWidget","http://dev.ok.ru","{width:100,height:100,st:'straight',sz:100,nt:1,nc:1}");
</script>
