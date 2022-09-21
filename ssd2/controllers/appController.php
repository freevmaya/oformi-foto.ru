<?
include_once(SSPATH.'/helpers/templates.php');
include_once(INCLUDE_PATH.'/image.php');

class appController extends controller {
    protected $param;
    
    function __construct($a_request) {
        parent::__construct($a_request);
        if (isset(ss::$task[1])) {
            $a = explode('-', ss::$task[1]);
            ss::$task[1] = $a[0];
            if (isset($a[1])) $this->param = $a[1]; 
        } 
        if (isset(ss::$task[2])) ss::setTemplate(ss::$task[2].'.html');
    }
    
    protected function createMeta() {
        return array('page-image'=>MAINURL.'/images/sshot01.jpg'); 
    }    
    
    public function view() {
        $isdev      = $this->request->getVar('dev', false);
        if ($isdev) $basePath   = 'pj/dev';
        else $basePath   = 'pj/v20';
        $lfashPATH = MAINPATH.'/'.$basePath.'/pj_free.swf';
        $ver        = ($isdev || !file_exists($lfashPATH))?rand(10, 1000000):filectime(MAINPATH.'/'.$basePath.'/pj_free.swf');
        $pluginVer  = 'v05';
        $plugins    = array(
            'free'=>MAINURL.'/pj/plugins/'.($isdev?'dev/free/':($pluginVer.'/free/')),
            'mail'=>MAINURL.'/pj/plugins/'.($isdev?'dev/free/':($pluginVer.'/mail/'))
        );
        
        if ($tmpl_id = $this->request->getVar('tid', false)) {
            $tmpl = $this->getTemplate($tmpl_id);
            $this->meta['page-image'] = $tmpl['preview'];     
            
            if (isset($tmpl['name'])) $this->title = $tmpl['name'];        
            if (isset($tmpl['desc'])) $this->description = $tmpl['desc'];        
        }
                
        require($this->templatePath);
    }       
    
    public function viewDEV() {
        require($this->templatePath);
    }                      

    public function clothing() {
        $isdev = $this->request->getVar('dev', false);
        if ($isdev) $basePath   = 'clothing/dev';
        else $basePath   = 'clothing/com/v10';
        $ver        = filectime(MAINPATH.'/'.$basePath.'/free_clothing.swf');
        $this->groupID = '54129139712019';
        require($this->templatePath);
    }                          

    public function tuse() {
        $isdev = $this->request->getVar('dev', false);
        if ($isdev) $basePath   = 'clothing/dev';
        else $basePath   = 'clothing/com/v09';
        
        ss::$noadv = true;
        $flashSWF   = 'tuse.swf'; 
        $ver        = filectime(MAINPATH.'/'.$basePath.'/'.$flashSWF);
        $this->groupID = '54129139712019';
        require($this->templatePath);
    }
    
    public function collages() {
        require($this->templatePath);
    }
    
    public function clothingDEV() {
        $isdev  = $this->request->getVar('dev', false);
        require($this->templatePath);
    }
    
    public function coloring() {
        require($this->templatePath);
    }
    
    public function tree() {
        require($this->templatePath);
    }
    
