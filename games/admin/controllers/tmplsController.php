<?
GLOBAL $dbname, $charset;

$charset = 'utf8';
$dbname = '_clothing';
include_once(INCLUDE_PATH.'/_edbu2.php');

class tmplsController extends controller {

    public function Active() {
        if ($other = $this->svar('other')) {
            $query = "SELECT * FROM `clt_templates` WHERE `other`='$other' AND `modifyTime`>='".$this->svar('startDate')."' AND `modifyTime`<='".$this->svar('endDate')."' AND checked=1";
            $list = DB::asArray($query);
        }
        require($this->templatePath);
    }

    public function noActive() {
        $where      = 'checked=0';
        //$where      = "modifyTime>='2011-09-25 00:00:00'";
        $clothings  = DB::asArray("SELECT * FROM `clt_templates` WHERE type='c' AND $where");
        $hairs      = DB::asArray("SELECT id,ears,`group` FROM `clt_templates` WHERE type='h' AND $where");
        require($this->templatePath);
    }
    
    public function accept() {
        DB::query('UPDATE `clt_templates` SET `checked`=1 WHERE checked=0');
        require($this->templatePath);
    }
}
?>