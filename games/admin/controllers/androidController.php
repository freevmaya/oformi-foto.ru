<?

include_once(CONTROLLERS_PATH.'stateController/config.php');
include_once(INCLUDE_PATH.'/GCMPushMessage.php');
include_once(INCLUDE_PATH.'/_edbu2.php');

$charset    = 'utf8';

class androidController extends controller {
    public function display() {
        require(TEMPLATES_PATH.'androidDisplay.html');
    } 

    public function sendGCM() {
        GLOBAL $mysqli, $_SERVER;
        
        include_once('/home/android_config.php');
        
        $myIP = $_SERVER['REMOTE_ADDR'];
        $admin_only = $this->request->getVar('admin_only', false);
        
        $appIndex   = $this->svar('appIndex', 0); 
        $admin_key = $this->request->getVar('admin_key', $config[$appIndex]['adminKey']);
        $apiKey = $config[$appIndex]['apiKey'];
        $table = $config[$appIndex]['table'];
        
        $limit = $this->svar('limit', '0, 999');
        $mode = $this->svar('mode', 'app');
        $title = $this->svar('title', GCMTITLE);
        $message = $this->svar('message', '');
        $lang = $this->svar('lang', '');
        $waitMls = $this->svar('waitMls', 0);    
        $id_message = $this->svar('id_message', 0);
        $last = $this->request->getVar('last', "-3 DAY");        
        $last_date  = date("Y-m-d", strtotime($last));
        $admins = DB::asArray("SELECT * FROM {$table} WHERE ip='{$myIP}' ORDER BY `lastDate` DESC");
        $holidays = DB::asArray("SELECT h.holiday_id, name, deftml_id, DATE_FORMAT(`date`, '%d.%b') as `fdate`, (SELECT COUNT(tmpl_id) FROM gpj_holidayTmpls ht WHERE ht.holiday_id = h.holiday_id) AS tmplCount FROM gpj_holiday h ORDER BY `date`");
        
        if ($this->request->getVar('send', false) && ($message > '')) {
        
            if ($admin_only) {
                $devices = array($admin_key);
            } else {
                $query = "SELECT GCM_ID FROM $table WHERE GCM_ID > '' AND lastDate < '{$last_date}' AND lang LIKE '{$lang}' LIMIT {$limit}";
                $list = DB::asArray($query);
                $devices = array();
                foreach ($list as $item) $devices[] = $item['GCM_ID'];
                $al = explode(',', $limit);
                $limit = ($al[0] + $al[1] + 1).', '.trim($al[1]);
            }
        
            if (count($devices) > 0) {
                $gcpm = new GCMPushMessage($apiKey);
                $gcpm->setDevices($devices);
                $params = array('title'=>$title, 'id'=>$id_message, 'mode'=>$mode);
                $response = $gcpm->send($message, $params);
            }
        }
        require(TEMPLATES_PATH.'android_sendGCM.html');
    } 

    public function autoGCM_list() {
        GLOBAL $mysqli, $_SERVER;
        
        include_once('/home/android_config.php');
        
        
        $dialog_mode = $this->request->getVar('dialog_mode', 'list');
        $myIP = $_SERVER['REMOTE_ADDR'];
        
        $appIndex   = 0;
        $admin_key  = $this->request->getVar('admin_key', $config[$appIndex]['adminKey']);
        $apiKey     = $config[$appIndex]['apiKey'];
        $table      = $config[$appIndex]['table'];
        
        $admin_only = $this->request->getVar('admin_only', false);
        $start_hour = $this->request->getVar('start_hour', '15');
        $end_hour = $this->request->getVar('end_hour', '21');
        $active = $this->request->getVar('active', false)?1:0;
        
        $params = $this->svar('params', '');
        $mode = $this->svar('mode', 'app');   
        $title = $this->svar('title', GCMTITLE);
        $message = $this->svar('message', '');
        $date = $this->svar('date', date('d.m'));
        $lang = $this->svar('lang', 'ru');
        $last = $this->request->getVar('last', "2");        
        $table = $mysqli->real_escape_string($this->svar('table', 'pjad_users'));
        
        $isEdit = $dialog_mode == 'edit_form';
        
        if ($dialog_mode == 'add_notify') {
            $options = '';
            if ($notify_id = $this->request->getVar('notify_id', 0)) {
                $r_options = DB::line("SELECT * FROM pjad_notify WHERE notify_id={$notify_id}");
                $options = $r_options['options']; 
            }                                                                 
            $admin_key_data = $admin_only?$admin_key:'';
            $values = "{$active}, '{$admin_key_data}', '{$date}', {$start_hour}, {$end_hour}, '{$mode}', '{$params}', '{$options}', {$last}, '{$lang}', '{$title}', '{$message}'";
            $fields = 'active, admin_key, `date`, start_hour, end_hour, mode, params, options, last_time, lang, title, `text`';
            if ($notify_id)
                $query = "REPLACE INTO pjad_notify (`notify_id`, $fields) VALUES ($notify_id, $values)";
            else $query = "INSERT INTO pjad_notify ($fields) VALUES ($values)";
            
            DB::query($query);
            $list = DB::asArray("SELECT * FROM pjad_notify ORDER BY `date` DESC"); 
        } else if ($isEdit || ($dialog_mode == 'add_form')) {
            if ($isEdit && ($notify_id = $this->request->getVar('notify_id', 0))) {
                $notify = DB::line("SELECT * FROM pjad_notify WHERE `notify_id`={$notify_id}");
                $admin_only=$notify['admin_key']>'';
                $active=$notify['active'];  
                $params=$notify['params'];
                $title=$notify['title'];
                $message=$notify['text'];
                $date=$notify['date'];
                $start_hour=$notify['start_hour'];
                $end_hour=$notify['end_hour'];
                $lang=$notify['lang'];
                $last=$notify['last_time'];
                $mode=$notify['mode'];
            }
            $admins = DB::asArray("SELECT * FROM {$table} WHERE ip='{$myIP}' ORDER BY `lastDate` DESC");
            $holidays = DB::asArray("SELECT h.holiday_id, name, deftml_id, DATE_FORMAT(`date`, '%d.%b') as `fdate`, (SELECT COUNT(tmpl_id) FROM gpj_holidayTmpls ht WHERE ht.holiday_id = h.holiday_id) AS tmplCount FROM gpj_holiday h ORDER BY `date`");
        } else if ($dialog_mode == 'list') {
            $list = DB::asArray("SELECT * FROM pjad_notify ORDER BY `date` DESC"); 
        }  else if ($dialog_mode == 'delete') {
            if ($notify_id = $this->request->getVar('notify_id', 0)) {
                DB::query("DELETE FROM pjad_notify WHERE notify_id={$notify_id}");
            };
            $list = DB::asArray("SELECT * FROM pjad_notify ORDER BY `date` DESC"); 
        }
         
        require(TEMPLATES_PATH.'android_autoGCM_list.html');
    }
}
?>