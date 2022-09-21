<?

define('FONTSIZE', 16);
class tlt {

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
    
    public static function resultProc($image, $params) {
    
        $back = imagecreatefrompng(DATA_PATH.'title_backs/'.($params[1]).'.png');
        $text = explode(chr(13), tlt::decode($params[3]));
        $fontFile = MAINPATH.'games/components/fonts/'.$params[4].'.ttf';
        
        $textWidth = 0;
        $textHeight = 0;
        $a_size = array();
        foreach ($text as $line) {
            $size = imageTTFBBox(FONTSIZE, 0, $fontFile, $line);
            
            if ($size[2] - $size[6] > $textWidth) $textWidth = $size[2] - $size[6];
            $textHeight += $size[3] - $size[7];

            $a_size[] = array($size[2] - $size[6], $size[3] - $size[7]);
        }
        
        $scaleX = $params[8] / $textWidth;
        $scaleY = $params[9] / $textHeight;
        $scale = (($scaleX < $scaleY)?$scaleX:$scaleY) * 0.85;
        
        $fontSize = floor(FONTSIZE * $scale);
        $ty = $params[7] + $fontSize + 9;
        
        foreach ($text as $i=>$line) {
            $tx = $params[6] + ($params[8] - $a_size[$i][0] * $scale) / 2;
            imageTTFText($back, $fontSize, 0, $tx, $ty, $params[10], $fontFile, $line);
            $ty += $a_size[$i][1] + $params[5];
        }
        
        
        $size = array(imagesx($back), imagesy($back));
        $r_size = array(imagesx($image), imagesy($image));
        $align  = isset($params[11])?$params[11]:'c';
        $valign  = isset($params[12])?$params[12]:'b';
        
        if ($align == 'c') $_x = ($r_size[0] - $size[0]) / 2;
        else if ($align == 'l') $_x = 0;
        else if ($align == 'r') $_x = $r_size[0] - $size[0];
        
        if ($valign == 't') $_y = 0;
        else $_y = $r_size[1] - $size[1];
        
        imagecopy($image, $back, $_x, $_y, 0, 0, $size[0], $size[1]);
    }   
}
?>