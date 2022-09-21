<?
class defaultController extends controller {
    public function view() {
        include_once(TEMPLATES_PATH.(ss::$isPhone?'mobile/':'').'default.html');
    }
}    
?>