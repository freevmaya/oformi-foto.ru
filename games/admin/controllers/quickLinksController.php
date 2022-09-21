<?
define('COUNTPERPAGE', 30);
include_once(INCLUDE_PATH.'/_edbu2.php');

class quickLinksController extends controller {
    protected function state_delete() {
        return DB::query('DELETE FROM `gpj_quicklinks` WHERE `id`='.$this->request->getVar('id', 0));
    }
    
    protected function state_new() {
        return array('name'=>'Новая ссылка', 'title'=>'Тайтл новой ссылки', 'url'=>'new_content.html');
    } 
    
    protected function state_edit() {
        return DB::line("SELECT * FROM `gpj_quicklinks` WHERE id=".$this->request->getVar('id', 0));
    } 
    
    protected function state_update() {
        GLOBAL $mysqli;
    
        $id = $this->request->getVar('id', false);
        if (($name = $mysqli->real_escape_string($this->request->getVar('name', ''))) &&
            ($title = $mysqli->real_escape_string($this->request->getVar('title', ''))) && 
            ($url = $mysqli->real_escape_string($this->request->getVar('url', '')))) { 
            if (!$id) $query = "INSERT INTO `gpj_quicklinks` (`name`, `title`, `url`) VALUES ('{$name}', '{$title}', '{$url}')";
            else $query = "UPDATE `gpj_quicklinks` SET `name`='{$name}', `title`='{$title}', `url`='{$url}' WHERE `id`={$id}";
            return DB::query($query);
        } else return false;
    } 
    
    protected function blocks_state_delete() {
        return DB::query('DELETE FROM `gpj_quicklinks_group` WHERE `id`='.$this->request->getVar('id', 0));
    }
    
    protected function blocks_state_new() {
        return array('name'=>'new_block', 'title'=>'Новый блок');
    } 
    
    protected function blocks_state_edit() {
        return DB::line("SELECT * FROM `gpj_quicklinks_group` WHERE id=".$this->request->getVar('id', 0));
    } 
    
    protected function blocks_state_update() {
        GLOBAL  $mysqli;
        $id = $this->request->getVar('id', false);
        
        if (($name = $mysqli->real_escape_string($this->request->getVar('name', ''))) &&
            ($title = $mysqli->real_escape_string($this->request->getVar('title', '')))) {
            $description = $mysqli->real_escape_string($this->request->getVar('description', ''));
             
            if (!$id) $query = "INSERT INTO `gpj_quicklinks_group` (`name`, `title`, `description`) VALUES ('{$name}', '{$title}', '{$description}')";
            else $query = "UPDATE `gpj_quicklinks_group` SET `name`='{$name}', `title`='{$title}', `description`='{$description}' WHERE `id`={$id}";
            
            return DB::query($query);
        } else return false;
    } 
    
    public function view() {
        if ($state = $this->request->getVar('state', false)) {
            $method = 'state_'.$state; 
            $result = $this->$method();
        }            
    
        $page   = $this->svar('page', 1);
        $list   = DB::asArray("SELECT SQL_CALC_FOUND_ROWS * FROM gpj_quicklinks LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE);
        $count  = DB::line('SELECT FOUND_ROWS() AS `count`');
        $count  = $count['count'];
        require($this->templatePath);
    }
    
    protected function getLinksHtml($name, $link_id) {
        $result = "<select name=\"{$name}\">\n";
        $links = DB::asArray("SELECT id, name FROM gpj_quicklinks");
        foreach ($links as $link) {
            $selected = '';
            if ($link['id'] == $link_id) $selected = 'SELECTED';
            $result .= "<option value=\"{$link['id']}\" $selected>{$link['name']}</option>\n";
        }
        
        return $result."</select>\n";
    }    
    
    protected function blocks_links() {
        if ($group_id   = $this->svar('group_id', 0)) {
            if (($action = $this->request->getVar('action', false)) &&
                ($item_id = $this->request->getVar('item_id', false))) {
                if ($action == 'add') $result = DB::query("INSERT INTO gpj_quicklinks_items (`group_id`, `item_id`) VALUES ($group_id, $item_id)");
                else if ($action == 'delete') $result = DB::query("DELETE FROM gpj_quicklinks_items WHERE group_id={$group_id} AND item_id={$item_id}"); 
            }
             
            $block = DB::line('SELECT * FROM gpj_quicklinks_group WHERE id='.$group_id);
            $list = DB::asArray("SELECT * FROM gpj_quicklinks_items i INNER JOIN gpj_quicklinks l ON i.item_id=l.id WHERE group_id={$group_id}");
        }
        require($this->templatePath);
    }
    
    public function blocks() {
        if ($state = $this->request->getVar('state', false)) {
            $method = 'blocks_state_'.$state; 
            $result = $this->$method();
        }            
    
        if (($state != 'new') && ($state != 'edit')) { 
            $page   = $this->svar('page', 1);
            $list   = DB::asArray("SELECT * FROM gpj_quicklinks_group LIMIT ".(($page - 1) * COUNTPERPAGE).", ".COUNTPERPAGE);
            $count  = DB::line('SELECT FOUND_ROWS() AS `count`');
            $count  = $count['count'];
        }
        require($this->templatePath);
    }
}
?>