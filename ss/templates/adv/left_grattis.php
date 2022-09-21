<style type="text/css">
	.left-extend-block, .right-extend-block {
	    position: fixed;
	    width: 125px;
	    height: 100%;
	    top: 0px;
	    transition: padding-top 0.5s; 
	}

	.left-extend-block {
	    position: fixed;
	    top: 220px;
	    margin-left: -125px;
	    overflow: hidden;
	}

	.left-extend-block:hover {
	    overflow: unset;
	    z-index: 1000;
	}

	@keyframes anim_title {
	  from {opacity: 0;}
	  to {opacity: 1;}
	}

	.left-extend-block .title {
	    transform: rotate(-90deg);
	    width: 300px;
	    margin-left: -130px;
	    margin-top: 210px;
	    position: absolute;
	    font-size: 18px;
	    font-family: fantasy;
	    color: #3376ca;
	    text-align: center;
	    opacity: 0;
	    animation: anim_title 1s forwards ease-in-out 1s;
	}
</style>
<div class="title">Поздравления по телефону</div>
<div class="grattis">
	<!--noindex-->
	<div id="grattis_widget_40625"><script async type="text/javascript">
	//<![CDATA[
	(function (d,w,s,f){f=function(){if(typeof w.GRATTIS==="object"){
	return w.GRATTIS.promo("widget",40625,null,false);}setTimeout(f,100)};
	if(typeof w.GRATTIS_l==="undefined"){s=d.createElement("script");
	s.src="//cloud2.grattis.ru/publicdata/code.js";
	s.async=true;s.type="text/javascript";w.GRATTIS_l=null;
	d.getElementsByTagName("head")[0].appendChild(s);}f();
	})(document, window);
	//]]>
	</script></div>
	<!--/noindex-->
</div>