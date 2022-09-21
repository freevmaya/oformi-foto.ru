<?
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate'); 
    header('Cache-Control: post-check=0, pre-check=0', FALSE); 
    header('Pragma: no-cache');
    
    include_once('/home/config.php');
    
    include_once(INCLUDE_PATH.'/request.php');
    include_once(INCLUDE_PATH.DS.'_dbu.php');
    include_once(INCLUDE_PATH.DS.'app.php');
    include_once(INCLUDE_PATH.DS.'fdbg.php'); 
    
    $request = new Request();
    
    $target = $request->getVar('target', 'd2');
                              
    define('SSRELATIVE', 'ss'.$target.DS);
    define('SSPATH', MAINPATH.DS.'ss'.$target.DS);
    define('SSURL', MAINURL.DS.SSRELATIVE);
    
    class LanguageTranslator {
        const ENDPOINT = 'https://www.googleapis.com/language/translate/v2';
        protected $_apiKey;
     
        // конструктор, принимает Google API key единственным параметром
        public function __construct($apiKey) {
            $this->_apiKey = $apiKey;
        }
     
        // переводимый текст/HTML хранится в $data. Целевой язык перевода
        // в $target. Также Вы можете указать исходный язык в $source.
        public function translate($data, $target, $source = '')
        {
            // это данные для запроса
            $values = array(
                'key'    => $this->_apiKey,
                'target' => $target,
                'q'      => $data
            );
     
            // добавим $source, если он был указан
            if (strlen($source) > 0) {
                $values['source'] = $source;
            }
     
            // преобразуем массив в строку, 
            // чтобы их можно было использовать с cURL
            $formData = http_build_query($values);
     
            // создадим соединение с API
            $ch = curl_init(self::ENDPOINT);
     
            // просим cURL возвращать ответ, а не выводить его
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     
            // запишем данные в тело запроса POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
     
            // обмануть Google, использовать POST запрос как GET
            curl_setopt($ch, CURLOPT_HTTPHEADER, 
    	                      array('X-HTTP-Method-Override: GET'));
     
            // выполнить HTTP запрос
            $json = curl_exec($ch);
            curl_close($ch);
     
            // декодировать ответ
            $data = json_decode($json, true);
     
            // убедимся в том, что данные корректны
            if (!is_array($data) || !array_key_exists('data', $data)) {
                throw new Exception('Unable to find data key');
            }
     
            // ещё раз убедимся
            if (!array_key_exists('translations', $data['data'])) {
                throw new Exception('Unable to find translations key');
            }
    
            // и ещё раз
            if (!is_array($data['data']['translations'])) {
                throw new Exception('Expected array for translations');
            }
     
            // пройдёмся в цикле по данным и вернём первый перевод
            // если Вы переводите несколько текстов,
            // код ниже нужно поправить
            foreach ($data['data']['translations'] as $translation) {
                return $translation['translatedText'];
            }
         }
    }    
    
    $transURL = 'https://translate.google.ru/#ru/uk/';
    $srclan = 'ru';
    $deslan = 'uk';
    $translator = new LanguageTranslator('AIzaSyAdlVaLkt3POcoidnM17kDCq_3IImBFBY8');
    
    if (($source = $request->getVar('file', false)) && file_exists(SSPATH.$source)) {
        $stext = file_get_contents(SSPATH.$source);
        $reg = '/([\.,"\'\/\\_()А-Яа-я\s0-9]+)/u';
//        $reg = '/(\<\?[\.,"\'\/\\_()\r\tA-zА-Яа-я\s0-9=-]+\?\>)/i';
        preg_match_all($reg, $stext, $list, PREG_SET_ORDER );
        
        $acc = array();
        $chreg = '/([А-Яа-я]+)/u';
        $index = 0;
        foreach ($list as $item) {
            if ($str = trim($item[0])) {
                if (preg_match($chreg, $str) > 0) {
                    $index = strpos($stext, $str, $index);
                    
//https://inputtools.google.com/request?text=&itc=ru-t-i0-und&num=13&cp=0&cs=1&ie=utf-8&oe=utf-8&app=translate&cb=_callbacks____0j77kzhpf
                    $htmlres = $translator->translate($str, $deslan, $srclan);
                    echo $htmlres;
                    break;
                    
                    $acc[] = array($index, mb_strlen($str), $str);
                }
            }
        }
        print_r($acc);
    } else echo "file $source no found";
?>