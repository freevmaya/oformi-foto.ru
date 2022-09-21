<?
class statistic {
    public static function add($intKey, $charKey, $value) {
        $query = "INSERT INTO gpj_statistic (`varInt`, `varChar`, `value`) VALUES ($intKey, '$charKey', '$value')";
        return sql_query($query);
    }
    
    public static function addClientInfo() {
        global $_SERVER;
        $ip     = $_SERVER['REMOTE_ADDR'];
        $time   = $_SERVER['REQUEST_TIME'];
        print_r($_SERVER);
    } 
}
?>