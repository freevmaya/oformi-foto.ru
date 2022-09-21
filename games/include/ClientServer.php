<?

define('TIMEWAIT', 60 * 1000 * 2); // 2 минут
define('TIMESLEEP', 100);

class ClientServer {
    private $socket;
    private $writeBuffer;
    
    function __construct($a_socket) {
        $this->socket       = $a_socket;
        $this->writeBuffer  = '';
    }
    
    public function begin() {
        $i=0;
        $buffer = '';
        echo "begin socket {$this->socket}\n";
        while ($i < TIMEWAIT) {
            usleep(TIMESLEEP);
            $lbuf = '';
            $read   = array($this->socket);
            $write  = array($this->socket);
            $except = array($this->socket);
            $n = socket_select($read, $write, $except, 10);
            if (count($read) > 0) {
                if (socket_recv($this->socket, $lbuf, 1024, 8) === false) {
                    echo "socket_recv() failed; reason: " . socket_strerror(socket_last_error($this->socket)) . "\n";
                    break;
                }
                $buffer .= $lbuf;
                $i=0;
            } else if ($buffer) {
                $parts  = explode(';', $buffer);
                $result = true;
                foreach ($parts as $part) {
                    if ($part{0} == '{') { 
                        if (!$this->parseJSON($part)) break;
                    } else {
                        $result = $this->callCommand($part, null) && $result;
                    }
                }
                $buffer = '';
                if (!$result) break;
                $i=0;
            };
            
            if ((count($write) > 0) && ($this->writeBuffer)) {
                socket_write($this->socket, $this->writeBuffer);
                $this->writeBuffer = '';
                $i=0;
            }
            
            $this->doFrame();
            $i += TIMESLEEP;
        }
        
        if ($i >= TIMEWAIT)
            echo "timeout\n";
        else echo "complete socket {$this->socket}\n";
    }
    
    protected function doFrame() {
    }
    
    protected function callCommand($command, $params) {
        $nameMethod = 'cmd_'.$command;
        echo 'method: '.$nameMethod."\n";
        if (method_exists($this, $nameMethod))
            return $this->$nameMethod($params);
        else echo "method $nameMethod not found";
        return false;
    }
    
    protected function sendObject($a_object) {
        $this->writeBuffer .= ($this->writeBuffer?';':'').json_encode($a_object);
    }
    
    protected function parseJSON($strJSON) {
        $result = false;
        $data = json_decode($strJSON);
        if (isset($data->method)) {
            $result = $this->callCommand($data->method, $data->params);
            if (isset($data->qid) && $result) { // Если это был запрос тогда возвращаем с идентификатором
                $this->sendObject(array('qid'=>$data->qid, 'result'=>$result));
            }
        }
        return $result;
    }
    
    protected function cmd_shutdown($params=null) {
        return false;
    }
    
    protected function cmd_init($params=null) {
        $this->sendObject(array(
            'method'=>'initResult',
            'params'=>$params
        ));
        return true;
    }
}

?>