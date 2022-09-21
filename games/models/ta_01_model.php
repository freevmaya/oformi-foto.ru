<?
    $config = isset($_GET['config'])?$_GET['config']:'tmpls';
    
    include_once(dirname(__FILE__).'/'.$config.'/config.php'); 
    include_once(INCLUDE_PATH.'/_edbu2.php');
    
    define('SR_CNVPNG', 4);
    define('SR_UNLINKPNG', 2);
    define('SR_SAVEPNG', 1);
    
    define('LIVEDATE', date('Y-m-d H:i:s', strtotime('+1 DAY')));
    define('TOADDTIME', 'INTERVAL -1 DAY');
    
    class ta_01 extends g_model {
        protected function tmplToDB($templates) {
            foreach ($templates as $id=>$tmpl) {
                $params = json_encode($tmpl);
                DB::query("REPLACE pj_templates (`tmpl_id`, `live`, `autor_id`, `params`, `active`) 
                            VALUES ($id, '".LIVEDATE."', 4, '{$params}', 1)");                
            }
        }
    
        public function inUser($params) {
            /* Отрыть если нужно создать БД из файла шаблонов
            $cfg = json_decode(file_get_contents(CFGFILE), true);
            $templates = $this->unpack($cfg['templates']);
            $this->tmplToDB($templates);
            $this->templatesToFile(CFGFILEPREPARE);
            */
            
            $aTmpl = $this->getTmplList();
            //foreach ($aTmpl as $key=>$tmplRec) $aTmpl[$key]['params'] = json_decode($tmplRec['params'], true);
            
            $user = DB::line("SELECT * FROM `pj_admin` WHERE `login`='{$params[0]}'");
            
            $cfg = json_decode(file_get_contents(CFGFILETEMPLATE), true);
            $result = array('result'=>$user?1:0, 'uid'=>@$user['uid'], 'tmpls'=>$aTmpl, 'defaults'=>$cfg['defaults'], 'user'=>$user);
            
            return $result; 
        }
        
        public function getTmplList($params=null) {
            $list = DB::asArray('SELECT t.tmpl_id, DATE_FORMAT(DATE_ADD(t.live, '.TOADDTIME.'), \'%d.%m\') as `time`, a.uid, a.login FROM pj_templates t INNER JOIN pj_admin a ON a.uid = t.autor_id WHERE t.`active`=1 ORDER BY t.tmpl_id DESC');
            $tmplIds = '';
            $index = array();
            foreach ($list as $i=>$item) {
                $list[$i]['login'] = '/'.$item['time'].' '.$item['login'];
                $index[$list[$i]['tmpl_id']] = $i;
                $tmplIds .= ($tmplIds?',':'').$list[$i]['tmpl_id'];
            } 
            
            //https://oformi-foto.ru/games/data/?model=tmpl_info&format=json&tmpl_id=' + e.object.tmpl_id
            $query = "https://oformi-foto.ru/games/data/?model=tmpl_info&format=json&tmpl_id={$tmplIds}";
//            echo $query;
            $maxLikes = 0;
            $pmLikes = 0;
            if ($rate_list = json_decode(file_get_contents($query), true)) {
                foreach ($rate_list as $rate_item) {
                    $li = $index[$rate_item['tmpl_id']];
                    unset($rate_item['tmpl_id']);
                    $rate = $rate_item['save_rate'] + $rate_item['user_rate'];
                    $list[$li]['rate'] = $rate;
                    if ($rate > $maxLikes) {
                        $pmLikes = $maxLikes?$maxLikes:$rate;
                        $maxLikes = $rate;
                    }
                }   
            }
            
//            $rstates = array('Плохо', 'Так себе', 'Нормально', 'Хорошо', 'Отлично');
            $rstates = array('', '', 'Нормально', 'Хорошо', 'Отлично');
            foreach ($list as $i=>$item) {
                if (isset($item['rate']) && $item['rate']) {
                    if (is_numeric($item['rate'])) {
                        $ri = round(min($item['rate'] / $pmLikes, 1) * (count($rstates) - 1));  
                        $list[$i]['rate_str'] = ($ri + 1).' '.$rstates[$ri];                     
                    }
                } else {
                    $list[$i]['rate_str'] = '?';
                    $list[$i]['rate'] = 0;
                }
            }
            return $list;
        }
        
        protected function lastID() {
            $max_line = DB::line('SELECT MAX(`tmpl_id`) AS max_id FROM pj_templates');
            return $max_line['max_id'];
        }
        
        public function getTemplate($params) {
            return DB::line("SELECT * FROM pj_templates WHERE tmpl_id={$params[0]}");
        }
        
        
        public function setDefaultTmpl($params) {
            $cfg_prepare = json_decode(file_get_contents(CFGFILEPREPARE), true);
            $templates = $this->unpack($cfg_prepare['templates']);
            $new_default_id = $params[0];
            $new_group_id = 0;
            foreach ($templates as $id=>$tmpl) {
                if ($new_default_id == $id)
                    $new_group_id = $tmpl['group'];
            }
            
            $cfg = json_decode(file_get_contents(CFGFILETEMPLATE), true);
            
            $cfg['defaults'][0]['DEFAULT_MASK'] = $new_default_id;
            $cfg['defaults'][0]['DEFAULT_GROUP'] = $new_group_id;
            
            $cfg['defaults'][1]['DEFAULT_MASK'] = $new_default_id;
            $cfg['defaults'][1]['DEFAULT_GROUP'] = $new_group_id;
            
            $cfg_prepare['defaults'] = $cfg['defaults'];
            
            $file = fopen(CFGFILETEMPLATE, 'w+');
            fwrite($file, json_encode($cfg));
            fclose($file); 
            
            $file = fopen(CFGFILEPREPARE, 'w+');
            fwrite($file, json_encode($cfg_prepare));
            fclose($file);
            
            return array('result'=>1);
        }
        
        public function uploadPNG($params) {
            
            $tmpl_id_rec = 0;
            $saveResult = 0;
            $queryStatus = 0;
            $tmpl_id = 0;
            
            $options = json_decode($params[2], true);
            $user = DB::line("SELECT * FROM `pj_admin` WHERE `login`='{$options['autor_login']}'");
            
            $template = array(
                'group'=>0,
                'spots'=>json_decode($params[0], true) 
            );
            
            if ($tmpl_params = json_decode($params[1], true)) {
                foreach ($tmpl_params as $key=>$value) {
                    if ($key != 'tmpl_id')
                        $template[$key] = $value;
                    else $tmpl_id = $value;
                }
            }
            
            if (isset($template['spots']) && (count($template['spots'])) == 0)
                unset($template['spots']);
            
            $templateJSON = json_encode($template);
            $query = '';
            $isNew = $tmpl_id == 0;
            $isFile = $isNew || (isset($options['reset_png']));
             
            $autor_id = $user['uid'];
            if ($isNew) {
                $tmpl_id = DB::one("SELECT MAX(tmpl_id) FROM pj_templates") + 1;
                $query = "INSERT INTO `pj_templates` (`tmpl_id`, `live`, `autor_id`, `params`, `active`) VALUES ({$tmpl_id}, '".LIVEDATE."', {$autor_id}, '{$templateJSON}', 1)";
            } else $query = "UPDATE `pj_templates` SET `params`= '{$templateJSON}' WHERE `tmpl_id`={$tmpl_id}";
            
            $queryStatus = DB::query($query);
/*            
            if ($isNew) {
                $tmpl_id_rec = DB::line('SELECT LAST_INSERT_ID() AS tmpl_id');
                $tmpl_id = $tmpl_id_rec['tmpl_id'];
            }
*/            
           
            if ($isFile) $saveResult = $this->savePNG($tmpl_id);
            $this->refreshAllSizes($tmpl_id);
            $this->templatesToFile(CFGFILEPREPARE);
            
            return array('tmpl_id'=>$tmpl_id, 'is_new'=>$isNew?1:0, 'saveResult'=>$saveResult, 'queryStatus'=>$queryStatus, 'autor_id'=>$autor_id);
        }
        
        public function refreshAllSizes($tmpl_id) {
            $servers = array(PREVIEWSERVER);
            $widths = array(385);
            foreach ($servers as $server) {
                foreach ($widths as $width) {
                    file_get_contents($server.'?id='.$tmpl_id.'&width='.$width);
                }
            }
        }
        
        public function removeTemplate($params) {
            $tmpl_id = $params[0]; 
            $result = DB::query("DELETE FROM pj_templates WHERE tmpl_id={$tmpl_id}");
            
            @unlink(OUTPATH.'/'.$tmpl_id.'.jpg');
            @unlink(OUTPATH.'/'.$tmpl_id.'m.jpg');
            @unlink(PREVIEWPATH.'/i'.$tmpl_id.'.jpg');
            
            $this->templatesToFile(CFGFILEPREPARE);
            return array('result'=>$result);
        }
        
        public function templatesToFile($fileName) {
            $cfg = json_decode(file_get_contents(CFGFILETEMPLATE), true);
            $templates = array();
            $items = DB::asArray('SELECT * FROM `pj_templates` WHERE `active`=1');
            foreach ($items as $item) {
                $templates[$item['tmpl_id']] = json_decode($item['params'], true);
            } 
            
            $newTmpls = $this->packTmpls($templates);
            $cfg['templates'] = $newTmpls;
            
            $file = fopen($fileName, 'w+');
            fwrite($file, json_encode($cfg));
            fclose($file);
        }
        
        public function applyAll($params) {
            $result = false;
            $message = '';
            if (file_exists(CFGFILEPREPARE)) {
                $new_cfg = json_decode(file_get_contents(CFGFILEPREPARE), true);
                $new_cfg['templates'] = $this->unpack($new_cfg['templates']);             

                if ($new_cfg) {// && (count($new_cfg['templates']) > count($prev_cfg['templates']))) {               
                    $reservedName = date('d-m-Y H:i').'.json';
                    $result = copy(CFGFILE, RESERVEDPATH.$reservedName);  
                    $result &= copy(CFGFILEPREPARE, CFGFILE);
                    if (!$result) $message = 'Ошибка при копировании файлов'; 
                } else $message = 'Нет новых шаблонов';
                                
            } else $message = 'Нет подготовленного файла';
            
            return array('result'=>$result?1:0, 'message'=>$message);
        }
        
        protected function pngCnv($png_file) {
            $result = 0;
            $rs = $this->convertToJPG($png_file, 
                            OUTPATH, 
                            PREVIEWPATH, 
                            IMAGEQUALITY, 
                            MASKQUALITY, 
                            explode('x', PREVIEWSIZE), 
                            explode('x', RESULTSIZE));  
            if ($rs['alpha'] && $rs['image']) $result = SR_CNVPNG;
            return $result;
        }
        
        protected function savePNG($tmpl_id) {
            global $GLOBALS;
            $result = 0;
            
            $file_name = $tmpl_id.'.png';
            if ($png = file_get_contents("php://input")) {
                $png_file = SRCPATH.$file_name;
                if (file_exists($png_file)) {
                    $result = $result | SR_UNLINKPNG;
                    unlink($png_file);
                }
                $file = fopen($png_file, 'w+');
                fwrite($file, $png);
                fclose($file);
                $result = $result | SR_SAVEPNG;
                $result = $result | $this->pngCnv($png_file);
            } else $result = "png data empty!";
            return $result;
        }
        
        private function copyValue($value) {
            if (is_array($value)) return array_merge($value, array());
            return $value;
        }
        
        public function packTmpls($templates) {
            $result = array();
            $index = -1;
            $cur_group = -1;
            $offset = 0;
            $prev_id = 0;
            foreach ($templates as $id=>$tmpl) {
                if (($cur_group != $tmpl['group']) || ($prev_id + 1 != $id)) {
                    $cur_group = $tmpl['group'];
                    $offset = 0;
                    $index++;
                    $result[$index] = array('id'=>array());
                }
                $result[$index]['id'][] = $id;
                foreach ($tmpl as $key=>$value) {
                    if (!isset($result[$index][$key])) $result[$index][$key] = array_fill(0, 1 + $offset, 0); // Если массив не инициализирован, создаем нужного размера
                    else { 
                        $minIndex = count($result[$index][$key]);
                        while ($minIndex < $offset) {                           // Если массив меньше чем надо заливаем его нулями до требуемого размера 
                            $result[$index][$key][$minIndex] = 0;
                            $minIndex++;
                        }
                    }
                    $result[$index][$key][$offset] = $value; 
                }
                $offset++;
                $prev_id = $id;
            }
            
            foreach ($result as $i=>$tmpl) {
                foreach ($tmpl as $key=>$value) {
                    if (($key != 'id') && ($key != 'spots')) $result[$i][$key] = $this->packProperty($value);
                }
                $result[$i] = $this->packID($result[$i]);
            }
            return $result;
        }        
        
        protected function packProperty($values) {
            $result = null;
            if (!is_array($values[0]) && (count(array_unique($values)) == 1)) 
                $result = $values[0];
            else $result = $values;
            return $result;
        }        
        
        protected function packID($values) {
            $result = $values;
            $ids = $values['id'];
            
            if (is_array($ids)) {
                $count = count($ids);
                if ($count > 2) {
                    $firstID    = $ids[0];
                    $lastID     = $ids[$count - 1];
                    
                    if ($firstID + $count - 1 == $lastID) 
                        $result['id'] = array($firstID, $lastID);
                } else if ($count == 1) {
                    foreach ($result as $key=>$value) {
                        if (is_array($result[$key]))
                            $result[$key] = $result[$key][0];
                    }
                }
            }                     
            return $result;
        }
        
        private function unpackPropertys($item, $offset) {
            $result = array();
            foreach ($item as $key=>$property) {
                if ($key != 'id') {
                    if (is_array($property)) {
                        if (isset($property[$offset])) 
                            $result[$key] = $this->copyValue($property[$offset]);
                    } else $result[$key] = $this->copyValue($property);
                }
            }
            return $result;
        }        
        
        protected function unpack($templates) {
            
            $result = array();
            foreach ($templates as $i=>$item) {
                if (is_array($item['id'])) {
                    $count = count($item['id']);
                    if ($count > 2) {
                        for ($n=0; $n<$count; $n++) {
                            $result[$item['id'][$n]] = $this->unpackPropertys($item, $n);
                        }
                    } else {
                        $i = 0;
                        for ($n=$item['id'][0]; $n<=$item['id'][1]; $n++) {
                            $result[$n] = $this->unpackPropertys($item, $i);
                            $i++;
                        }
                    }                        
                } else $result[$item['id']] = $item;
            }
            
            ksort($result);
            
            return $result;        
        }
        
//Методы конвертации    
        protected function checkSize($result, $maxSize, $fillColor=null) {
            $size = array(imagesx($result), imagesy($result));
            if (($size[0] > $maxSize[0]) && ($size[1] > $maxSize[1])) {
                $scale = min($maxSize[0]/$size[0], $maxSize[0]/$size[1]);
                $newsize = array(round($size[0] * $scale), round($size[1] * $scale));
                
                $tmp = imagecreatetruecolor($newsize[0], $newsize[1]);
                
                if ($fillColor !== false) imagefilledrectangle($tmp, 0, 0, $newsize[0], $newsize[1], $fillColor);
                imagecopyresampled($tmp, $result, 0, 0, 0, 0, $newsize[0], $newsize[1], $size[0], $size[1]);
                imagedestroy($result);
                $result = $tmp;
            }
            return $result;
        }

        
        protected function saveAlphaChannel($filePath, $destPath, $quality, $resultSize) {
            $resultName = str_replace('i', '', basename($filePath, '.png'));
            $resultFile = "{$destPath}/{$resultName}m.jpg";
            $png = imagecreatefrompng($filePath);
            $width = imagesx($png);
            $height = imagesy($png);
            $result = imagecreatetruecolor($width, $height);
        
            imagefilter($png, IMG_FILTER_COLORIZE, 255, 255, 255);
        
            imagecopy($result, $png, 0, 0, 0, 0, $width, $height);
            $result = $this->checkSize($result, $resultSize);
            
            if (file_exists($resultFile)) unlink($resultFile);
            
            imagejpeg($result, $resultFile, $quality);
        
            imagedestroy($png);
            imagedestroy($result);
            chmod($resultFile, PERMISSION);
            return $resultFile;
        }
        
        protected function saveJPG($filePath, $destPath, $destPreviewPath, $quality, $previewSize, $resultSize) {
            $resultName = str_replace('i', '', basename($filePath, '.png'));
            $resultFile = "$destPath/$resultName.jpg";
            
            $previewFile = "$destPreviewPath/i$resultName.jpg";
            $result = imagecreatefrompng($filePath);
            $result = $this->checkSize($result, $resultSize);
            
            if (file_exists($resultFile)) unlink($resultFile);
            imagejpeg($result, $resultFile, $quality);
            chmod($resultFile, PERMISSION);
            $preview = $this->checkSize($result, $previewSize, imagecolorallocate($result, 255, 255, 255));
            
            if (file_exists($previewFile)) unlink($previewFile);
            imagejpeg($preview, $previewFile, $quality);
            chmod($previewFile, PERMISSION);
        
            imagedestroy($preview);
        //    imagedestroy($result);
            return $resultFile;
        }
        
        protected function checkDir($destPath) {
            if (!file_exists($destPath)) { 
                if (!mkdir($destPath)) die("Немогу создать каталог $destPath");
                chmod($destPath, PERMISSION);
            }
        }
        
        protected function convertToJPG($filePath, $destPath, $destPreviewPath, $quality, $maskQuality, $previewSize, $resultSize) {
            $result = array();
            $this->checkDir($destPath);
            $this->checkDir($destPreviewPath);
            $result['alpha'] = $this->saveAlphaChannel($filePath, $destPath, $maskQuality, $resultSize);
            $result['image'] = $this->saveJPG($filePath, $destPath, $destPreviewPath, $quality, $previewSize, $resultSize);
            return $result;
        }
    }
?>    