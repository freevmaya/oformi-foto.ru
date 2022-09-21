<?

include_once(CONTROLLERS_PATH.'sysController/config.php');

class svnController extends controller {
    public function treeCommit() {
        echo exec('/home/vmaya/tree/commit');
    }  

    public function treeUpdate() {
        echo exec('/home/vmaya/tree/update');
    }  
}
?>