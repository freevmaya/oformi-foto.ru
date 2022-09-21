<?
    require_once('Authentication.php');
    require_once('/home/clodo_auth.ini');
    
    $auth = new CF_Authentication($username, $apiKey, null, $url);
    if ($auth->authenticate()) {
        $conn = new CF_Connection($auth);
        $public_container = $conn->get_container("public");
        $all_objects = $public_container->get_objects();
        
        $fname = '601.jpg';
        $baby = $public_container->create_object($fname);
//        $baby->load_from_filename($fname);
/*        
        
        $size = (float) sprintf("%u", filesize($fname));
        
        $body = file_get_contents($fname);
        $baby->_guess_content_type('image/jpeg');
        $baby->write($body, $size);
*/        
        
        print_r($baby->public_uri());
    };    
?>