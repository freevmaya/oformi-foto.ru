<?php

class App {
    var $charset;
    function __construct($charset=MAINCHARSET) {
        $this->charset = $charset;
    }
    
    function iconv($var, $from, $to) {
        if (is_array($var)) {
            $new = array();
            foreach ($var as $key => $val)
                $new[App::iconv($key, $from, $to)] = App::iconv($val, $from, $to);
            $var = $new;
        } else if (is_string($var)) $var = iconv($from, $to, $var);

        return $var;
    }
    
    function json_decode($json_str) {
        if ($is_conv = strtolower($this->charset) != 'utf-8') 
            $json_str = $this->iconv($json_str, $this->charset, 'utf-8');
        $result = json_decode($json_str, true);
        if ($is_conv) return $this->iconv($result, 'utf-8', $this->charset);
        else return $result;
    }
    
    public function pj($value, $data_charset) {
        if (!$value) return 0;
        else /*if (is_numeric($value) && (count(strval($value)) < 12)) return $value;
        else*/ if (is_string($value)) {
            $value = str_replace('"', "'", str_replace("\r", "\n", $value));//ereg_replace("[\n\r]+", '\n', $value));
            if (strtolower($data_charset) == strtolower($this->charset))
                return "\"$value\"";
            else return '"'.iconv ($data_charset, $this->charset, $value).'"';
        } else return '"'.print_r($value, true).'"';
    }
    
    private function obj_json($arr, $data_charset=MAINCHARSET) {
		$result = '';
		if ($arr)
    		foreach ($arr as $key=>$value) {
    			if ($result) $result .= ",";
    			$result .= $this->pj($key, $data_charset).':';
    			if (is_array($value)) $result .= App::arr_to_json($value, $data_charset);
    			else $result .= $this->pj($value, $data_charset);
    		}
		$result = '{'.$result.'}';
		return $result;
    }
    
    private function arr_json($arr, $data_charset=MAINCHARSET) {
		$result = '';
		for ($i=0; $i<count($arr); $i++) {
			if ($result) $result .= ",";
			if (is_array($arr[$i])) $result .= App::arr_to_json($arr[$i], $data_charset);
			else $result .= $this->pj($arr[$i], $data_charset);
		}
		if ($result) $result = '['.$result.']';
		else $result = '[]';
		return $result;
    }
    
	public function arr_to_json($arr, $data_charset=MAINCHARSET) {
        if (is_array($arr) && ((empty($arr) || isset($arr[0])))) $result = App::arr_json($arr, $data_charset);
        else $result = App::obj_json($arr, $data_charset);
        
        return $result;
	}
	
	public function json_to_arr($json_str) {
	   
    }
	
	public function errorResult($errorStr) {
		return "{\"info\":\"".mainURL."\", \"error\":\"$errorStr\"}";
	}
	
	public function getTemplate($modelName, $data_charset=MAINCHARSET) {
		$template_file = TEMPLATE_PATH.$modelName.'.tmpl';
		if (file_exists($template_file)) {
			$template_content 	= file_get_contents($template_file);
            if ($data_charset != $this->charset)
                $template_content = iconv ($data_charset, $this->charset, $template_content);
/*			$g_menu_script		= file_get_contents('scripts/g_menu.js');
			$template_content 	= "{'div':$template_content, 'script':function(parentElem, data) {".$g_menu_script."}}";*/
			return $template_content;
		} else return false;	
	}

    public static function decode($textCode) {
		$result	= '';
		$i      = 1;
		$bytes	= explode('x', $textCode);
		while ($i < count($bytes)) {
            $charCode = hexdec($bytes[$i]);
            $result .=  html_entity_decode('&#'.$charCode.';', ENT_NOQUOTES,'UTF-8');
			$i++;
		}
		return $result;
    }
}
?>
