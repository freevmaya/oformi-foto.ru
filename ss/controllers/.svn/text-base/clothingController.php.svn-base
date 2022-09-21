<?

define('ITEMSPERPAGE', 7 * 4);
include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(SSPATH.'controllers/catalog/list.php');

class clothingController extends controller {
    function __construct($a_request) {
        parent::__construct($a_request);
    }
    
    function view() {
        $groupIds = array(1, 2); 
        $menu = array(
            array('name'=>'Одежда', 'part'=>1, 'id'=>40),
            array('name'=>'Прически', 'part'=>2, 'id'=>41)
        );
        require(TEMPLATES_PATH.'viewClothing.html');
    }
}
?>    