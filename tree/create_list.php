<?
    function getFiles($paths, $ext) {
        $files = array();
        $n = 0;
        $exts = explode(',', $ext);
        for ($i=0; $i<count($paths); $i++) {
            $path = 'theme/tree/images/'.$paths[$i];
            $dir = opendir($path);
            while ( $file = readdir ($dir)) {
                $inf = pathinfo($file);
                if (isset($inf['extension']) && in_array($inf['extension'], $exts)) {
                    $filePath = $path.$file;
                    if (is_file($filePath)) $files[] = $paths[$i].$file;
                }
            }
        }
        
        return $files;
    }
    
    
    $ilist = getFiles(array('buttons/', 
                            'frames/',
                            'libs/',
                            'tips/',
                            'vin/',
                            ''), 'png,jpg,gif');
                            
    $str = json_encode($ilist);
    file_put_contents('theme/js/ilist.json', $str);
    echo $str;
?>
    