<?
    include_once(dirname(__FILE__).'/base_model.php');
     
    class dataModel extends base_model {         
        protected function getList() {
            $tmpl_id = $this->getVar('tmpl_id', '0');
            return DB::asArray("SELECT tmpl_id, save_rate, user_rate FROM gpj_tmplOptions WHERE tmpl_id IN ({$tmpl_id})");
        }
    }
?>    