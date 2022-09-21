<?

include_once(CONTROLLERS_PATH.'/gameBaseController.php');

class orderController extends gameBaseController {
    protected function sendNotify($orderID, $mailTo, $message) {
        $subject = 'Новый заказ на сайте oformi-foto.ru';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';            
        $headers[] = 'From: webmaster@oformi-foto.ru';
        $headers[] = 'Reply-To: webmaster@oformi-foto.ru';
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        
        $message = '<p><h1>У вас новый заказ на сайте!</h1>'.
        'Номер заказа: '.$orderID.
        '</p><p>'.$message.'</p>';
        
       // mail($mailTo, $subject, $message, implode("\r\n", $headers));        
    }
    
    public function view() {
        GLOBAL $locale, $_FILES;
        ss::$noadv = true;
        
        $user = ss::getUserAlternate();
        $img_field = 'image';
        
        if (($order_type = $this->getVar('order_type')) && 
            ($name = $this->getSafeVar('name')) &&
            ($email = $this->getSafeVar('email')) &&
            (isset($_FILES[$img_field]) && ($_FILES[$img_field]['size'] > 0))) {
            
            $fileTmp = $_FILES[$img_field]['tmp_name'];
            
            if (exif_imagetype($fileTmp) !== false) {
                $message = $this->getSafeVar('message');
                
                $pinfo = pathinfo($_FILES[$img_field]['name']);
                $ext = $pinfo['extension'];
                $query = "INSERT INTO of_orders (`uid`, `user_source`, `order_type`, `date`, `time`, `name`, `email`, `message`, `ext`) VALUES ".
                    "({$user['uid']}, '{$user['source']}', '{$order_type}', NOW(), NOW(), '{$name}', '{$email}', '{$message}', '{$ext}')";
                    
                if (DB::query($query)) {
                    $orderID = DB::lastID();        
                    $imageFile = $orderID.'.'.$ext;
                    $uploadfile = ORDERPATH.$imageFile;
                    if (@move_uploaded_file($fileTmp, $uploadfile)) {
                        $this->sendNotify($orderID, 'order@oformi-foto.ru', 
                            "Пользователь: {$name} ({$email}) {$message}<br><img src=\"".ORDERURL.$imageFile."\">"
                        );
                        require(TEMPLATES_PATH.'/order/'.ss::lang().'/success.html');
                        return;            
                    }
                }
            }
            require(TEMPLATES_PATH.'/order/'.ss::lang().'/fail.html');            
        } else {
            $user = ss::getUser();
            $dataView = '_'.ss::$task[2];
            if (method_exists($this, $dataView)) $this->$dataView();
            else require($this->templatePath);
        }
    }
    
    protected function _list() {
        GLOBAL $_POST;
        $state = $this->getVar('state', false); 
        if ($state && ($orders = @$_POST['orders'])) {
            $where = implode(' OR order_id=', $orders);
            $query = "UPDATE of_orders SET `state`='{$state}' WHERE order_id={$where}";
            DB::query($query);
        }
        require($this->templatePath);
    }
}

?>