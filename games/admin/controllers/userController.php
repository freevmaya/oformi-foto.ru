<?

class userController extends controller {
    public function login() {
        GLOBAL $_SESSION;
        if ($login = $this->request->getVar('login')) {
            $pass = strtoupper(md5($this->request->getVar('password')));
            
            $line = DB::line("SELECT uid, login, type, last_time, percent FROM pj_admin WHERE `login`='{$login}' AND `pass`='$pass'");
            
            if ($line) {
                DB::query("UPDATE pj_admin SET `last_time`=NOW() WHERE `login`='{$line['login']}'");
                $_SESSION['user'] = $line['login'];
                $_SESSION['user-data'] = $line; 
                $this->loginSuccess();
                return;
            }
        } else if (isset($_SESSION['user'])) {
            $this->loginSuccess();
            return;
        }
        
        require_once TEMPLATES_PATH.'loginForm.html';
    }
    
    public function loginSuccess() {
        //$this->redirect('sys', 'sysInfo');
        Admin::getInstance()->resetUser(); 
        Admin::toDefault();
    }
    
    public function logout() {
        unset($_SESSION['user']);
        Admin::getInstance()->redirect('user', 'login');
    }
}

?>