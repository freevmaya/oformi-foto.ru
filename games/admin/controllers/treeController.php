<?

include_once(INCLUDE_PATH.'/_edbu2.php');

GLOBAL $dbname;

$dbname = "_tree";

class treeController extends controller {
    public function rods() {
        $list = DB::asArray("SELECT r.*, COUNT(p.people_id) AS count_people FROM `t_rod` r INNER JOIN t_peoples p ON p.rod_id = r.rod_id GROUP BY r.rod_id ORDER BY count_people DESC LIMIT 0, 20");
        require($this->templatePath);
    }
    
    public function mm_rods() {
        GLOBAL $dbname;
        $dbname = "_tree_mm";
        $list = DB::asArray("SELECT r.*, COUNT(p.people_id) AS count_people FROM `t_rod` r INNER JOIN t_peoples p ON p.rod_id = r.rod_id GROUP BY r.rod_id ORDER BY count_people DESC LIMIT 0, 20");
        require($this->templatePath);
    }
}

?>