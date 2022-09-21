<?php
    include('/home/config.php');
    include(INCLUDE_PATH.'/util.php');
    if (util::isMyDomain() && isset($_GET['name'])) {
        $get_name = $_GET['name'];
        $ext = '.jpg';
        $baseFileName = str_replace('.', '', $get_name); 
        $fileName = '64/'.$baseFileName;
        $fullFileName = '200/'.$baseFileName;
        $url = '?name='.$get_name;
        
        $index = 1;
        while (file_exists($fileName.$ext)) {
            if (@$_POST['rewrite']) {
                unlink($fileName.$ext);
                break;
            }
            $fileName = $baseFileName.' '.$index;
            $index++;
        } 
        
        if (isset($_POST['imageURL']) && $_POST['imageURL']) {
            $completeLoaded = imagejpeg(util::imageReize(imagecreatefromjpeg($_POST['imageURL']), array(64, 64)), $fileName.$ext, 80);
            $fullSize = imagejpeg(util::imageReize(imagecreatefromjpeg($_POST['imageURL']), array(404, 200)), $fullFileName.$ext, 80);
        } else if (isset($_FILES['image'])) {
            $completeLoaded = util::imageResponse($fileName.$ext, array(64, 64));
            $fullSize = util::imageResponse($fullFileName.$ext, array(404, 200)); 
        } 
        include(dirname(__FILE__).'/himage.html');   
    } else echo 'referer no main domain'
?>