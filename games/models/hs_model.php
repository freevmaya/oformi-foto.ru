<?

    define('HSCACHEPATH', DATA_PATH.'hs_cache/');
    
    class hs extends g_model {
    
        private $feeds = array(
            array('url'=>'http://www.hyrax.ru/cgi-bin/bn_xml.cgi', 'name'=>'Ежедневный общий гороскоп', 'description'=>'Основан на лунном календаре для 12 знаков зодиака.', 'offset'=>0),
            array('url'=>'http://www.hyrax.ru/cgi-bin/love_xml.cgi', 'name'=>'Ежедневный любовный гороскоп', 'description'=>'Любовный гороскоп для 12 знаков зодиака.', 'offset'=>-1),
            array('url'=>'http://www.hyrax.ru/cgi-bin/mob_xml.cgi', 'name'=>'Ежедневный мобильный гороскоп', 'description'=>'Юмористический гороскоп для владельцев мобильных телефонов для 12 знаков зодиака', 'offset'=>-1),
            array('url'=>'http://www.hyrax.ru/cgi-bin/auto_xml.cgi', 'name'=>'Ежедневный автомобильный гороскоп', 'description'=>'Юмористический гороскоп для владельцев автомобилей 12 знаков зодиака.', 'offset'=>-1),
            array('url'=>'http://www.hyrax.ru/cgi-bin/cook_xml.cgi', 'name'=>'Ежедневный кулинарный гороскоп', 'description'=>'Кулинарный гороскоп для 12 знаков зодиака.', 'offset'=>-1)
        );
    
        function getHoros($params) {
            $date = date('d-m');
            if (isset($params[1]))
                $cache_file = $params[1]; 
            else $cache_file = 'c_'.$params[0].'_'.$date.'.json';
            if (file_exists(HSCACHEPATH.$cache_file)) {
                return file_get_contents(HSCACHEPATH.$cache_file);
            } else return $this->converContent($params[0], $cache_file);
        } 
        
        private function converContent($feedType, $toFile) {
            GLOBAL $app;
            require_once INCLUDE_PATH.DS.'xml_parser.inc';
            $error = '';
            $result = array('feed'=>$this->feeds[$feedType - 1], 
                            'file'=>$toFile);
            $rss = xml_to_array(file_get_contents($result['feed']['url']), $error);
            $result['content']  = $rss['rss']['channel'];
            $result['date']     = date('d.m.Y');
            
            $json_result = $app->arr_to_json($result);
            $file = fopen(HSCACHEPATH.$toFile, 'w+');
            fwrite($file, $json_result);
            fclose($file);
            return $json_result;
        }
    }
?>