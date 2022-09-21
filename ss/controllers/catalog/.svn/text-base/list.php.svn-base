<?
    include_once(INCLUDE_PATH.'/_edbu2.php');
    GLOBAL $catalogList, $laliase;
    
    $catalogList = null;
    $laliase = array(
        'en'=>'Eng',
        'zh'=>'Zh',
        'uk'=>'Uk'
    );
    
    function getCatalogList() {
    
        
        GLOBAL $catalogList, $laliase;
        
        if (!$catalogList) {
            $lang = ss::lang();
            $table = 'gpj_groups';
            $ptable = 'gpj_parts';
            
            if (isset($laliase[$lang])) {
                $table .= $laliase[$lang];
                $ptable .= $laliase[$lang];
            }
        
            $list = DB::asArray('SELECT g.*, gt.name AS translit, p.name as part 
            FROM '.$table.' g INNER JOIN gpj_groupsTrans gt ON g.group_id = gt.group_id INNER JOIN '.$ptable.' p ON g.part_id=p.part_id 
            WHERE g.group_id>0 AND p.visible = 1 ORDER BY p.sort');
            $catalogList = array();
            foreach ($list as $item) {
                $item['small_desc'] = stripcslashes($item['small_desc']); 
                $catalogList[] = $item;
            }
        }
        return $catalogList;
    }
    
    function catalogFromMenu() {
        GLOBAL $root;
        $list = getCatalogList();
        $result = array();
        foreach ($list as $item) {
            $result[link::c('catalog', urlencode($item['translit']))] = $item['name'];
        }
        return $result;
    }
    
    function defaultCat() {
        GLOBAL $laliase;
        $table = 'gpj_groups';
        $lang = ss::lang();
        
        if (isset($laliase[$lang])) $table .= $laliase[$lang];
        return DB::line("SELECT * FROM {$table} WHERE group_id=0");
    }
?>