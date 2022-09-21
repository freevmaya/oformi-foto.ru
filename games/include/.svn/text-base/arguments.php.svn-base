<?php
    class Arguments {
        var $values;
        function __construct() {
            GLOBAL $argv;
            
            $this->values = array();
            for ($i=1; $i<count($argv); $i++) {
                $a = explode('=', $argv[$i]);                
                if (count($a) > 1) 
                    $this->values[$a[0]] = $a[1]; 
            }
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
    }
?>