<?

define('OKAPIURL', 'https://api.ok.ru/api/');

class OKServer {
    
    public static function sig($values, $access_token='') {
        GLOBAL $secrets;
        $query_str = '';
        foreach ($values as $key=>$value) $query_str .= $key.'='.$value;

        if ($access_token)        
             $session_secret_key = md5($access_token.$secrets[$values['application_key']]);
        else $session_secret_key = $secrets[$values['application_key']];
          
        $query_str .= $session_secret_key;
        return md5($query_str);    
    } 
    
    public static function request($appKey, $method, $params, $access_token='') {
        GLOBAL $sKeys;
        $params = array_merge($params);
        $params['application_key'] = $appKey;
        $params['format'] = 'JSON';
        
        ksort($params);
        
        $query = '?sig='.OKServer::sig($params, $access_token);
        
        if ($access_token) $params['access_token'] = $access_token;
        foreach ($params as $key=>$param) $query .= '&'.($key.'='.urlencode($param));
        
/*        $result = '';
        $fp = fsockopen(MAILAPIURL.$query, 80, $errno, $errstr);
        if (!$fp) {
            $result = '{"ERROR":"'.$errno.'-'.$errstr.'"}';
        } else {
            $result = fread($fp);
            fclose($fp);
        }
        
        return json_decode($result);*/
        $url = OKAPIURL.$method.$query;
        //echo($url);
//        echo "query: $url\n\n";
        $result = json_decode(file_get_contents($url), true);      
        //trace(print_r($result, true));
            
        return $result;
    }
    
}
?>                               