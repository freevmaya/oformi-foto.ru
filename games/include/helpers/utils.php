<?
class Utils {
    public static function createRequest($url, $app_id, $params) {
        GLOBAL $secrets;
        $params = array_merge($params);
        $params['app_id'] = $app_id;
        $params['sig']    = Request::genSig($params, $secrets);
        ksort($params);
        $query = '';
        foreach ($params as $key=>$param) $query .= ($query?'&':'?').($key.'='.urlencode($param));
        return $url.$query;
    }
}
?>