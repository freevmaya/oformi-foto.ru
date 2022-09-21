<?
GLOBAL $sheme, $root;

define('CACHEEXPIRED', 60 * 60 * 8); // 8 часов
if (ss::$isPhone)  define('ARTICLESPATH', SSPATH.'articles/mobile/');
else define('ARTICLESPATH', SSPATH.'articles/'.$root->lang().'/');
define('ARTICLESURL', SSURL.'articles/');

include_once(SSPATH.'/helpers/templates.php');

class articleController extends controller {
    protected $other_themes = false;
    protected $social_block = true; 
    protected $article_footer = false;      
   
    public function viewArticle() {
        GLOBAL $mysqli;
        include_once(SSPATH.'redirect.php');
        
        
        $page = trim(ss::$task[2]);
        if ($page) {
            $keys = array_keys($redirectList);
            $index = -1;
            foreach ($keys as $i=>$key)
                if (strcasecmp($key, $page) == 0) {
                    $index = $i; 
                    break;
                }
                
            if ($index > -1) {
                ss::$task = explode(',', $redirectList[$keys[$index]]);
                $controller = $this->redirect($this->createController(ss::$task[0]), ss::$task[1]);
            } else {
                $templateName = str_replace('-', ' ', $page);
                
                $fileName = ARTICLESPATH.$templateName.'.html';
                
                if (!file_exists($fileName)) {
                    if (!$article = t($page)) {
                        $mysql_page = mysqli_escape_string($mysqli, $page);
                        $count = DB::line("SELECT `count` FROM `seo_noarticle_page` WHERE `page`='{$mysql_page}'");
                        $count = $count?($count['count'] + 1):1;
                        $page_ru = mb_ucfirst(controller::translit($templateName, true));
                        
                        DB::query("REPLACE `seo_noarticle_page` VALUES ('{$mysql_page}', $count)");
                        
                        $rpage_ru = resortWords($page_ru);
                        $fileName = TEMPLATES_PATH.(ss::$isPhone?'mobile/':'').'default.html';
                        
                        if ($words = words($page_ru, 4)) {
                            
                            $this->title = $page_ru;
                            $this->description = $rpage_ru;
                            
                            $tmpls = array();
                            $wordsWhere = '';
                            foreach ($words as $word) {
                                $wordsWhere .= ($wordsWhere?' OR ':'')."`name` LIKE '%$word%'";
                            }
                            $tmpls = DB::asArray("SELECT * FROM `gpj_tmplOptions` WHERE $wordsWhere GROUP BY `tmpl_id` ORDER BY `tmpl_id` DESC LIMIT 0,4");
                            if (count($tmpls) == 0)
                                $tmpls = DB::asArray("SELECT * FROM `gpj_tmplOptions` WHERE `name`>'' ORDER BY `tmpl_id` DESC LIMIT 0,4");
                            
                            $keywords = $words;
                        }
                    }
        /*            
                    $cacheKey = 'other_articles';
                    if (!$otherPages = ss::getCache($cacheKey)) {
                        $otherPages = otherArticles();
                        ss::setCache($cacheKey, $otherPages, $this->cacheExpire());
                    }
        */            
                }            
                                           
                require($this->templatePath);
            }
        } else {
            $fileName = TEMPLATES_PATH.(ss::$isPhone?'mobile/':'').'default.html';
            require($this->templatePath);
        } 
    }
    
    public function cacheKey() {
        return ss::cacheKeyDefault();
    }
    
    public function isCached() {
        return !ss::$isAdmin;
    }   
    
    public function cacheExpire() {
        return CACHEEXPIRED;        
    }
}    
?>