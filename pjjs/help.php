<?
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru" dir="ltr">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="styles/help.css?v=1" type="text/css" media="screen" />
    <script src="jsa/mootools-core-1.4.5.js" type="text/javascript"></script>
    <script src="jsa/mootools-ss.js" type="text/javascript"></script>
    <script type="text/javascript">
        window.addEvent('domready', function() {
            var alinks = $$('.smsll');
            alinks.each(function(item) {
                item.addEvent('mouseover', function() {
                    item.set('tween', {duration: 50});
                    item.tween('margin-right', [0, 20]);
                    (function() {
                        item.set('tween', {duration: 200});
                        item.tween('margin-right', [item.getStyle('margin-right'), 0]);
                    }).delay(100);
                });
            });
            new Fx.SmoothScroll({
                links: alinks,
                wheelStops: false
            });
            
            var head = $('head');
            var fx = new Fx.Tween(head, {
                duration: 50,
                property: 'color'
            });
            var startColor = head.getStyle('color');
            var step = (function() {
                fx.start(startColor, '#fff');
                (function() {
                    fx.start('#fff', startColor);
                    step.delay(1000 + Math.random() * 3000);
                }).delay(100);
            })
            step.delay(1000);
        });
    </script>

</head>
<body>
    <div class="back">
        <div class="wrapper">
            <div class="content" style="background: url(images/user.jpg) left top no-repeat;">
                <div class="menu">
                    <h2>Содержание</h2>
                    <a href="#elems" class="smsll">Как поместить фотографию в вырез</a>
                    <a href="#inject" class="smsll">Как двигать, вращать и изменять размер</a> 
                    <a href="#color" class="smsll">Как корректировать цвет фотографии</a>
                    <a href="#save" class="smsll">Как сохранить на компьютер</a>
                    <a href="#send" class="smsll">Как отправить или опубликовать в соцсети</a>
                </div>
                <h1 class="head" id="head">КРАТКОЕ РУКОВОДСТВО</h1>
            </div>
            <div class="green desc">
                <div class="content">
                    Как создать коллаж или как украсить фотографию рамкой или фотоприколом
                </div>
            </div>
        </div>
    </div>
    <div class="wrapper2">
        <div class="content">
            <a name="elems"></a>
            <h2>Как поместить фотографию в вырез фото-шаблона</h2>
                <p>При первом запуске в приложение есть кнопка “Вставить ваше фото”. Коснитесь ее.</p>
                <p>Откроется диалог загрузки фотографии с компьютера. Выберите нужное фото.</p>
                <p>Что делать, если в шаблоне несколько вырезов, или же кнопка “Вставить Ваше фото” отсутствует?</p>
                <p>Нужно быстро дважды нажать на нужном вырезе. После этого появится меню выбора фотографий с компьютера. </p>
                
            <a name="inject"></a>        
            <h2>Как двигать, вращать и изменять размер.</h2>
                <p>Прикоснитесь пальцем к фотографии. Сразу после этого высветятся и пропадут ее контуры, а потом появится маленькое меню в форме кольца.</p> 
                
 <table border=0 cellpadding="12">
 <tr><td><img src="http://img-fotki.yandex.ru/get/9165/214220405.3/0_d51f6_32eb1e98_XS.png"></td>
 <td> Это меню настроек. Оно состоит из четырех частей. Рассмотрим каждую.</td></tr>
<tr>
<TD><img src="http://img-fotki.yandex.ru/get/9797/214220405.3/0_d51eb_47f5d46f_XS.png" alt="стрелки-крестик, желтая область"></TD>            
<TD><b>Перемещение.</b> Прикоснитесь пальцем к этому сектору. Меню исчезнет. Теперь, не отрывая пальца от фото, перемещайте его по своему усмотрению.</TD>           
</tr>
<tr><td colspan=2>Чтобы снова вызвать меню для настройки других параметров фото - вновь прикоснитесь пальцем к фотографии.</td></tr>

