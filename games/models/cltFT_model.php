<?

    include_once(dirname(__FILE__).'/clt_model/config.php');
    include_once(INCLUDE_PATH.'/_edbu2.php');

    class cltFT extends g_model {

        public function uploadJPGTemplate($params) {
            
            GLOBAL $GLOBALS;

            $path       = CLT_TEMPLATES_PATH.$params[0];
            $url        = CLT_TEMPLATES_URL.$params[0];
            
            $jpg        = file_get_contents('php://input');
            
            if (file_exists($path)) {
                if ($params[1] == 1) unlink($path);
                else return array('result'=>'FILEEXISTS', 'file'=>$url);
            }
            $file = fopen($path, 'w+');
            fwrite($file, $jpg);
            fclose($file);
            return array('result'=>'OK', 'file'=>$url);
        }
        
        public function uploadJPGTemplateClodo($params) {
            
            GLOBAL $GLOBALS;
            
        
            include_once('/home/vmaya/games/admin/helpers/opencloud/Authentication.php');
            require_once('/home/clt_clodo_auth.ini');
            
            $auth = new CF_Authentication($clodo['username'], $clodo['apiKey'], null, $clodo['url']);
            if ($auth->authenticate()) {
                $conn = new CF_Connection($auth);
                $public_container = $conn->get_container("public");

                $path       = CLT_TEMPLATES_PATH.$params[0];
                
                $jpg        = file_get_contents('php://input');
                
                if (file_exists($path)) {
                    if ($params[1] == 1) unlink($path);
                    else return array('result'=>'FILEEXISTS');
                }
                $file = fopen($path, 'w+');
                fwrite($file, $jpg);
                fclose($file);
                
                $file = $public_container->create_object($params[0]);
                $file->load_from_filename($path);
                unlink($path);
                
                return array('result'=>'OK', 'file'=>"http://storage-27811-4.cs.clodoserver.ru/$params[0]");
            }
            return array('result'=>'error');
        }
        
        public function deleteTemplate($params) {
            $query = "DELETE FROM `clt_templates` WHERE `type`='{$params[0]}' AND `id`={$params[1]}";
            return array('result'=>DB::query($query));
        }
        
        public function updateTemplate($params) {
            if ($params[0]) {
                $result = DB::query("REPLACE INTO `clt_templates` (`id`, `type`, `group`, `ws`, `ears`, `corr`, `autor`) VALUES({$params[0]}, '{$params[1]}', {$params[2]}, {$params[3]}, '{$params[4]}', '{$params[5]}', {$params[6]})");
                return array('result'=>$result);
            } else {
                $result = DB::query("INSERT INTO `clt_templates` (`type`, `group`, `ws`, `ears`, `corr`, `autor`) VALUES('{$params[1]}', {$params[2]}, {$params[3]}, '{$params[4]}', '{$params[5]}', {$params[6]})");
                return array('result'=>$result, 'id'=>DB::lastID());
            }
        }

        public function getTemplates($params) {
            return DB::asArray('SELECT * FROM `clt_templates` WHERE `checked`=0');
        }
    }
?>