<?php
    class Request {
        var $values;
        function __construct() {
            GLOBAL $_GET, $_POST;
            $this->values = array_merge($_GET, $_POST);
        }
        
        public function getVar($varName, $default='') {
            if (isset($this->values[$varName])) return $this->values[$varName];
            else return $default;
        }
        
        public static function genSig($values, $secrets) {
            $query_str  = '';
            $values     = array_merge($values);
            unset($values['sig']);  // Выкидываем сигнатуру
            /* Выкидываем все попбочные поля */
            if (isset($values['Filename'])) unset($values['Filename']);
            if (isset($values['Filedata'])) unset($values['Filedata']);
            if (isset($values['Upload'])) unset($values['Upload']);
            
//			trace($values);
            ksort($values);         // Сортируем
            foreach ($values as $key=>$value) $query_str .= $key.'='.$value;
            $query_str .= $secrets[$values['app_id']];
            return md5($query_str);
        }
        
        public function toString() {
            $result = '';
            foreach ($this->values as $key=>$value)
                $result .= ($result?'&':'').($key.'='.$value);
            return $result;
        }
    }
?>