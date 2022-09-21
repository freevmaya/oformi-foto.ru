<?
    define('ALBUMTEMPLATE', MAINPATH.'games/models/templates/albums2/');
    define('USERALBUMRPATH', 'games/models/templates/user-albums/');
    define('QUESTIONPATH', DATA_PATH.'album/question/');
    define('QUESTIONURL', DATA_URL.'album/question/');
    define('USERALBUMPATH', MAINPATH.USERALBUMRPATH);
    define('ALBUMRESULTFILE', '.zip');
    
    class album4 extends g_model {
        public function saveOptions($params) {
            $result = 0;
            if (($options = $this->getOptions($params)) && (isset($options['uid'])))
                $result = sql_query("UPDATE ab_user_options SET `theme`={$params[1]}, `background`={$params[2]}, `music`={$params[3]} WHERE `uid`={$params[0]}");
            else $result = sql_query("REPLACE INTO ab_user_options (`uid`, `theme`, `background`, `music`) VALUES ({$params[0]}, {$params[1]}, {$params[2]}, {$params[3]})");
            return array('result'=>$result);
        }
        
        public function getPaidAlbums($params) {
            $mindate = date('Y-m-d H:i:s', strtotime("-5 day"));
            $query = "SELECT `time`, SUM(sms_price + other_price) / 100  AS price, service_id
                        FROM album_transaction 
                        WHERE `user_id`='{$params[0]}' && (`service_id` > 5000) AND (`time` > '{$mindate}') AND (`debug`=0)  
                        GROUP BY service_id";
			return query_array($query);
        }
        
        public function getOptions($params) {
            $query = "SELECT * FROM ab_user_options WHERE `uid`='{$params[0]}'";
            if (!$result = query_line($query)) 
                $result = array();
            $result['serverTime'] = strtotime('now');
            $result['paidAlbums'] = $this->getPaidAlbums($params);
            return $result;
        }
        
        protected function checkAndCreateUAPath($uid, $aid, $files) {
            $album_path = USERALBUMPATH.$uid.DS.$aid.DS;
            $album_ipath = $album_path.'data/album-image/';
            if (file_exists(USERALBUMPATH.$uid)) { // Если есть директория для альбома
                if (file_exists($album_path)) { // Есть дириктория альбома
                    if (file_exists(USERALBUMPATH.$uid.DS.$aid.ALBUMRESULTFILE)) 
                        return MAINURL.'/'.USERALBUMRPATH.$uid.'/'.$aid.ALBUMRESULTFILE;    // Есть файл для закачки 
                    $i = 0;
                    $files_notfond = array();
                    while ($i < count($files)) {    // Просматриваем наличие файлов
                        if (!file_exists($album_ipath.$files[$i][0])) { // Если нет какого то фала, тогда возвращаем список существующих файлов
                            $files_notfond[] = $files[$i][0];
                            $files_notfond[] = $files[$i + 1][0];
                        }
                        $i += 2;
                    }
                    return $files_notfond; // Возвращаем список закаченных фалов
                } else {
                    mkdir($album_path, 0744);
                    mkdir($album_ipath.'preview', 0744, true);
                }
            } else {
                mkdir(USERALBUMPATH.$uid, 0744);
                mkdir($album_ipath.'preview', 0744, true);
            }
            
            return false;
        }
        
        protected function uploadFiles($uid, $aid, $preview_path, $full_path, $files) {
            $i = 0;
            $album_ipath = USERALBUMPATH.$uid.DS.$aid.DS.'data/album-image/';
            while ($i < count($files)) {
                try {
                    copy('http://'.$full_path.'/'.$files[$i][0], $album_ipath.$files[$i][0]);
                    copy('http://'.$preview_path.'/'.$files[$i + 1][0], $album_ipath.'preview'.DS.$files[$i + 1][0]);
                    $i += 2;
                } catch(Exception $e) {
                    return false; 
		        }
            }
            
            return true;
        }
        
        public function createZIP($uid, $aid, $title, $description, $theme, $background, $music, $image_files) {
            include_once(INCLUDE_PATH.'/zip.php');
            try {
                ob_start();
                require_once(ALBUMTEMPLATE.'data/album.json');
                $dataContent = ob_get_contents();
                ob_end_clean(); 

                $path = USERALBUMPATH.$uid.DS.$aid.DS;
                
                $d_file = fopen($path.'data/album.json', 'w+');
                fwrite($d_file, $dataContent);
                fclose($d_file);

                if (!file_exists($path.'data/music')) mkdir($path.'data/music');
                if (!file_exists($path.'data/backimages')) mkdir($path.'data/backimages');
                if (!file_exists($path.'data/themes')) mkdir($path.'data/themes');
                
                $filePath = USERALBUMPATH.$uid.DS.$aid.ALBUMRESULTFILE; 
                $zipfile = new zipfile();
                $zipfile->_readdir(ALBUMTEMPLATE, $files);
                foreach ($files as $file)
                    if (!file_exists($path.$file)) 
                        copy(ALBUMTEMPLATE.$file, $path.$file);
                $zipfile->packToFile($path, $filePath, false);
    //            $zipfile->_readdir($path, $files);
    //            foreach ($files as $file) unlink($path.$file);
                chmod ($filePath, 0755);
                return MAINURL.'/'.USERALBUMRPATH.$uid.'/'.$aid.ALBUMRESULTFILE;
            } catch (Exception $e) {
                return false;
            }
        }
        
        protected function unpackImageList($list) {
            $result = array();
            foreach ($list as $item)
                $result[] = explode(chr(1), $item);
            return $result;
        }
        
        public function downloadAlbum($params) {
            $files = $this->unpackImageList(array_slice($params, 9));
            $all_files = $files;
            $uid = $params[0];
            $aid = $params[1];
            $result = $this->checkAndCreateUAPath($uid, $aid, $files);
            if (is_array($result)) { // Если файлы закаченны не полностью, тогда докачиваем и создаем архив
                $files = $result;
            } else if (is_string($result)) {    // Если архив уже есть, передаем его
                return array('file_url'=>$result);
            }
            
            if ($this->uploadFiles($uid, $aid, $params[7], $params[8], $files)) {
                if ($file = $this->createZIP($uid, $aid, $params[2], $params[3], $params[4], $params[5], $params[6], $all_files))
                    $result = array('file_url'=>$file);
                else $result = array('error'=>2);
            } else $result = array('error'=>1);
            
            return $result;
        }
        
        public function uploadImage($params) {
            GLOBAL $request;
            $file_name = QUESTIONPATH.'q'.$params[0].'.jpg';
            $fileURL = QUESTIONURL.'q'.$params[0].'.jpg';
            
            $size   = explode('x', $params[1]);
            $data   = $request->getVar('Filedata');
            $len    = strlen($data);
            if ($image = imagecreatetruecolor($size[0], $size[1])) {
                $index = 0;
                for ($y=0;$y<$size[1];$y++)
                    for ($x=0;$x<$size[0];$x++) {
                        if ($index + 6 < $len) {
                            $code = '$color = 0x'.substr($data, $index, 6).';';
                            eval($code);
                            imagesetpixel($image, $x, $y, $color);
                            $index += 6;
                        }
                    }
            }
            
            $result = imagejpeg($image, $file_name, 90);
            $result = imagedestroy($image) && $result;
            return array('file'=>$result?$fileURL:'');
        }
    }
?>