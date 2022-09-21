<?
/*
	Автор: Фролов Вадим, VMAYA
	
	Класс: xml_stack
		Класс парсит XML документ, содает на основе анализа ассоциативный массив
		
	Класс: arrayToXML	
		Метод value_xml - возвращает строку в формате XML на основе переданного массива
		
	На основе этих классов работают функции:
	
	xml_array	- Конвертирует XML строку, в ассоциативный массив данных
	array_xml	- Конвертирует ассоциативный массив в строку XML
*/

class xml_stack {
	var $level;
	var $cur_tags;
	var $value;
	var $cur_list;
	var $prev_level;
	var $cur_name;

	function xml_stack() {
		$this->level = 0;
		$this->value = array();
		$this->prev_level = array();
		$this->cur_list = &$this->value;
	}

	function startTag($parser, $name, $attrs) {
		GLOBAL $xml_stack;
			
		$xml_stack->prev_level[$xml_stack->level] = &$xml_stack->cur_list;
		$xml_stack->level++;
			
		foreach ($attrs as $key=>$value) $attrs[$key] = iconv("UTF-8//TRANSLIT", "Windows-1251", $value);

		if (isset($xml_stack->cur_list[$name])) {
			if (!isset($xml_stack->cur_list[$name][0])) $xml_stack->cur_list[$name] = array($xml_stack->cur_list[$name]);
			array_push($xml_stack->cur_list[$name], $attrs);
			$xml_stack->cur_list = &$xml_stack->cur_list[$name][count($xml_stack->cur_list[$name]) - 1];
		} else {
			$xml_stack->cur_list[$name] = $attrs;
			$xml_stack->cur_list = &$xml_stack->cur_list[$name];
		}
		$xml_stack->cur_name = $name;
	}

	function cdata($parser, $cdata) {
		GLOBAL $xml_stack;
		if (trim($cdata) > '') {
//			if ($xml_stack->cur_name == 'Value') echo $cdata.'<br>';
//			$cdata = iconv("UTF-8//TRANSLIT", "Windows-1251", $cdata);
			if (isset($xml_stack->cur_list['cdata'])) {
				if (!is_array($xml_stack->cur_list['cdata'])) $xml_stack->cur_list['cdata'] = array($xml_stack->cur_list['cdata']);
				array_push($xml_stack->cur_list['cdata'], $cdata);
			} else $xml_stack->cur_list['cdata'] = $cdata;
		}
	}

	function endTag($parser, $name) {
		GLOBAL $xml_stack;
		$xml_stack->level--;
		$xml_stack->cur_list = &$xml_stack->prev_level[$xml_stack->level];
	}

}
	
//********************************

class arrayToXML {
	var $empty_no_save;
	
	function __construct($nosave_empty=true) {
		$this->empty_no_save = $nosave_empty;
	}
	
	private function string_convert($value) {
		$convert = array(array('char'=>'«', 'tochar'=>'"'), array('char'=>'»', 'tochar'=>'"'));
		foreach ($convert as $cnv) $value = str_replace($cnv['char'], $cnv['tochar'], $value);
		return $value;
	}

	private function parse_xml_item($value, $key, $level='') {
		$result = '';
		if (!is_array($value)) return '';

		$cdata = "\n";
		$nodes_str = $level.'<'.$key;
		$attr = '';
		foreach ($value as $lkey=>$lval) {
			if (is_string($lval) && $lval) {
				$t_val = $this->string_convert(trim($lval));
				if ($lkey == 'cdata') {
					$cdata = "<![CDATA[$t_val]]>";
				} else {
					if (strpos($t_val, '"') !== false) $sp = "'";
					else $sp = '"';
					if ($t_val) $attr.=' '.$lkey.'='.$sp.$t_val.$sp;
				}
			} else {
				if ($lkey == 'cdata') {
					$count = count($lval);
					if ($count > 0) {
						$cdata = '';
						for ($i=0; $i<$count; $i++) $cdata .= '<![CDATA['.trim($lval[$i]).']]>';
					}
				}
			}
		}

		if ($attr) $nodes_str .= $attr;
		$nodes_str .= ">".$cdata;
		$child_values = $this->value_xml_tree($value, $level.chr(9));
		$nodes_str .= $child_values;
		if (!trim($cdata)) $nodes_str .= $level;
		$nodes_str .= "</$key>\n";

		if ($this->empty_no_save) {
			if ($attr || $child_values || trim($cdata)) $result .= $nodes_str;
		} else	$result .= $nodes_str;
		return $result;
	}

	private function value_xml_tree($values, $level='') {
		$result = '';
		foreach ($values as $key=>$value) {
			if (isset($value[0]) && !is_string($value)) {
				foreach ($value as $l_key=>$l_value) $result .= $this->parse_xml_item($l_value, $key, $level);
			} else $result .= $this->parse_xml_item($value, $key, $level);
		}
		return $result;
	}

	public function value_xml($array) {
		return stripcslashes($this->value_xml_tree($array));
	}
}	 

function xmp_parser_init() {
	GLOBAL $xml_stack;
	$xml_stack = new xml_stack();
	$xml_parser = xml_parser_create();
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
	xml_set_element_handler($xml_parser, array($xml_stack, 'startTag'), array($xml_stack, 'endTag'));
	xml_set_character_data_handler($xml_parser, array($xml_stack, 'cdata'));
	return $xml_parser;
}

function xml_to_array($xml_data, &$error_str) {
	GLOBAL $xml_stack;
	$xml_parser = xmp_parser_init();
	
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($xml_parser, $xml_data, $values, $tags);
    $error=xml_get_error_code($xml_parser);
    if($error)
    {
        $column=xml_get_current_column_number($xml_parser);
        $line=xml_get_current_line_number($xml_parser);
        $byte=xml_get_current_byte_index($xml_parser);
        $error_str = xml_error_string($error).' at line: '.$line.', column: '.$column.', byte: '.$byte."\n";
    }
    
	$xmldata = xml_parse($xml_parser, $xml_data);
	xml_parser_free($xml_parser);
	return $xml_stack->value;
}
	
function xml_array($xml_file, &$error_str) {
	if (file_exists($xml_file)) {
		return xml_to_array(file_get_contents($xml_file), $error_str);
	} else return null;
}

function array_XMLAttr($arr, $level='') {
	$result = '';
	foreach ($arr as $key=>$val) {
		if (!is_array($val)) {
			if (strpos($val, '"') !== false) $tmp = "'%s'";
			else $tmp = '"%s"';
			if ($result > '') $result .= ' ';
			$val = iconv("Windows-1251", "UTF-8//TRANSLIT", $val);
			$result .= $key.'='.sprintf($tmp, $val);
		} 
	}
	return $result;
}

function array_XMLList($arr, $nodeName='node', $pref="\t") {
	$result = '';
	foreach ($arr as $key=>$val) {
		$result .= "\n$pref<$nodeName ".array_XMLAttr($val)."/>"; 
	}
	return $result;
}

?>