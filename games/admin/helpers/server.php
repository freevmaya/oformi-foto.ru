<?

define('MAILAPIURL', 'http://www.appsmail.ru/platform/api');

class MAILServer {
    
    
    public static function request($app_id, $method, $params) {
        GLOBAL $sKeys;
        $params = array_merge($params);
        $params['method'] = $method;
        $params['app_id'] = $app_id;
        $params['secure'] = 1;
        $params['sig']    = Request::genSig($params, $sKeys);
        ksort($params);
        
        $query = '';
        foreach ($params as $key=>$param) $query .= ($query?'&':'?').($key.'='.urlencode($param));
/*        
        $result = '';
        $fp = fsockopen(MAILAPIURL.$query, 80, $errno, $errstr);
        if (!$fp) {
            $result = '{"ERROR":"'.$errno.'-'.$errstr.'"}';
        } else {
            $result = fread($fp);
            fclose($fp);
        }
        
        return json_decode($result);
*/        
        if (!($result = @file_get_contents(MAILAPIURL.$query))) $result = '{"error":"400", "query":"'.MAILAPIURL.$query.'"}';
        return json_decode($result, true);
    }
    
}
?>