    public function gifavt() {
        GLOBAL $locale;
        if (!$this->param) {
            $isdev  = $this->request->getVar('dev', false);
            if ($isdev) {
                $basePath   = 'gifavt/dev';
                $ver        = rand(0, 10000000);
            } else {
                $basePath   = 'gifavt/com/v02';
                $ver        = filectime(MAINPATH.'/'.$basePath.'/of_gifavt.swf');
            }
            require($this->templatePath);
        } else {
            $this->title = $locale['GIFFILETITLE'];
            $applink = link::c('app', 'gifavt');
            if (is_numeric($this->param)) {
                $image      = DB::line("SELECT * FROM ".DBPREF."gif WHERE gif_id={$this->param}");
                $fb_width   = 486;
                
    
                $previewPATH = GAMEPATH.'gifs/'.$fb_width;
                $previewURL = GAMEURL.'gifs/'.$fb_width;
                            
                $fid        = 'a'.$image['gif_id'];
                $filePATH   = GAMEPATH.'gifs/'.$fid.'.gif';
                $prevPATH   = $previewPATH.'/'.$fid.'.jpg'; 
                $prevURL    = $previewURL.'/'.$fid.'.jpg';
                $fileURL    = GAMEURL.'gifs/'.$fid.'.gif';
                
                $MAINURL_SSL = str_replace('http:', 'https:', MAINURL);
                    
                if (file_exists($filePATH)) {
                    if (!file_exists($previewPATH)) {
                        mkdir($previewPATH);
                        chmod($previewPATH, 0775);
                    }
                
                    if (!file_exists($prevPATH)) {
                        $img = imagecreatefromgif($filePATH);
                        $img_desc = imagecreatetruecolor($fb_width, $image['height']);
                        $color = imagecolorallocate($img_desc, 255, 255, 255);
                        imagefill($img_desc, 0, 0, $color); 
                        imagecopy($img_desc, $img, round(($fb_width - $image['height']) / 2), 0, 0, 0, $image['width'], $image['height']);
                        imagejpeg($img_desc, $prevPATH);
    					imagedestroy($img);                        
                    }
                    $this->title = $locale['GIFFILETITLE'];
                    $this->description = $locale['GIFFILEDESC'];
                    
                    $videoURL = $MAINURL_SSL.'/gifavt/dev/of_view.swf?fileURL=//oformi-foto.ru/images/game/gifs/'.$fid.'.gif&width='.$fb_width.'&height='.$image['height'];
                    
                    $this->addMeta('page-image', $fileURL);
                    $this->og['image'] = $prevURL;
                    $this->og['title'] = $this->title;
                    
                    $this->og['type'] = 'video'; 
                             
                    $this->og['image:width'] = $fb_width;
                    $this->og['image:height'] = $image['height'];
                    
                    $this->og['video'] = $videoURL;
                    $this->og['video:type'] = 'video.other';
                    $this->og['video:width'] = $fb_width;
                    $this->og['video:height'] = $image['height'];
                    $this->og['video:url'] = $videoURL;
                    $this->og['video:secure_url'] = $videoURL;
                    
                    require(TEMPLATES_PATH.'/app/gifavt-view.html');
                    
                } else include_once(TEMPLATES_PATH.'default.html');
            } else {
                $size = getimagesize(GAMEPATH."gifs/{$this->param}.gif");
                $fileURL = GAMEURL."gifs/{$this->param}.gif";
                $this->og['image'] = $fileURL;
                $this->og['title'] = $this->title;
                $this->og['image:width'] = $size[0];
                $this->og['image:height'] = $size[1];
                require(TEMPLATES_PATH.'/app/gifavt-view.html');
            }            
        }
    }
    
    public function pixlr() {
        require($this->templatePath);
    }
    
    public function phone() {
        require($this->templatePath);
    }
    
    public function pjjs() {
        //ss::$noadv = true;
        require($this->templatePath);
    }
    
    public function gapi_pj() {
        require($this->templatePath);
    }
    
    public function getTemplate($tmpl_id) {
        GLOBAL $sheme;
        $result = array('image'=>FRAMES_URL.$tmpl_id.'.jpg', 'preview'=>FRAMES_URLPREVIEW.$tmpl_id.'.jpg', 'medium'=>FRAMES_URLPREVIEWMED.$tmpl_id.'.jpg');
        
        //if (!ss::$isPhone && ss::isHavePNG($tmpl_id)) $result['image'] = sprintf(FRAMES_PNGURL, $tmpl_id);
        
        if ($info = DB::line("SELECT * FROM gpj_tmplOptions WHERE tmpl_id={$tmpl_id}")) {
            $result = array_merge($result, $info);
            $result['info'] = appController::templateInfo($result);
        }            
        
        return $result; 
    }
    
    public function pjm() {
        $this->redirect($this, 'anim');
    }
    
    public function anim() {
        require($this->templatePath);
    }
    
    public static function templateInfo($item) {
        GLOBAL $locale;
        $info = '';
        if ($item['width']) $info .= "{$locale['SIZE']}: <strong itemprop=\"width\">{$item['width']}</strong>x<strong itemprop=\"height\">{$item['height']}</strong>";
        if ($date = $item['date']) $info .= ($info?', ':'')."{$locale['ADDDATE']}: $date";
        $rate = $item['save_rate'] + $item['user_rate'];
        
                
        return $info.($info?', ':'').$locale['RATE'].': '.$rate;
    }
}    
?>