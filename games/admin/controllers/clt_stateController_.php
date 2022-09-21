<?

define('COLLAGEPATH', DATA_PATH.'clothing/game/');
define('COLLAGEPREVIEWPATH', DATA_PATH.'clothing/game/preview/');
define('DEFCOUNT', 20);

include_once(INCLUDE_PATH.'/_edbu2.php');

class clt_stateController extends controller {
    public function victory() {
        $limit = $this->svar('limit', DEFCOUNT);
        if ($sDate = $this->request->getVar('sDate', false)) {
            $list = DB::asArray("SELECT *
                                FROM `clt_game` g 
                                WHERE `time`< '{$sDate}' AND `rate`>0 ORDER BY `time` DESC LIMIT 0, $limit");
        }
        require($this->templatePath);
    }
    
    public function test() {
        $uid        = '8062938299454250872';
        $list       = DB::asArray("SELECT * FROM `clt_game` WHERE `uid`={$uid} AND noVictory=0 AND `time`<=NOW()-INTERVAL 1 DAY");
        $nullIds    = '';
        $noVic      = '';
        
        foreach ($list as $key=>$item) {
            if ($item['rate'] == 0) { // Отсеиваем все с нулевым рейтингом
                $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
            } else {
                $endTime = date('Y-m-d H:i:s', strtotime($item['time'].'+1 day'));
                $query = "SELECT *,
                            (SELECT SUM(votes) FROM clt_votes WHERE game_id=g.id AND `time`<='{$endTime}') AS votes 
                        FROM `clt_game` g 
                        WHERE g.`id`<>{$item['id']} AND g.`time`>='{$item['time']}' AND g.`time`<='$endTime' 
                        ORDER BY votes DESC
                        LIMIT 0,1";
                $vic = DB::line($query);
                if ($vic['votes'] > $item['rate']) { // Это не победа
                    $nullIds .= ($nullIds?' OR ':'')."id={$item['id']}";
                } else {
                    DB::query("UPDATE `clt_game` SET noVictory=2 WHERE id={$item['id']}");
                    DB::query("INSERT `clt_gameVictory` VALUES ({$item['id']}, $uid, '{$item['time']}', {$item['rate']})");
                }
            }
        }
        
        if ($nullIds) DB::query("UPDATE `clt_game` SET noVictory=1 WHERE $nullIds");
    }
    
    public function victoryTest() {
        if ($startDate = $this->request->getVar('startDate', false)) {
            $limit = $this->svar('limit', DEFCOUNT);
            $endDate = $this->request->getVar('endDate', false);
            $collages = DB::asArray("SELECT *, (SELECT SUM(votes) FROM clt_votes WHERE game_id=g.id AND `time`<='{$endDate}') AS votes 
                                FROM `clt_game` g
                                WHERE `time`>='{$startDate}' AND  `time`<='{$endDate}' ORDER BY `rate` DESC LIMIT 0, $limit");
        } else if ($id = $this->request->getVar('id', false)) {
            $item = DB::line("SELECT *, (SELECT SUM(votes) FROM clt_votes WHERE game_id=g.id AND `time`<=g.time + INTERVAL 1 DAY) AS votes  FROM `clt_game` g WHERE g.`id`={$id}");
            $endTime = date('Y-m-d H:i:s', strtotime($item['time'].' +1 day'));
            $query = "SELECT *,
                            (SELECT SUM(votes) FROM clt_votes WHERE game_id=g.id AND `time`<='{$endTime}') AS votes 
                        FROM `clt_game` g 
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
        $where = "`time`<='{$endDate}'";
        if ($noVictory) $where .= " AND noVictory=1"; 
        $list = DB::asArray("SELECT id, (SELECT SUM(votes) FROM clt_votes WHERE game_id=g.id AND `time`<=g.`time` + INTERVAL 1 DAY) AS votes
                            FROM `clt_game` g 
                            WHERE $where");
        $ids = array();
        foreach ($list as $item) {
            if (!$isNullOnly || ($item['votes']==0)) $ids[] = $item['id'];
        }
        return $ids;
    }
    
    public function removeCollages($ids) {
        if (count($ids)) {
            foreach ($ids as $id) {
                if (file_exists(COLLAGEPATH.$id.'.jpg')) {
                    unlink(COLLAGEPATH.$id.'.jpg');
                    unlink(COLLAGEPREVIEWPATH.$id.'.jpg');
                }
            }
            
            DB::query("DELETE FROM `clt_game` WHERE id=%s", implode(' OR id=', $ids)); 
            DB::query("DELETE FROM `clt_votes` WHERE game_id=%s", implode(' OR game_id=', $ids));
            DB::query("OPTIMIZE TABLE `clt_game`");
            DB::query("OPTIMIZE TABLE `clt_votes`");
        }
        
        return $ids;
    }
    
    public function ripper() {
        require($this->templatePath);
    }      
}
?>