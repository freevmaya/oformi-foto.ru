<?

define('CFGFILEPREPARE', '/home/vmaya/games/data/a/templates/temp_storage_prepare.json');
define('CFGFILE', '/home/vmaya/games/data/a/templates/temp_storage.json');
include_once(CONTROLLERS_PATH.Admin::$task[0].'/config.php');

class jpgCnvController extends controller {
    public function jpgcnvForm2() {
        GLOBAL $_SESSION;
        $cfg = json_decode(file_get_contents(CFGFILE));
        if ($this->request->getVar('src_path', false) && file_exists($this->request->getVar('src_path'))) {
            set_time_limit(0);
            $result = $this->fileList($this->request->getVar('src_path'));
            foreach ($result as $key=>$filePath) {
                $result[$key] = $this->convertToJPG($filePath, 
                        $this->request->getVar('out_path'), 
                        $this->request->getVar('out_previewPath'), 
                        $this->request->getVar('quality'), 
                        $this->request->getVar('maskQuality'), 
                        explode('x', $this->request->getVar('previewSize')), 
                        explode('x', $this->request->getVar('resultSize'))
                );
            }
            if ($this->request->getVar('zip', false) && count($result)) $this->toZIP($this->request->getVar('src_path'), RESULTZIPPATH);
            if ($this->request->getVar('unlink')) {
                $this->clearPath($this->request->getVar('src_path'));
            }
            
            $cfg->defaults[0]->DEFAULT_MASK = $this->request->getVar('defMale', 0);
            $cfg->defaults[1]->DEFAULT_MASK = $this->request->getVar('defMale', 0);
            $cfg->defaults[0]->DEFAULT_GROUP = $this->request->getVar('defMaleGroup', 0);
            $cfg->defaults[1]->DEFAULT_GROUP = $this->request->getVar('defMaleGroup', 0);
            
            $_SESSION['jsonData'] = $this->request->getVar('jsonField', '');
            $templates  = json_decode($this->request->getVar('jsonData', false));
            
            $cfg->templates = array_merge($cfg->templates, $templates);
            
            $file = fopen(CFGFILEPREPARE, 'w+');
            fwrite($file, json_encode($cfg));
            fclose($file);
        } else if ($this->request->getVar('apply', false)) {
            $cfgText = file_get_contents(CFGFILEPREPARE);
            $file = fopen(CFGFILE, 'w+');
            fwrite($file, $cfgText);
            fclose($file);
        }
        require($this->templatePath);
    }
    
    protected function clearPath($path, $ext='png') {
        $result = $this->fileList($path, $ext);
        foreach ($result as $key=>$filePath) unlink($filePath);
    }
    
    protected function toZIP($path, $destPath) {
        include_once(INCLUDE_PATH.'/zip.php');
        
        $filePath = $destPath.'cards_'.date('d.m.Y').'.zip';
        if (file_exists($filePath)) unlink($filePath);
        $zipfile = new zipfile();
        $zipfile->packToFile($path, $filePath, false);
        chmod($filePath, 0755);        
    }
    
    protected function fileList($path, $ext='png') {
        $list = Array();
       	
        if ($d = dir($path)) {
            while (false !== ($entry = $d->read())) {
                if (($entry == '.') || ($entry == '..')) continue;
                
                $file_path = $path.'/'.$entry;
                if (!is_dir($file_path)) {
                    $aExt = explode('.', $entry);
                    if ($aExt[1] == $ext) $list[] = $file_path;
                }
            }
            $d->close();
        }
        return $list;
    }

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
        
    /*    $size = array(imagesx($result), imagesy($result));
        
        $scale = min($previewSize[0]/$size[0], $previewSize[0]/$size[1]);
        $newsize = array(round($size[0] * $scale), round($size[1] * $scale));
    
        $preview = imagecreatetruecolor($newsize[0], $newsize[1]);
        imagefilledrectangle($preview, 0, 0, $newsize[0], $newsize[1], imagecolorallocate($preview, 255, 255, 255));
        imagecopyresampled($preview, $result, 0, 0, 0, 0, $newsize[0], $newsize[1], $size[0], $size[1]);*/
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
/*    
    protected function convertToJPG($filePath, $destPath, $destPreviewPath, $quality, $maskQuality, $previewSize, $resultSize) {
        $result = array();
        $this->checkDir($destPath);
        $this->checkDir($destPreviewPath);
        $result['alpha'] = $this->saveAlphaChannel($filePath, $destPath, $maskQuality, $resultSize);
        $result['image'] = $this->saveJPG($filePath, $destPath, $destPreviewPath, $quality, $previewSize, $resultSize);
        return $result;
    }
*/        
}
?>