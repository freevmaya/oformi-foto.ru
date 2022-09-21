<?
class util {
    public static function isMyDomain() {
        GLOBAL $_SERVER;
        if (!($ref = @$_SERVER['HTTP_REFERER'])) return true;
        
        $domains = array('vmaya.ru', 'oformi-foto.ru', 'fotoprivet.com');
         
        $result = false;
        foreach ($domains as $domain) {
            $result = $result || (strpos($ref, $domain) !== false);  
        }
        return $result;
    }
    
    public static function imageReize($source, $resize) {
        
        $size  = array(imagesx($source), imagesy($source));
        $scale = max($resize[0]/$size[0], $resize[1]/$size[1]);
        
        $dest   = imagecreatetruecolor($resize[0], $resize[1]); 
        $x = ($resize[0] - $size[0] * $scale) / 2;
        $y = ($resize[1] - $size[1] * $scale) / 2;
        imagecopyresampled($dest, $source, $x, $y, 0, 0, $size[0] * $scale, $size[1] * $scale, $size[0], $size[1]);
        return $dest;
    }
    
    public static function imageResponse($fileName, $resize=null, $fileParamName='image', $compress=80) {
        GLOBAL $_FILES;
        if (isset($_FILES[$fileParamName]) && $_FILES[$fileParamName]['tmp_name']) {
            $tmpFileName = 'tmp.jpg';
            if ($resize) {
                move_uploaded_file($_FILES[$fileParamName]['tmp_name'], $tmpFileName);
                imagejpeg(util::imageReize(imagecreatefromjpeg($tmpFileName), $resize), $fileName, $compress);
                unlink($tmpFileName);
            } else move_uploaded_file($_FILES[$fileParamName]['tmp_name'], $fileName);
            return true;
        }    
    }
}
?>