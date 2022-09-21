<style type="text/css">
    .item, .info-wrap, .info, .item .info-front, .item .info-back {
        width: 200px;
        height: 80px;
        -moz-border-radius: 8px;
        border-radius: 8px;
        -webkit-border-radius: 8px;
    }
    
    .item {
        display: inline-block;
        position: relative;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        cursor: default;
        margin: 0px 5px;
    }
    
    .buttons {
        -webkit-perspective: <?=$pp?>px;
        -moz-perspective: <?=$pp?>px;
        -o-perspective: <?=$pp?>px;
        -ms-perspective: <?=$pp?>px;
        perspective: <?=$pp?>px;
        -webkit-transition: all <?=$speed?>s ease-in-out;
        -moz-transition: all <?=$speed?>s ease-in-out;
        -o-transition: all <?=$speed?>s ease-in-out;
        -ms-transition: all <?=$speed?>s ease-in-out;
        transition: all <?=$speed?>s ease-in-out;
    }
    
    .info {
        position: absolute;
        -webkit-transform-origin: 100% 100%;
        -moz-transform-origin: 100% 100%;
        -o-transform-origin: 100% 100%;
        -ms-transform-origin: 100% 100%;
        transform-origin: 100% 100%;
        -webkit-transition: all <?=$speed?>s ease-in-out;
        -moz-transition: all<?=$speed?>s ease-in-out;
        -o-transition: all <?=$speed?>s ease-in-out;
        -ms-transition: all <?=$speed?>s ease-in-out;
        transition: all <?=$speed?>s ease-in-out;
        -webkit-transform-style: preserve-3d;
        -moz-transform-style: preserve-3d;
        -o-transform-style: preserve-3d;
        -ms-transform-style: preserve-3d;
        transform-style: preserve-3d;
    }
    
    .item .info-front, .item .info-back {
        position: absolute;
        display: block;
        background-position: center center;
        background-repeat: no-repeat;
        cursor: pointer;
    }
        
    .info > a {
        -webkit-backface-visibility: hidden;
        -moz-backface-visibility: hidden;
        -o-backface-visibility: hidden;
        -ms-backface-visibility: hidden;
        backface-visibility: hidden;
    }
    .info .info-back {
        -webkit-transform: rotate3d(0, 1, 0, 180deg);
        -moz-transform: rotate3d(0, 1, 0, 180deg);
        -o-transform: rotate3d(0, 1, 0, 180deg);
        -ms-transform: rotate3d(0, 1, 0, 180deg);
        transform: rotate3d(0, 1, 0, 180deg);
        background: #000;
    }
    
    .item:hover .info-wrap {
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.8), inset 0 0 3px rgba(115, 114, 23, 0.8);
        background-color: rgba(255, 255, 255, 0.7);
    }
    .item:hover .info {
        -webkit-transform: rotate3d(1, 0, 0, <?=$deg?>deg);
        -moz-transform: rotate3d(1, 0, 0, <?=$deg?>deg);
        -o-transform: rotate3d(1, 0, 0, <?=$deg?>deg);
        -ms-transform: rotate3d(1, 0, 0, <?=$deg?>deg);
        transform: rotate3d(1, 0, 0, <?=$deg?>deg);
    }    
</style>