<tr><td><img src="http://img-fotki.yandex.ru/get/9762/214220405.3/0_d51ec_ee4304d1_XS.png"alt="круговые стрелки, зеленая область"></td>
<td><b>Вращение.</b> Нажмите пальцем на эту область. Меню исчезнет. Теперь вращайте фотографию, не отрывая пальца от экрана, до тех пор, пока фото не примет нужное положение.   
</tr>
<tr><td><img src="http://img-fotki.yandex.ru/get/9305/214220405.3/0_d51ed_3a0de164_XS.png" alt="двойная стрелка, синяя обасть"></td>
<td><b>Масштабирование</b> (увеличить или уменьшить фото). Нажмите на эту кнопку. Меню исчезнет. Теперь, не отрывая пальца от фото, двигайте им. Размер будет меняться в зависимости от направления движения.</br> 
При движении <b>в середину</b> - размер фото уменьшится; <b>наружу</b> - размер фото увеличится.</td>
</table>         
        
        <a name="color"></a>
            <h2>Как корректировать цвет фотографии</h2>    
                <p>Нужная кнопка находится в том же колечке-меню, о котором говорилось выше.</p>
 <table border=0 cellpadding="10"> 
 <tr><td valign=top><img src="http://img-fotki.yandex.ru/get/9165/214220405.3/0_d51ea_ef516f86_XS.png" alt=три разноцветных кружочка,розовая область></td>  
 <td>   <b>Цветокоррекция</b>
 <ul>
                     <li>Прикоснитесь к фотографии, чтобы появилось меню.</li>
                      <li> Теперь нажмите на розовую область меню с изображением разноцветных кружков. Вверху страницы появится новое меню с пятью бегунками белого, черного, красного, зеленого и  синего цветов. Двигая их, можно изменять цвет фото. Экспериментируйте. </li>
                <li>После того, как Вы добьетесь желаемого результата, закройте меню, нажав на крестик в его верхнем правом углу.</li>
                 </ul>        
</td></tr>  
</TABLE>                
                          
                 <a name="save"></a> 
            <h2>Как сохранить открытку на компьютер</h2>
  <table border=0 cellpadding="10"> 
  <tr><td valign=top><img src="http://img-fotki.yandex.ru/get/9305/214220405.3/0_d51f4_ff6aec1e_XS.gif" alt="дискета"></td>            
  <td>Это - кнопка сохранения. Она находится в нижней части экрана, под рамкой с фотографией.
             <ul>
             <li>Нажмите ее. Ваше изображение откроется в новом окне. </li>
              <li>Теперь прикоснитесь пальцем к открытке и удерживайте до тех пор, пока внизу не появится новое меню внизу экрана.</li>
              <li>Выберите “Сохранить изображение”.  Открытка сохранится в папку, указанную в настройках Вашего планшета. </li>
              </ul></td>
</table>            
               <a name="send"></a>
            <h2>Как отправить открытку другу по почте или опубликовать в соцсети</h2>       
                <p>Свою работу Вы можете показать своим друзьям, используя для этого электронную почту или социальные сети. Кнопки для отправки находятся в верхней части экрана.</p>
                <p><img src="http://img-fotki.yandex.ru/get/9808/214220405.3/0_d51ef_113fab55_XS.gif" hspace="10">Оправить открытку по почте своему другу;</p>
                <p>Опубликовать в соцсетях:</p>
                   <p><img src="http://vk.com/images/share_32.png" hspace="10">ВКонтакте;</p>
                  <p><img src="http://img-fotki.yandex.ru/get/9108/214220405.3/0_d51f1_97ccf22c_XS.gif" hspace="10"/>Одноклассники;</p>
                  <p><img src="http://img-fotki.yandex.ru/get/9061/214220405.3/0_d51f0_4e12f356_XS.gif" hspace="10">Мой Мир;</p> 
                  <p><img src="http://img-fotki.yandex.ru/get/6731/214220405.3/0_d51e9_fce6d136_XS.gif" hspace="10">Facebook;</p>
                  <p><img src="http://img-fotki.yandex.ru/get/6731/214220405.3/0_d51fc_2e10071_XS.gif" hspace="10">Twitter.</p>
                <p> После нажатия на любую из этих кнопок, появится окно авторизации. Введите свой логин, под которым Вы зарегестрированы, и пароль. Вы можете опубликовать свое фото только в той соцсети, в которой у Вас есть аккаунт.</p>

                        </div>
    </div>
</body>
</html>













