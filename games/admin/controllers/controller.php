<?

class controller {
    protected $request;
    protected $templatePath;
    function __construct($a_request) {
        $className = get_class($this);
        if (!isset($_SESSION[$className])) $_SESSION[$className] = $this->initSession();
        $this->request = $a_request;
    }
    
    function redirect($controllerName, $task='display') {
        if (is_array($controllerName)) {
            $task = $controllerName[1];
            $controllerName = $controllerName[0];
        }
        $controllerFile = $controllerName.'Controller';
        require_once CONTROLLERS_PATH.$controllerFile.'.php';
        $controller = new $controllerFile($this->request);
        
        $tmplFile = $controller->templatePath = TEMPLATES_PATH.$task.'.html';
        if (!file_exists($tmplFile)) $tmplFile = TEMPLATES_PATH.$controllerName.'_'.$task.'.html';
          
        $controller->templatePath = $tmplFile;
        $controller->$task();
    }
    
    protected function initSession() {
        return array();
    }
    
    public function getSession($varName) {
        global $_SESSION;
        $className = get_class($this);
        if (!isset($_SESSION[$className][$varName])) return false;
        else return $_SESSION[$className][$varName];
    }
    
    public function setSession($varName, $value) {
        global $_SESSION;
        $className = get_class($this);
        $_SESSION[$className][$varName] = $value;
    }
    
    public function svar($varName, $default=0) {
        global $_SESSION;
        $className = get_class($this);
        $_SESSION[$className][$varName] = $this->request->getVar($varName, 
                                            isset($_SESSION[$className][$varName])?$_SESSION[$className][$varName]:$default);
        return $_SESSION[$className][$varName];
    }
    
    
    public function html_uidInput($defaultUid = 0) {
        global $_SESSION;
        if (!isset($_SESSION['uids'])) $_SESSION['uids'] = array();
        $uid = $this->svar('uid', $defaultUid);
        $_SESSION['uids'][$uid] = $uid;
        $uids = '<option value="0">---</option>\n';
        foreach ($_SESSION['uids'] as $l_uid) {
           $uids .= "<option value=\"{$l_uid}\" ".($uid==$l_uid?'checked':'').">{$l_uid}</option>\n"; 
        }
        return '<input name="uid" value="'.$uid.'" size="20" id="uid">
                <select name="uids" style="width:200px" onchange="document.getElementById(\'uid\').value = this.value;">
                    '.$uids.'
                </select>';
    }
    
    public function showTable($table, $refs=null) {
        $result = '';
        if (count($table) > 0) {
            $fields = array_keys($table[0]);
            $result = '<table class="report"><tr>';
            foreach ($fields as $field) {
                $result .= "<th>$field</th>";
            }
            $result .= "</tr>";
                    
            foreach ($table as $num=>$row) {
                $class = ($num % 2 == 0)?' class="odd"':'';
                $result .= "<tr{$class}>";
                foreach ($row as $field=>$item) {
                    if ($refs && isset($refs[$field]) && isset($refs[$field][$item])) {
                        $item .= '-'.$refs[$field][$item];
                    }
                    $result .= "<td>$item</td>";
                }
                $result .= '</tr>';
            }
            
            $result .= '</table>';
        }        
        return $result;
    }
    
    public function inputHtml($name, $size=40, $default='') {
        return "<input type=\"text\" name=\"{$name}\" value=\"".$this->request->getVar($name, $default)."\" size=\"{$size}\">";
    }
    
    public function textEditor($name, $text) {
        echo '<div>Допустимые теги:<br>
                    [a <i>ссылка</i>]<i>текст ссылки</i>[/a], [pimg <i>ссылка</i>]<i>то что попадет в alt</i>[/pimg], [img <i>ссылка</i>]<i>то что попадет в alt</i>[/img], [module <i>имя файла</i>][/module]
                    </div>
                    <textarea style="width:800px;height:300px" name="'.$name.'" id="'.$name.'">'.$text.'</textarea>';
        echo '<script type="text/javascript">tinymce.init({selector:"#'.$name.'", width : 800});</script>';
    }  
    
    public static function translit($string, $revers=false) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'yo',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'j',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'\'',
            'э' => 'e\'',   'ю' => 'yu',  'я' => 'ya',
            
            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'Yo',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'J',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '-',  'Ы' => 'Y',   'Ъ' => '-',
            'Э' => 'E\'',   'Ю' => 'Yu',  'Я' => 'Ya'
        );
        
        $string = strtr($string, $revers?array_flip($converter):$converter);
        if (!$revers)
            $string = preg_replace('~[^-A-z0-9\'_]+~u', '', $string);
        return $string;
    }     
}

?>