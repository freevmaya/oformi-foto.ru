<?
define('DEFAULTWAITMLS', 2);

include_once(MAINPATH.'scalaxy/manager.php');
include_once(MAINPATH.'scalaxy/netstat.php');

class scalaxyController extends controller {

    public function state() {
        GLOBAL $CTYPE;
        eval('$this->state'.$CTYPE.'();');
    }
    
    public function stateHtml() {
        $result = $this->getState();
        require($this->templatePath);
    }
    
    public function stateJson() {
        echo json_encode($this->getState());
    }
        
    protected function getState() {
        $result = array();
        $state  = new netstat();
        $wait   = $this->request->getVar('waitmls', DEFAULTWAITMLS); 
        for ($i=0; $i<2; $i++) {
            $result['speed'] = $state->sampleSpeed('eth1');
            if ($i==0) sleep($wait);
        }
        $mem    = $state->detailMemory();
        $result['mem'] = $mem['memory'];
        return $result;
    }
    
    public function listState() {
        GLOBAL $_SERVER;
        $myServer       = $_SERVER['HTTP_HOST'];
        $serversList    = array('188.127.228.242', 'oformi-foto.ru', '188.127.227.87', '188.127.227.55');
        $servers        = array();
        $waitmls        = $this->request->getVar('waitmls', DEFAULTWAITMLS); 
        
        foreach ($serversList as $server) {
            if ($server == $myServer) $servers[$server] = $this->getState();
            else {
                $queryString = 'http://'.$server.'/games/admin.php?task=scalaxy,state&ctype=json&waitmls='.$waitmls;
                $jsonContent = json_decode(file_get_contents($queryString), true);
                $servers[$server] = $jsonContent;
            }
        }
        require($this->templatePath);
    } 
}
?>