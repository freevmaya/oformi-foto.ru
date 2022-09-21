<?

class utils extends g_model {
    function getTime($params) {
        $result = array();
        if (isset($params[0])) {
            if (isset($params[1])) 
                 $result['time'] = strtotime("{$params[0]} {$params[1]}");
            else $result['time'] = strtotime("{$params[0]} now");
        }
        return $result;
    }
}

?>