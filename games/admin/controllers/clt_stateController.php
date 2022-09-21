<?
    GLOBAL $charset, $dbname;
    $charset = 'utf8';
    $dbname = '_clothing';

define('COLLAGEPATH', DATA_PATH.'clothing/game/');
define('COLLAGEPREVIEWPATH', DATA_PATH.'clothing/game/preview/');

define('COLLAGEURL', DATA_URL.'clothing/game/');
define('COLLAGEPREVIEWURL', DATA_URL.'clothing/game/preview/');
define('DEFCOUNT', 20);      
define('TBPREFIX', 'clt_ok');
define('DATEFORMAT', 'Y.m.d');
define('OKAPIKEY', 'CBAOKEABABABABABA');

include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(INCLUDE_PATH.'/OKServer.php');
//include_once(CONTROLLERS_PATH.'/stateController/config.php');

class clt_stateController extends controller {
    public function victory() {                  
        $limit = $this->svar('limit', DEFCOUNT);
        if ($sDate = $this->request->getVar('sDate', false)) {
            $list = DB::asArray("SELECT *
                                FROM `".TBPREFIX."game` g 
                                WHERE `time`< '{$sDate}' AND `rate`>0 ORDER BY `time` DESC LIMIT 0, $limit");
        }
        require($this->templatePath);
    }
    
    public function test() {
        $uid        = '8062938299454250872';
        $list       = DB::asArray("SELECT * FROM `".TBPREFIX."game` WHERE `uid`={$uid} AND noVictory=0 AND `time`<=NOW()-INTERVAL 1 DAY");
        $nullIds    = '';
        $noVic      = '';
        
        foreach ($list as $key=>$item) {
            if ($item['rate'] == 0) { // Отсеиваем все с нулевым рейтингом
                $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
            } else {
                $endTime = date('Y-m-d H:i:s', strtotime($item['time'].'+1 day'));
                $query = "SELECT *,
                            (SELECT SUM(votes) FROM ".TBPREFIX."votes WHERE game_id=g.id AND `time`<='{$endTime}') AS votes 
                        FROM `".TBPREFIX."game` g 
                        WHERE g.`id`<>{$item['id']} AND g.`time`>='{$item['time']}' AND g.`time`<='$endTime' 
                        ORDER BY votes DESC
                        LIMIT 0,1";
                $vic = DB::line($query);
                if ($vic['votes'] > $item['rate']) { // Это не победа
                    $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                } else {
                    DB::query("UPDATE `".TBPREFIX."game` SET noVictory=2 WHERE id={$item['id']}");
                    DB::query("INSERT `".TBPREFIX."gameVictory` VALUES ({$item['id']}, $uid, '{$item['time']}', {$item['rate']})");
                }
            }
        }
        
        if ($nullIds) DB::query("UPDATE `".TBPREFIX."game` SET noVictory=1 WHERE $nullIds");
    }
    
    public function victoryTest() {
        $votesSum = "(IFNULL((SELECT SUM(votes) FROM ".TBPREFIX."votes WHERE game_id=g.id AND `time`<=g.time + INTERVAL 24 HOUR), 0) + IFNULL((SELECT SUM(votes) FROM ".
                    TBPREFIX."votes_other WHERE game_id=g.id AND `time`<=g.time + INTERVAL 24 HOUR), 0))";
        $startDate = $this->svar('startDate', date('Y-m-d H:i:s', strtotime('-24 HOUR')));
        $endDate = $this->svar('endDate', date('Y-m-d H:i:s'));
        
        
        
        if ($id = $this->request->getVar('id', false)) {
            $item = DB::line("SELECT *, (SELECT SUM(votes) FROM ".TBPREFIX."votes WHERE game_id=g.id AND `time`<=g.time + INTERVAL 24 HOUR) AS votes  FROM `".TBPREFIX."game` g WHERE g.`id`={$id}");
            $startDate = date('Y-m-d H:i:s', strtotime($item['time']));
            $endDate = date('Y-m-d H:i:s', strtotime($item['time'].' +24 HOUR'));
            $query = "SELECT *, $votesSum AS votes 
                        FROM `".TBPREFIX."game` g 
                        WHERE g.`id`<>{$item['id']} AND g.`time`>='{$item['time']}' AND g.`time`<='$endDate' 
                        ORDER BY votes DESC
                        LIMIT 0, 4";
            $collages = DB::asArray($query);
            
            if ($this->request->getVar('set_victory', false)) {
                $query1 = "UPDATE `".TBPREFIX."game` SET noVictory=2 WHERE `id`=$id"; 
                $query2 = "REPLACE `".TBPREFIX."gameVictory` VALUES (SELECT `g`.id, `g`.uid, `g`.time, $votesSum AS rate, g.`mlp` FROM `".TBPREFIX."game` g WHERE g.`id`=$id)";
                echo $query1.'<br>'.$query2;
                /* 
                DB::query($query1);
                DB::query($query2);
                */
            }
        } else if ($startDate && $endDate) {
            $limit = $this->svar('limit', DEFCOUNT);
            $collages = DB::asArray("SELECT *, $votesSum AS votes 
                                FROM `".TBPREFIX."game` g
                                WHERE `time`>='{$startDate}' AND  `time`<='{$endDate}' ORDER BY `votes` DESC LIMIT 0, $limit");
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
        $where = "(`time`<='{$endDate}') AND g.`group`=0 AND g.best=0";
        if ($noVictory) $where .= " AND noVictory=1"; 
        $list = DB::asArray("SELECT id, (SELECT SUM(votes) FROM ".TBPREFIX."votes WHERE game_id=g.id AND `time`<=g.`time` + INTERVAL 1 DAY) AS votes
                            FROM `".TBPREFIX."game` g 
                            WHERE $where");
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
                
                $filePath = $path.'game/share/'.$id.'.jpg';
                if (file_exists($filePath)) unlink($filePath);
            }
            
            DB::query("DELETE FROM `".TBPREFIX."game` WHERE id=%s", implode(' OR id=', $ids)); 
            DB::query("DELETE FROM `".TBPREFIX."votes` WHERE game_id=%s", implode(' OR game_id=', $ids));
            DB::query('DELETE FROM `'.TBPREFIX.'comments` WHERE (content_id='.implode(' OR content_id=', $ids).') AND content_type=1'); 
            DB::query("OPTIMIZE TABLE `".TBPREFIX."game`");
            DB::query("OPTIMIZE TABLE `".TBPREFIX."votes`");
            DB::query("OPTIMIZE TABLE `".TBPREFIX."comments`");
        }
        
        return $ids;
    }
    
    public function ripper() {
        require($this->templatePath);
    }
    
    private function binterval($startTime, $endTime) {
        return date(DATEFORMAT, strtotime($startTime)).'-'.date(DATEFORMAT, strtotime($endTime));
    }
    
    public function notification() {
        GLOBAL $_POST, $FDBGLogFile;
        
        $specchars = array(9792, 9787, 9786, 9829, 9830, 8226, 9632, 9788, 9794,
                            9472, 9474, 9484, 9488, 9492, 9496, 9617, 9618, 9619,
                            9608);
        
        $FDBGLogFile = LOGPATH.basename(__FILE__).'.log';
        include_once('/home/secrects.inc');    
        include_once(INCLUDE_PATH.'/OKServer.php');
        
        $first_access_range_list = array('Все'=>'', 'Старые'=>$this->binterval('-4 YEAR', '-2 YEAR'), 
                                        'Недавние'=>$this->binterval('-2 YEAR', '-3 MONTH'), 
                                        'Последние, год'=>$this->binterval('-1 YEAR', 'NOW'), 
                                        'Последние 3 месяца'=>$this->binterval('-3 MONTH', 'NOW'), 
                                        'Последние 1 месяц'=>$this->binterval('-1 MONTH', 'NOW'), 
                                        '1 день назад'=>$this->binterval('-1 DAY', '-1 DAY'), 
                                        '7 дней назад'=>$this->binterval('-7 DAY', '-7 DAY'), 
                                        '30 дней назад'=>$this->binterval('-30 DAY', '-30 DAY'));
        $age_range_list = array('Все'=>'', 'Школьники'=>'7-17', 'Студенты'=>'18-22', 'Работающие'=>'23-50', 'Пенсионеры'=>'51-80');
        
        if ($this->request->getVar('text')) {
            //$date = date('Y.m.d H:i', strtotime('-2 MINUTE'));
            $date = $this->request->getVar('expires');
            if (!$date) $date = date('Y.m.d H:i', strtotime('+2 HOUR'));
            
            $params = array();
            foreach ($_POST as $key=>$value)
                if ($value) {
//                    if ($key == 'text') $value = iconv('Windows-1252', 'UTF-8', $value);
                    $params[$key] = $value;
                }
                
            $params['expires'] = $date;
            //print_r($params);
            $result = OKServer::request($this->request->getVar('appKey'), 'notifications/sendMass', $params, false);
//            print_r($result);
            $params['result'] = $result;
            trace($params);
        }
        $log = @file_get_contents($FDBGLogFile);
        require($this->templatePath);
    }
    
    public function banRequest() {
        GLOBAL $charset;
        if ($select = $this->request->getVar('items')) {
//            $charset = 'utf8';
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
                $query = "DELETE FROM `".TBPREFIX."comments` WHERE $whereCID";
                DB::query($query);
                
                $query = "UPDATE `".TBPREFIX."users` SET `banType`=0 WHERE $where";
                DB::query($query);
            } else {
                $query = "UPDATE `".TBPREFIX."users` SET `banType`=$banType, `banDate`='$banDate' WHERE $where";
                DB::query($query);
            }
            
            if ($whereGID && (($banType == -1) || ($banType == 2) || ($banType == 3))) {
                $query = "DELETE FROM `".TBPREFIX."game` WHERE $whereGID";
                DB::query($query);
            }
            //echo $query;
        }
        $banTypeView1 = $this->request->getVar('banTypeView1', 1);
        $banTypeView2 = @$this->request->getVar('banTypeView2', false);
        
        $list = DB::asArray("SELECT u.*, c.comment as message, u.banContent as comment_id, g.id as game_id, c.content_id as content_id
                            FROM `".TBPREFIX."users` u LEFT JOIN `".TBPREFIX."comments` c ON c.comment_id=u.banContent LEFT JOIN `".TBPREFIX."game` g ON g.id=u.banContent
                            WHERE u.banType=$banTypeView1".($banTypeView2?" OR u.banType=4":''));
        require($this->templatePath);
    }
    
    public function refreshStatus() {
        //$list = DB::asArray("");            
        require($this->templatePath);
    }
    
    public function textCnv($text) {
        $result = mb_convert_encoding($text, 'CP1251', 'UTF-8');
      /*  
        $i = 0;
        $buffer = '';
        while ($i < strlen($result)) {
            $char = substr($result, $i, 2);
            $code = ord($char);
            if ($code < 128) {
                   $c2 = ord(substr($char, 1, 1));
                   echo dechex($code).' '.dechex($c2).'<br>';
            } else {
                $buffer .=$char;
                $i += 1;
            }
            $i += 1;
        }
        */
        return $result;
    }
    
    public function cnv() {
        GLOBAL $charset;
//        $charset = 'utf8';
        
        /*
        $count = 100;
        $start = 0;
        $games = DB::asArray("SELECT * FROM ".TBPREFIX."game` LIMIT $start, $count");
        
        foreach ($games as $key=>$item) {
            $games[$key]['name'] = $this->textCnv($games[$key]['name']);
            echo $games[$key]['name'].'<br>';
        }
        */
        
        if ($text = $this->request->getVar('text', '')) {
            $text = $this->textCnv($text);
        }
        
        require($this->templatePath);
    }
    
    protected function OKApiCall($method, $params) {
        return  OKServer::request(OKAPIKEY, $method, $params);
    }
    
    protected function getOkUsers($uids) {
        $params = array('uids'=>$uids, 'fields'=>'uid,name,first_name,last_name,gender,birthday,pic128x128,pic640x480,url_profile');
        $users = $this->OKApiCall('users/getInfo', $params);
        
        return $users;
    }    
    
    protected function usersInfo_ok(&$list, $checkSource=true) {
        $uids = array();
        foreach ($list as $item) $uids[] = $item['uid'];
            
        if (count($uids) > 0) {
            $infos = $this->getOkUsers(implode(',', $uids));
            
            foreach ($list as $key=>$item)
                foreach ($infos as $info) {
                    if ($item['uid'] == $info->uid) {
                        $list[$key]['user'] = $info;
                    }
                }
        }
        
        return $list;
    }
    
    public function leadersYear() {
        $years = array('2013', '2014', '2015', '2016', '2017');
        
        $year = $this->request->getVar('year', date('Y', strtotime('-1 year')));
        $line_count = DB::line("SELECT COUNT('uid') AS `count` FROM _clt_v_users WHERE year='{$year}'");
        if ($line_count['count'] == 0) {
            $query = "REPLACE _clt_v_users (SELECT uid, '{$year}' as `year`, SUM(rate) AS sum_rate, COUNT(id) AS vic_count FROM `clt_okgameVictory` WHERE time>='{$year}-01-01' AND time<='{$year}-12-31' GROUP BY uid)";
            DB::query($query);
        }
        
        $list = DB::asArray("SELECT * FROM _clt_v_users WHERE `year`='{$year}' ORDER BY sum_rate DESC LIMIT 0, 20");
        
        $this->usersInfo_ok($list);
        require($this->templatePath);
    }   

    public function VIPactions() {
        if ($this->request->getVar('send', false)) {
            $FDBGLogFile = LOGPATH.basename(__FILE__).'.log';
            include_once('/home/secrects.inc');    
            include_once(INCLUDE_PATH.'/OKServer.php');          

            $params = array('names'=>$this->request->getVar('actions', false));
            $result = OKServer::request('CBAOMQFBABABABABA', 'apps/setVipOffers', $params, false);
            print_r($result);
        }
        require($this->templatePath);
    }
}
?>