<?

include_once(CONTROLLERS_PATH.'sysController/config.php');

class sysController extends controller {
    
    public function sysSample() {
        GLOBAL $CTYPE;
        eval('$this->sysSample'.$CTYPE.'();');
    }

    public function sysSampleJson() {
        echo $this->getSampleFileJSON();
    }
    
    public function getSampleFileJSON() {
        return file_get_contents(MAINPATH.'/scalaxy/netstate.json'); 
    }
    
/*    
    public function sysSampleHtml() {
        GLOBAL $_SERVER;
        $myServer       = $_SERVER['HTTP_HOST'];
        $serversList    = array('62.76.176.102', 'oformi-foto.ru', 'vmaya.ru');
        $servers        = array();
        
        foreach ($serversList as $server) {
            if ($server == $myServer) $servers[$server] = $this->getSysData();
            else {
                $queryString = 'http://'.$server.'/games/admin.php?task=sys,sysSample&ctype=json';
                $jsonContent = json_decode(file_get_contents($queryString), true);
                $servers[$server] = $jsonContent;
            }
        }
        
        require($this->templatePath);
    }
*/    
    
    public function sysInfo() {
        GLOBAL $CTYPE;
        eval('$this->sysInfo'.$CTYPE.'();');
    }
    
    public function sysInfoJson() {
        echo json_encode($this->getSysData());
    }
    
    public function sysInfoData() {
        GLOBAL $_SERVER, $serversList;
        $myServer       = $_SERVER['HTTP_HOST'];
        $servers        = array();
        
        foreach ($serversList as $server) {
            if ($server == $myServer) $servers[$server] = $this->getSysData();
            else {
                $queryString = 'http://'.$server.'/games/admin.php?task=sys,sysInfo&ctype=json';
                $jsonContent = json_decode(file_get_contents($queryString), true);
                $servers[$server] = $jsonContent;
            }
        }
        
        return $servers;
    }
    
    public function sysInfoHtml() {
        GLOBAL $_SERVER;
        $servers        = $this->sysInfoData();
        require($this->templatePath);
    }
    
    public function getSysData() {
        return array(
            '/'=>array(
                'freeSpace'=>round(disk_free_space('/') / 1024 / 1024),
                'totalSpace'=>round(disk_total_space('/') / 1024 / 1024)
            ),'/dev'=>array(
                'freeSpace'=>round(disk_free_space('/dev') / 1024 / 1024),
                'totalSpace'=>round(disk_total_space('/dev') / 1024 / 1024)
            ),
            'loadavg'=>sys_getloadavg()
        );
    }
    
    public function logsViewer() {
        $paths = array(LOGPATH, '/var/log/');
        
        $n = 0;
        for ($i=0; $i<count($paths); $i++) {
            $path = $paths[$i];
            $dir = opendir($path);
            while ( $file = readdir ($dir)) {
                if (( $file != ".") && ($file != "..")) {
                    $filePath = $path.$file;
                    if (is_file($filePath)) {
                        $files[] = $filePath;
                        
                        $select = $this->request->getVar("select{$n}", false);
                        if ($select) {
                            $file = fopen($filePath, 'w+');
                            fwrite($file, '');
                            fclose($file);                    
                        }
                        $n++;
                        
                    }
                }
            }
        }
        
        if ($fileName = $this->request->getVar('file')) {
            $lines = file_get_contents($fileName);
        }
        require($this->templatePath);
    }
    
    public function codes() {
        require($this->templatePath);
    }
    
    public function tasks() {
        require($this->templatePath);
    }
    
    public function mysql() {
        if ($id = $this->request->getVar("id", 0)) {
            $killResult = DB::query('kill '.$id);
        }
        $list = DB::asArray('show processlist;');
        require($this->templatePath);        
    }  
}
?>