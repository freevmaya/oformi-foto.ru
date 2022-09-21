<?

define('VKAPIURL', 'http://api.vkontakte.ru/api.php');

class VKServer {
    
    
    public static function request($api_id, $method, $params) {
        $params = array_merge($params);
        $params['api_id']       = $api_id;
        $params['method']       = $method;
        $params['timestamp']    = time();
        $params['random']       = rand(1, 100000);
        $params['v']            = '2.0';
        $params['format']       = 'JSON';
        $params['sig']          = VKServer::genSig($params);
        ksort($params);
        
        $query = '';
        foreach ($params as $key=>$param) $query .= ($query?'&':'?').($key.'='.urlencode($param));
        return json_decode(file_get_contents(VKAPIURL.$query), true);
    }
 
    public static function genSig($values) {
        GLOBAL $sKeys;
        
        $query_str  = '';
        $values     = array_merge($values);
        unset($values['sig']);  // Выкидываем сигнатуру
        ksort($values);         // Сортируем
        foreach ($values as $key=>$value) $query_str .= $key.'='.$value;
        $query_str .= $sKeys[$values['api_id']];
        return md5($query_str);
    }    
}
?>