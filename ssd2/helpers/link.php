<?
include_once(dirname(__FILE__).'/link-data.php');

define("LONGLINK", 'def');
define("SHORTLINK", 'seo');

class link {
    static protected $request;
    public static function init($a_request) {
        link::$request = $a_request;
    }
    
    public static function getVar($varName, $default=0) {
        return link::$request?link::$request->getVar($varName, $default):$default;
    }
    
    public static function c() {
        GLOBAL $link_data;
        
        $acount = func_num_args();
        $link = $link_data['default'];
        if ($acount > 0) {
            $args = func_get_args();
            if (($args[0] == LONGLINK) || ($args[0] == SHORTLINK)) {
                $type = $args[0];
                $args = array_splice($args, 1);
            } else $type = link::isSEOLink()?SHORTLINK:LONGLINK;
            if ($format = @$link_data[$args[0]][$type]) {
                $args = array_splice($args, 1);
                $link = vsprintf($format[count($args)], $args);
                if ($type == LONGLINK) {
                    $params = array_slice(link::$request->values, 1);
                    foreach ($params as $key=>$p) $link .= "&{$key}={$p}";
                }
            }
        }  
        
        return $link;
    }
    
    public static function isSEOLink() {
        return !link::isTarget() || (link::getVar('seo', false) != false);
    }
    
    public static function isTarget() {
        return link::getVar('target', false) != false;
    }
}
?>