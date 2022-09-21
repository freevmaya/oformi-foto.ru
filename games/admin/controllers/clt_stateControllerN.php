<?

define('COLLAGEPATH', DATA_PATH.'clothing/game/');
define('COLLAGEPREVIEWPATH', DATA_PATH.'clothing/game/preview/');

define('COLLAGEURL', DATA_URL.'clothing/game/');
define('COLLAGEPREVIEWURL', DATA_URL.'clothing/game/preview/');
define('DEFCOUNT', 20);      
//define('TBPREFIX', 'clt_ok');

include_once(INCLUDE_PATH.'/_edbu2.php');
//include_once(CONTROLLERS_PATH.'/stateController/config.php');

class clt_stateController extends controller {
    private $tbprefix;
    
    function __construct($a_tbprefix) {
        $this->tbprefix = $a_tbprefix;
    }
    
    public function victory() {                  
        $limit = $this->svar('limit', DEFCOUNT);
        if ($sDate = $this->request->getVar('sDate', false)) {
            $list = DB::asArray("SELECT *
                                FROM `".$this->tbprefix."game` g 
                                WHERE `time`< '{$sDate}' AND `rate`>0 ORDER BY `time` DESC LIMIT 0, $limit");
        }
        require($this->templatePath);
    }
    
    public function test() {
        $uid        = '8062938299454250872';
        $list       = DB::asArray("SELECT * FROM `".$this->tbprefix."game` WHERE `uid`={$uid} AND noVictory=0 AND `time`<=NOW()-INTERVAL 1 DAY");
        $nullIds    = '';
        $noVic      = '';
        
        foreach ($list as $key=>$item) {
            if ($item['rate'] == 0) { // ќтсеиваем все с нулевым рейтингом
                $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
            } else {
                $endTime = date('Y-m-d H:i:s', strtotime($item['time'].'+1 day'));
                $query = "SELECT *,
                            (SELECT SUM(votes) FROM ".$this->tbprefix."votes WHERE game_id=g.id AND `time`<='{$endTime}') AS votes 
                        FROM `".$this->tbprefix."game` g 
                        WHERE g.`id`<>{$item['id']} AND g.`time`>='{$item['time']}' AND g.`time`<='$endTime' 
                        ORDER BY votes DESC
                        LIMIT 0,1";
                $vic = DB::line($query);
                if ($vic['votes'] > $item['rate']) { // Ёто не победа
                    $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                } else {
                    DB::query("UPDATE `".$this->tbprefix."game` SET noVictory=2 WHERE id={$item['id']}");
                    DB::query("INSERT `".$this->tbprefix."gameVictory` VALUES ({$item['id']}, $uid, '{$item['time']}', {$item['rate']})");
                }
            }
        }
        
        if ($nullIds) DB::query("UPDATE `".$this->tbprefix."game` SET noVictory=1 WHERE $nullIds");
    }
    
    public function victoryTest() {
        if ($startDate = $this->request->getVar('startDate', false)) {
            $limit = $this->svar('limit', DEFCOUNT);
            $endDate = $this->request->getVar('endDate', false);
            $collages = DB::asArray("SELECT *, (SELECT SUM(votes) FROM ".$this->tbprefix."votes WHERE game_id=g.id AND `time`<='{$endDate}') AS votes 
                                FROM `".$this->tbprefix."game` g
                                WHERE `time`>='{$startDate}' AND  `time`<='{$endDate}' ORDER BY `rate` DESC LIMIT 0, $limit");
        } else if ($id = $this->request->getVar('id', false)) {
            $item = DB::line("SELECT *, (SELECT SUM(votes) FROM ".$this->tbprefix."votes WHERE game_id=g.id AND `time`<=g.time + INTERVAL 1 DAY) AS votes  FROM `".$this->tbprefix."game` g WHERE g.`id`={$id}");
            $endTime = date('Y-m-d H:i:s', strtotime($item['time'].' +1 day'));
            $query = "SELECT *,
                            (SELECT SUM(votes) FROM ".$this->tbprefix."votes WHERE game_id=g.id AND `time`<='{$endTime}') AS votes 
                        FROM `".$this->tbprefix."game` g 
                        WHERE g.`id`<>{$item['id']} AND g.`time`>='{$item['time']}' AND g.`time`<='$endTime' 
                        ORDER BY votes DESC
                        LIMIT 0, 4";
            $collages = DB::asArray($query);
        }
        require($this->templatePath);
    }
    
    public function delete() {
        if ($sDate = $this->request->getVar('sDate', false)) {
            $list = $this->prepareDelete(
                        $this->request->getVar('sDate'),
                        $this->request->getVar('isNull'),
                        $this->request->getVar('noVictory')
                    );
            $this->removeCollages($list);
        }
        require($this->templatePath);
    }
    
    public function prepareDelete($endDate, $isNullOnly, $noVictory) {
        $lastDate = date('Y-m-d H:i:s', strtotime('-1 YEAR'));
        //¬ключаем только коллажи с конкурса, не победители, или коллажи пользовател€ если он незаходил год
        $where = "((`time`<='{$endDate}') AND g.`group`=0 AND g.`noVictory`<>2) OR (u.visitDate <= '{$lastDate}' AND g.`noVictory`<>2)";
        if ($noVictory) $where .= " AND noVictory=1";
        $query = "SELECT g.id, (SELECT SUM(votes) FROM ".$this->tbprefix."votes WHERE game_id=g.id AND `time`<=g.`time` + INTERVAL 1 DAY) AS votes, u.uid 
                            FROM `".$this->tbprefix."game` g INNER JOIN clt_okusers u ON g.uid = u.uid  
                            WHERE $where";
        $list = DB::asArray($query);
        $ids = array();
        foreach ($list as $item) {
            if (!$isNullOnly || ($item['votes']==0)) $ids[] = $item['id'];
        }
        return $ids;
    }
    
    public function removeCollages($ids, $subDir='') {
        if (count($ids)) {
            $path = DATA_PATH.'clothing/'.($subDir?($subDir.'/'):'');
            foreach ($ids as $id) {
                $filePath = $path.'game/'.$id.'.jpg';
                if (file_exists($filePath)) unlink($filePath);
                
                $filePath = $path.'game/preview/'.$id.'.jpg';
                if (file_exists($filePath)) unlink($filePath);
            }
            
            DB::query("DELETE FROM `".$this->tbprefix."game` WHERE id=%s", implode(' OR id=', $ids)); 
            DB::query("DELETE FROM `".$this->tbprefix."votes` WHERE game_id=%s", implode(' OR game_id=', $ids));
            DB::query('DELETE FROM `'.$this->tbprefix.'comments` WHERE (content_id='.implode(' OR content_id=', $ids).') AND content_type=1'); 
            DB::query("OPTIMIZE TABLE `".$this->tbprefix."game`");
            DB::query("OPTIMIZE TABLE `".$this->tbprefix."votes`");
            DB::query("OPTIMIZE TABLE `".$this->tbprefix."comments`");
        }
        
        return $ids;
    }
    
    public function ripper() {
        require($this->templatePath);
    }
    
    public function notification() {
        GLOBAL $_POST;
        include_once('/home/secrects.inc');    
        include_once(INCLUDE_PATH.'/OKServer.php');
        
        if ($this->request->getVar('text')) {
            //$date = date('Y.m.d H:i', strtotime('-2 MINUTE'));
            $date = $this->request->getVar('expires');
            if (!$date) $date = date('Y.m.d H:i');
            
            $params = array();
            foreach ($_POST as $key=>$value)
                if ($value) {
//                    if ($key == 'text') $value = iconv('Windows-1252', 'UTF-8', $value);
                    $params[$key] = $value;
                }
                
            $params['expires'] = $date;
            //print_r($params);
            $result = OKServer::request($this->request->getVar('appKey'), 'notifications/sendMass', $params, false);
            print_r($result);
        }
        require($this->templatePath);
    }
    
    public function banRequest() {
        if ($select = $this->request->getVar('items')) {
            $banType = $this->request->getVar('banType');
            $banDate = $this->request->getVar('banDate');
            
            $where      = '';
            $whereCID   = '';
            $whereGID   = '';
            foreach ($select as $uid) {
                $content = explode('_', $uid);
                $where      .= ($where?' OR':'')." uid={$content[0]}";
                $whereCID   .= ($whereCID?' OR':'')." comment_id={$content[1]}";
                if ($content[2]) {
                    $whereGID   .= ($whereGID?' OR':'')." id={$content[2]}";
                    if (($banType == -1) || ($banType == 2) || ($banType == 3)) {
                        unlink(COLLAGEPATH.$content[2].'.jpg');
                        unlink(COLLAGEPREVIEWPATH.$content[2].'.jpg');
                    }
                }
            }
            
            if ($banType == -1) {
                $query = "DELETE FROM `".$this->tbprefix."comments` WHERE $whereCID";
                DB::query($query);
                
                $query = "UPDATE `".$this->tbprefix."users` SET `banType`=0 WHERE $where";
                DB::query($query);
            } else {
                $query = "UPDATE `".$this->tbprefix."users` SET `banType`=$banType, `banDate`='$banDate' WHERE $where";
                DB::query($query);
            }
            
            if ($whereGID && (($banType == -1) || ($banType == 2) || ($banType == 3))) {
                $query = "DELETE FROM `".$this->tbprefix."game` WHERE $whereGID";
                DB::query($query);
            }
            //echo $query;
        }
        $banTypeView1 = $this->request->getVar('banTypeView1', 1);
        $banTypeView2 = @$this->request->getVar('banTypeView2', false);
        
        $list = DB::asArray("SELECT u.*, c.comment as message, u.banContent as comment_id, g.id as game_id
                            FROM `".$this->tbprefix."users` u LEFT JOIN `".$this->tbprefix."comments` c ON c.comment_id=u.banContent LEFT JOIN `".$this->tbprefix."game` g ON g.id=u.banContent
                            WHERE u.banType=$banTypeView1".($banTypeView2?" OR u.banType=4":''));
        require($this->templatePath);
    }  
}
?>