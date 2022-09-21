<?
    define('IMAGEPATH', dirname(__FILE__).'/');
    
    $ref = parse_url(@$_SERVER['HTTP_REFERER']);
    if ($ref['host'] == $_SERVER['HTTP_HOST']) {
        $path = (isset($_POST['path']) && $_POST['path'])?$_POST['path']:'users';
        $fullPath = IMAGEPATH.$path.'/';
        if ((fileperms($fullPath) & 0070) == 0070) {
            $filePath = $fullPath.$_POST['fileName'].'.jpg';
            
            $file = fopen($filePath, 'w+');
            $image = str_replace(" ", "+", $_POST['image']);
            $image = substr($image, strpos($image, ","));
            echo 'result="'.(fwrite($file, base64_decode($image))).'"';
            fclose($file);
        } else echo 'result="Нет разрешения для папки '.$fullPath.'"';
    } else echo 'result="Не мой домен"';
?>