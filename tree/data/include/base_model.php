<?
    class base_model {
        private  $request;
        function __construct($request) {
            $this->request = $request;
        }
        
        protected function aconv($array, $fields, $sourceCharset, $descCharset) {
            foreach ($array as $key=>$item) {
                foreach ($fields as $field)
                    $array[$key][$field] = iconv($sourceCharset, $descCharset, $item[$field]); 
            }
            
            return $array;
        }
        
        protected function getVar($varName, $default) {
            return isset($this->request[$varName])?$this->request[$varName]:$default;
        }
        
        protected function json($values) {
            echo 'var result='.json_encode($values).';';
        }
        
        protected function asis($values) {
            echo json_encode($values);
        }
        
        private function XMLElements($values, $group='items') {
            $result = '';
            foreach ($values as $key=>$value) {
                $result .= "<$group";
                if (is_array($value)) {
                    if (isset($value[0]))
                        $result .= ">\n".$this->XMLElements($value)."</$key>\n";
                    else $result .= ' '.$this->XMLAttrs($value)."/>\n";
                } else $result .= ">\n$value</$key>\n"; 
            }
            
            return $result;
        }
        
        private function XMLAttrs($values) {
            $result = '';
            foreach ($values as $key=>$value) {
                $result .= $key.'="'.$value.'" '; 
            }
            
            return $result;
        }
        
        protected function xml($values) {
            $result = "<?xml version=\"1.0\" encoding=\"utf-8\" ?".">\n";
            $result .= "<body>\n";
            $result .= $this->XMLElements($values);
            $result .= "</body>\n";
            return $result;
        }

        public function result() {
            $method = $this->getVar('method', 'getList');
            $format = $this->getVar('format', 'json');
             
            return $this->$format($this->$method());
        } 
    }
?>