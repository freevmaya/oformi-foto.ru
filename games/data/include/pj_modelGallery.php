<?
    include_once(dirname(__FILE__).'/base_model.php');
    
    $charset    = 'utf8';
    session_start();
    class dataModel extends base_model {
        
        protected function createGallery() {
            GLOBAL $_POST, $_SESSION;
            
            $result = 0;
            
            if (isset($_POST['list'])) {
                $_SESSION['list'] = json_decode($_POST['list']);
                $result = 1;
            }
            
            return array('result'=>$result);
        }
        
        protected function getBig() {
            GLOBAL $_SESSION;
            $list = $_SESSION['list'];
            include((dirname(__FILE__).'/gallery/big.pxml'));
        }
        
        protected function getThumbs() {
            GLOBAL $_SESSION;
            $list = $_SESSION['list'];
            //print_r($list);
            include((dirname(__FILE__).'/gallery/thumbs.pxml'));
        }      
    }
?>