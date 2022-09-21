<?
    class baseApiShell {
        function __construct($params) {
        }

        public function userGetInfo($params) {
        }

        public function photosGetAlbums($params) {
        }  

        public function photosGet($params) {
        }

        public function getVar($varName, $default=null) {
            global $_SESSION;
            $className = get_class($this);
            if (!isset($_SESSION[$className][$varName])) return $default;
            else return $_SESSION[$className][$varName];
        }
        
        public function setVar($varName, $value) {
            global $_SESSION;
            $className = get_class($this);
            $_SESSION[$className][$varName] = $value;
        }        
    }
?>