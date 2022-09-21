<?
define('BLOCKSIZE', 1024);

class downloadController extends controller {
    protected $param;
    
    public function data() {
        GLOBAL $_SESSION;

        set_time_limit(0);

        if (isset(ss::$task[2]) && ($query = ss::$task[2])) {
            if ($_SESSION['zip-file'] == $query) {
                $link_a = explode('.', $query);
                $pref = $link_a[0];

                $file = fopen(HOMEPATH.'/zip-templates/'.$pref.'.oformi-foto.ru.zip', "r");
                while (!feof($file)) {
                    $data = fread($file, BLOCKSIZE);
                    echo $data;
                }
                fclose($file);
            } else echo "Unknown link";
        }

        ss::setTemplate('data.html');
    }
}    
?>