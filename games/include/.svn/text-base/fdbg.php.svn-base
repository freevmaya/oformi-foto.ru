<?

$FDBGLogFile = LOGPATH.'fdbg.log';

class fdbg {
    public static function trace($str, $topCalled=1) {
        GLOBAL $FDBGLogFile;
        
		$stack = fdbg::GetStack();
        if (!is_string($str)) $str = print_r($str, true);
//		$notifyStr = 'TRACE IN FILE: '.$stack[$topCalled]['file'].', line: '.$stack[$topCalled]['line'].($str?("\n".$str):$str);
                                                                                                                                
        $str = str_replace("'", '`', $str);
		$notifyStr = "array('time'=>'".date('d.m.y H.i')."', 'file'=>'{$stack[$topCalled]['file']}', 'line'=>'{$stack[$topCalled]['line']}', 'message'=>'{$str}')";

        if (file_exists($FDBGLogFile)) {
            $notifyStr = ",\n".$notifyStr;
            $line = filesize($FDBGLogFile);
        } else $line = 0;
        $fd = fopen($FDBGLogFile, 'a+');
        fwrite($fd, $notifyStr);
        fclose($fd);
        return $line;
    }
    
    public static function time() { 
        list($usec, $sec) = explode(" ", microtime()); 
        return ((float)$usec + (float)$sec); 
    }
    
    public function callStackItem($depth=1) {
		$stack = fdbg::GetStack();
		return $stack[$depth + 1];
    }
    
	public static function GetStack() {
        $stack = debug_backtrace();
        foreach ($stack as $key=>$val) {
            if (!isset($stack[$key]['file'])) unset($stack[$key]);	// удаляем пустые записи
        }
        return $stack;
	}
}

function trace($value, $to='file', $callDepth=2) {
    switch ($to) {
        case 'document': echo '<pre>';
                        $line = fdbg::callStackItem($callDepth);
                        echo 'trace in file: '.$line['file'].', line: '.$line['line']."\n";
                        print_r($value); 
                        echo '</pre>';
                        break;
        case 'file': fdbg::trace($value, $callDepth);
                    break;
    }
}
?>