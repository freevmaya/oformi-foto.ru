<?
    require_once(dirname(__FILE__).'/opencloud/Authentication.php');
    
    class clodoContainer {
        private static $clodoContaier;
        public static function initialize($clodo) {
            $auth = new CF_Authentication($clodo['username'], $clodo['apiKey'], null, $clodo['url']);
            if ($auth->authenticate()) {
                $conn = new CF_Connection($auth);
                clodoContainer::$clodoContaier = $conn->get_container("public");
            }
        }
         
        public static function copy($sourceFilePath, $fileName, $destPath='') {
            $file = clodoContainer::$clodoContaier->create_object("{$destPath}{$fileName}");
            $file->load_from_filename($sourceFilePath);
        }
    }
?>