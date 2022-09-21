<?
    include_once(dirname(__FILE__).'/ga_model/config.inc');
    
        
    class ga2 extends g_model {
        function createFrame2($params) {
            GLOBAL $request;
            $file_name = GA_TMPPATH.'f'.$params[0].'_'.$params[1].'.gif';
            $size = explode('x', $params[2]);
            $data = $request->getVar('Filedata');
            $len = strlen($data);
            
            $extClass = $request->getVar('extClass', false);
            if ($extClass) {
                require_once(MODEL_PATH.'ga_model'.DS.$extClass.'.php');
                $extObject = new $extClass();
                $image = $extObject->execute($params, $request);
            } else {
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
            }
            
            $result = $image != null;
            $result = imagetruecolortopalette($image, true, 255) && $result;
            $result = imagegif($image, $file_name) && $result;
            $result = imagedestroy($image) && $result;
            return array('result'=>$result);
        }
        
        function createFrame($params) {
            $file_name = GA_TMPPATH.'f'.$params[0].'_'.$params[1].'.gif';
            $data = explode('|', $params[2]);
            $size = explode('x', $data[0]);
            if ($image = imagecreatetruecolor($size[0], $size[1])) {
                $index = 1;
                for ($y=0;$y<$size[1];$y++)
                    for ($x=0;$x<$size[0];$x++) {
                        $color = base_convert($data[$index], 36, 10);
                        imagesetpixel($image, $x, $y, $color);
                        $index++;
                    }
            }
            
            $result = $image != null;
            $result = imagetruecolortopalette($image, true, 255) && $result;
            $result = imagegif($image, $file_name) && $result;
            $result = imagedestroy($image) && $result;
            return array('result'=>$result);
        }
        
        function createGIF($params) {
            GLOBAL $request;
            
            include_once(INCLUDE_PATH.'/GIFEncoder.class.php');
            include_once(INCLUDE_PATH.'/helpers/utils.php');
            
            $fileName = 'ga'.$params[0].'.gif';
            $filePath = GA_RELATIVETMPPATH.$fileName;
            
            $doubleLoop = $request->getVar('extend', '') == 'doubleLoop';
            $acel = 10;
            
            for ($i=0; $i<$params[1]; $i++) {
                $frames[] = GA_TMPPATH.'f'.$params[0].'_'.$i.'.gif';
                $framed[] = round($params[2] / $acel);
            }
            
            if ($doubleLoop) {
                for ($i=$params[1] - 2; $i > 0; $i--) {
                    $frames[] = GA_TMPPATH.'f'.$params[0].'_'.$i.'.gif';
                    $framed[] = round($params[2] / $acel);
                }
            }

            $gif = new GIFEncoder($frames,
                                    $framed,
                                    0, 2, 0, 0, 0,
                                    "url");
            $file = fopen(DATA_PATH.$filePath, 'w+'); // Создаем GIF файл
            fwrite($file, $gif->GetAnimation());
            fclose($file);
            
            for ($i=0; $i<$params[1]; $i++) { // Удаляем все файлы кадров
                unlink($frames[$i]);
            }

            // Передаем GIF файл на сервер хранилище
            $model = explode('_', get_class($this));
            $request = Utils::createRequest(STORAGE_SERVER, APP_ID, array('querycount'=>1, 'query1'=>$model[0].';storageImage;'.DATA_URL.$filePath.';'.$fileName.';from_question'));
                        
            $result = file_get_contents($request);
            $result = $this->app->json_decode($result);
            
            //Удаляем GIF файл
            unlink(DATA_PATH.$filePath);
            
            return array('fileURL'=>$result['response'][0]['fileURL']);
        }
        
        public function storageImage($params) {
            $imageBody = file_get_contents($params[0]);
            $file = fopen(QUESTION_PATH.$params[1], 'w+');
            fwrite($file, $imageBody);
            fclose($file);
            return array('fileURL'=>QUESTION_URL.$params[1]);
        }

        public function uploadImage($params) {
            GLOBAL $_FILES;
            include_once ('include/image.php');
          
            $uid = $params[0];
            $filePath = USERDATAPATH.$params[1];
            if (file_exists($filePath)) unlink($filePath);
            
            $image = new Image();
            $result = $image->Upload($_FILES['Filedata'], $filePath);
            if (is_array($result)) {
                $image->Resize($result['fileName'], 800, 800, '', 90);
                return array('file_url'=>USERDATAURL.$params[1]);
            } else return array('error'=>$result);
        }

        function getBalance($params) {
            $query = "SELECT transaction_id, DATE_FORMAT(time, '%d.%m.%Y') as `date`, service_id, params, sms_price + other_price AS price 
                      FROM ga_transaction 
                      WHERE user_id='{$params[0]}'";
            $balance = query_array($query);
            $curTime = date('d.m.Y');
            $isFromDay = 0;
            foreach ($balance as $key=>$t) {
                if (($t['service_id'] == 4) && 
                    ($t['date'] == $curTime)) $isFromDay = 1;
                $balance[$key]['price'] = ceil($t['price'] / 100 / 11);
            }
            $result = array('balance'=>$balance, 'time'=>microtime(), 'isFromDay'=>$isFromDay);
			return $result;
        }

        function setTransaction($params) {
            $price = $params[2] * 100 * 11;
            $result = sql_query("INSERT INTO ga_transaction (`user_id`, `service_id`, `other_price`, `time`, `params`) 
                        VALUES ('{$params[0]}', {$params[1]}, {$price}, '".date('Y-m-d H:i:s')."', '{$params[3]}')");
            return array('result'=>$result, 'price'=>$params[2], 'uid'=>$params[0]);
        }
    }
?>