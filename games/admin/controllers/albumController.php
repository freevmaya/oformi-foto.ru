<?

include_once(INCLUDE_PATH.'/_edbu2.php');

define('USERALBUMRPATH', 'games/data/user-albums/');
define('QUESTIONPATH', DATA_PATH.'album/question/');
define('QUESTIONURL', DATA_URL.'album/question/');
define('USERALBUMPATH', MAINPATH.USERALBUMRPATH);
define('USERALBUMURL', DATA_URL.'user-albums/');


class albumController extends controller {
    public function albumState() {
        $albums = query_array("SELECT COUNT(uid) as `count`, theme FROM ab_user_options GROUP BY theme");
        $musics = query_array("SELECT COUNT(uid) as `count`, music FROM ab_user_options GROUP BY music");
        $backs = query_array("SELECT COUNT(uid) as `count`, background FROM ab_user_options GROUP BY background");
        require_once TEMPLATES_PATH.'albumState.html';
    }
    
    public function albumList() {
        if ($uid = $this->svar('uid', 0)) {
            if ($this->request->getVar('action', 0) == 'delete') {
                $sid = $this->request->getVar('sid', 0);
                $time = $this->request->getVar('time', 0);
                $delResult = DB::query('DELETE FROM album_transaction WHERE `user_id`=\'%s\' AND `service_id`=%s AND `time`=\'%s\'', array($uid, $sid, $time));
            }
            $list = DB::asArray('SELECT * FROM album_transaction WHERE user_id=\'%s\' AND `service_id` > 5000', $uid);
            $path = USERALBUMPATH.$uid.'/';
            $url = USERALBUMURL.$uid.'/';
            foreach ($list as $key=>$item) {
                $fileName = ($item['service_id'] - 5000).'.zip';
                $list[$key]['fileName'] = $fileName;
                if (file_exists($path.$fileName)) {
                    $list[$key]['url'] = $url.$fileName;
                    $list[$key]['size'] = round(filesize($path.$fileName) / 1024).'Κα';
                }
            }
/*            $list = Array();
            if ($d = dir($path)) {
                while (false !== ($entry = $d->read())) {
                    if (($entry != '.') && ($entry != '..')) {
                        if (!is_dir($path.$entry)) {
                            $extr = explode('.', $entry);
                            if ($extr[count($extr) - 1] == 'zip') {
                                $list[] = array(
                                    'fileName'=>$entry,
                                    'url'=>$url.$entry,
                                    'size'=>round(filesize($path.$entry) / 1024)
                                );
                            }
                        }
                    }
                }
                $d->close();
            }*/
        }
    
        require($this->templatePath);
    }
}

?>