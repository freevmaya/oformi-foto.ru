<?

$profile_list = array();
class profile {

    public static function start() {
        GLOBAL $profile_list;
        $profile_list[] = microtime(1);
    }

    public static function stop() {
        GLOBAL $profile_list;
        $result = 0;
        if (($index = count($profile_list) - 1) > 0); {
            $result = microtime(1) - $profile_list[$index];
            array_splice($profile_list, $index, 1);
        }
        return $result;
    }
}
?>