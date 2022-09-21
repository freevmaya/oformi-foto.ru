<?
    define('UPLOADPATH', '/home/vmaya/html/clothing/images/share/');
    
    class dataModel extends base_model {
        protected function getList() {
            return array();
        }
        
        protected function aconv($array, $fields, $sourceCharset, $descCharset) {
            foreach ($array as $key=>$item) {
                foreach ($fields as $field)
                    $array[$key][$field] = iconv($sourceCharset, $descCharset, $item[$field]); 
            }
            
            return $array;
        }
        
        protected function upload() {
            GLOBAL $GLOBALS;
            $result = array();
            $file_name  = $this->getVar('id', null).'.jpg';
            
            if ($jpg =  $GLOBALS["HTTP_RAW_POST_DATA"]) {
            
                if (file_exists(UPLOADPATH.$file_name)) unlink(UPLOADPATH.$file_name);
                $file = fopen(UPLOADPATH.$file_name, 'w+');
                fwrite($file, $jpg);
                fclose($file);
                
                $result = array('file'=>$file_name, 'time'=>time());
            }
            return $result;
        } 
    }
?>