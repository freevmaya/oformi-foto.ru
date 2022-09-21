<?
if (!defined('MAILAPIURL'))
    define('MAILAPIURL', 'http://www.appsmail.ru/platform/api');

class mailAPI {
    
    
    public static function request($app_id, $method, $params) {
        GLOBAL $secrets;
        $params = array_merge($params);
        $params['method'] = $method;
        $params['app_id'] = $app_id;
        $params['uid']  = '8062938299454250872'; 
        $params['sig']    = Request::genSig($params, $secrets);
        ksort($params);
        
        $query = '';
        foreach ($params as $key=>$param) $query .= ($query?'&':'?').($key.'='.urlencode($param));
        echo MAILAPIURL.$query;
        if (!($result = @file_get_contents(MAILAPIURL.$query))) $result = '{"error":"400"}';
        return json_decode($result, true);
    }
    
}
?>