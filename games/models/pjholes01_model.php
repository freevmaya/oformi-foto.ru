<?
    define('BASE_PATH', '/home/oformi-foto.ru/');

    class pjholes01 extends g_model {
        protected function checkPath($tmplId) {
            $relativePath = 'holes/'.$tmplId;
            $tmplPath = BASE_PATH.$relativePath;
            if (!file_exists($tmplPath)) {
                mkdir($tmplPath);
                chmod($tmplPath, 0775);
            } 
            
            return $tmplPath;       
        }
        
        protected function toFile($fileName, $data) {
            if (file_exists($fileName)) unlink($fileName);
            $file = fopen($fileName, 'w+');
            fwrite($file, $data);
            fclose($file);
        }
        
        public function uploadHole($params) {
            $relativePath = 'holes/'.$params[0];
            $tmplPath = $this->checkPath($params[0]).'/';
            $file_name = $params[1].'.png';
            $this->toFile($tmplPath.$file_name, file_get_contents('php://input'));
            
            return array('file'=>BASE_PATH.$relativePath.'/'.$file_name, 'time'=>time());
        }
        
        public function uploadPreview($params) {
            $relativePath = 'preview120/';
            $tmplPath = BASE_PATH.$relativePath;
            $file_name = $params[0].'.jpg';
            if (file_exists($tmplPath.$file_name)) unlink($tmplPath.$file_name);
            $this->toFile($tmplPath.$file_name, file_get_contents('php://input'));
            
            return array('file'=>BASE_PATH.$relativePath.$file_name, 'time'=>time());
        }
        
        public function holesInfo($params) {
            $relativePath = 'holes/'.$params[0];
            $file_name = 'holes.js';
            $tmplPath = $this->checkPath($params[0]).'/';
            $this->toFile($tmplPath.$file_name, 'var info='.$params[1].';');
            return array('file'=>BASE_PATH.$relativePath.'/'.$file_name);
        }
    }
?>