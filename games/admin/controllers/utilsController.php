<?

include_once(INCLUDE_PATH.'/_edbu2.php');
include_once(CONTROLLERS_PATH.'utilsController/config.php');

define('COMPACT_SERVICE', 2005);
define('TEMPLATES_URL', 'http://oformi-foto.ru/pj/check_template.php');

class utilsController extends controller {
    public function optimize() {
        include_once(CONTROLLERS_PATH.'utilsController/tables.php');
        
        if ($cht = $this->request->getVar('cht')) {
            $strTables = implode(',', $cht);
            $result = DB::asArray("OPTIMIZE TABLE $strTables");
        }
        require($this->templatePath);
    }
    
    public function userInfo() {
        $infoList = query_array('SELECT * FROM gpj_userInfo');
        require($this->templatePath);
    }
    
    public function userBrowser() {
        if (($uid = $this->request->getVar('uid', 0)) && ($app_id = $this->request->getVar('app_id', 0))) {
            $result = DB::line('SELECT * FROM ml_userInfo AS ui 
                                    WHERE `uid`=%s AND `app_id`=%s', array($uid, $app_id));
           require($this->templatePath);
        }
    }
    
    public function queryStatistic() {
        $list = DB::asArray('SELECT COUNT(`query`) AS `count`, `query`, SUM(timeCount) as timeCount FROM `query_statistic` GROUP BY `query` ORDER BY timeCount');
        $sum = 0;
        foreach ($list as $item) $sum += $item['timeCount'];
        foreach ($list as $key=>$item) $list[$key]['percent'] = $item['timeCount']/$sum * 100;
        require($this->templatePath);
    }
    
    public function cardsDelete() {
        if ($delDate = $this->request->getVar('delDate', false)) {
            $result = DB::query("DELETE FROM gpj_send WHERE sendTo IN (SELECT uid FROM gpj_options WHERE `visitDate`='0000-00-00') AND `time`<'{$delDate}' AND `received`=0;");
        }
        require($this->templatePath);
    } 
    
    public function badBrowser() {
        if ($app_id = $this->request->getVar('app_id', 0)) {
            $hour = $this->request->getVar('hour', 1);
            $time = date('Y-m-d H:i:s', strtotime("-$hour HOUR"));
            $result = DB::asArray("SELECT COUNT(uid) as badCount, `info`
                                    FROM ml_userInfo AS ui 
                                    WHERE `time`>='%s' AND `app_id`=%s
                                    GROUP BY `app_id`, `info`", array($time, $app_id));
            
            foreach ($result as $key=>$item) {
                $a_b = explode(' (', $item);
                $result[$key]['browser'] = $a_b[0]; 
            }
            
            $order = $this->request->getVar('order', 'A');
            function cmpB($a, $b) {
                if ($a['browser'] == $b['browser']) return 0;
                else return ($a['browser'] > $b['browser']) ? -1 : 1;
            }
            
            function cmpA($a, $b) {
                if ($a['badCount'] == $b['badCount']) return 0;
                else return ($a['badCount'] > $b['badCount']) ? -1 : 1;
            }
    
            usort($result, 'cmp'.$order); 
            require($this->templatePath);
        }
    }
    
    public function APIuserInfo() {
        if ($uid = $this->request->getVar('uid', 0)) {
            $result = MAILServer::request('441805', 'users.getInfo', 
                        array('uids'=>$uid));
        }
        require($this->templatePath);
    }
    
    public function getSuccess() {
        $hour = $this->request->getVar('hour', 5);
        $time = date('Y-m-d H:i:s', strtotime("-$hour HOUR"));
        $result = DB::asArray('SELECT COUNT(`uid`) AS fullCount, app_id, 
                                (SELECT COUNT(`uid`) FROM ml_userInfo WHERE `fp_v`=0 AND `app_id`=ui.`app_id` AND `time`>=\'%1$s\') AS badCount,
                                (SELECT COUNT(`uid`) FROM ml_userInfo WHERE `fp_v`>0 AND `app_id`=ui.`app_id` AND `time`>=\'%1$s\') AS sucCount 
                              FROM ml_userInfo AS ui
                              WHERE `time`>=\'%1$s\'
                              GROUP BY `app_id`', array($time));
        require($this->templatePath);                                
    }
    
    public function compactPay() {
        include(MODEL_PATH.'pj_model_ok/config.php');
        $cpdate = $this->svar('cpdate', date('d.m.Y 00:00:00', strtotime('-5 DAY')));
        $cptable = $this->svar('cptable', DEFAULTTRANSTABLE);
        $cpstart = $this->svar('cpstart', 0);
        $cpcount = $this->svar('cpcount', 20);
        $mysql_date = date('Y-m-d H:i:s', strtotime($cpdate));
        $unixdate   = strtotime($cpdate);
        $a_uids     = $this->svar('uids', false);
        
        $svWhere    = '';
        if ($str_services   = $this->svar('services', implode(',', $services))) {
            $a_services = explode(',', $str_services);
            foreach ($a_services as $id) $svWhere .= ($svWhere?' OR ':'')."service_id={$id}";
        }
        
        if ($this->request->getVar('begin', 0)) {
            $where = "createDate>'0000-00-00'";
            $uidsWhere = '';
            if ($a_uids) {
                $aa_uids = explode(',', $a_uids);
                $uidsWhere = '';
                foreach ($aa_uids as $uid) $uidsWhere .= ($uidsWhere?' OR ':'')."uid={$uid}";
                
                $where .= ' AND '.$uidsWhere;
            }
            
            
            $a_svWhere = $svWhere?(' AND ('.$svWhere.')'):'';
            
            //SELECT uid, (SELECT COUNT(`transaction_id`) FROM pjok_transaction WHERE UNIX_TIMESTAMP(`time`)<='1459458000' AND user_id=uid GROUP BY user_id) AS `count` FROM pjok_options LIMIT 0, 20
            $query = "SELECT uid, (SELECT COUNT(`transaction_id`) FROM {$cptable} WHERE UNIX_TIMESTAMP(`time`)<='{$unixdate}' AND user_id=uid $a_svWhere GROUP BY user_id) AS `count`, 
                                    (SELECT SUM(`price`) FROM {$cptable} WHERE UNIX_TIMESTAMP(`time`)<='{$unixdate}' AND user_id=uid $a_svWhere GROUP BY user_id) AS `sum`
                     FROM pjok_options WHERE $where
                     LIMIT {$cpstart}, {$cpcount}";
            echo $query;
            $list = DB::asArray($query);                                                                              
            if ($this->request->getVar('compact', 0)) {
                $uids   = array();
                foreach ($list as $user) {
                    if ($user['count'] > 1) {
                        if ($user['sum'] < 5000)// $user['sum'] = round($user['sum'] / 100);   // 
                            $uids[] = array('uid'=>$user['uid'], 'sum'=>$user['sum']);
                    }    
                }               
                $result = 1;
            }
        } else if ($this->request->getVar('compact', 0)) {
            $uids = $this->request->getVar('uid', array());
            foreach ($uids as $key=>$user) {                    
                $uids[$key] = array('uid'=>$user, 'sum'=>$this->request->getVar('sum_'.$user, 0));
            }               
            $result = 1;
        }

        if (isset($uids) && (count($uids) > 0)) {
            $where = '';
            $values = '';
                                                                                     
            foreach ($uids as $user) {
                $where .= ($where?' OR ':'')."user_id='{$user['uid']}'";
                if (($sum = $user['sum']) != 0)
                    $values .= ($values?',':'')."('$mysql_date', '{$user['uid']}', ".COMPACT_SERVICE.", $sum, 'convolution')";
            }
            
            
            if ($where) {
                if ($svWhere) $where .= ' AND ('.$svWhere.')';
                $delQuery = 'DELETE FROM %s WHERE (%s) AND `time`<=\'%s\'';
            }
            if ($values) $inserQuery = "INSERT INTO %s (`time`, `user_id`, `service_id`, `price`, `params`) VALUES %s";
            
            $result = 1;
            tables_lock(array($cptable));
            if ($where) $result = DB::query($delQuery, array($cptable, $where, $mysql_date)) & $result;
            if ($values) $result = DB::query($inserQuery, array($cptable, $values)) & $result;
            tables_unlock();
            if ($result) $cpstart += $cpcount;
            
        } else $cpstart += $cpcount;
        
        require($this->templatePath);                                
    }
    
    public function compactOkMoney() {
        $start = $this->svar('start', 0);
        $count = $this->svar('count', 1000);
        $list = array();
        if ($this->request->getVar('begin', 0)) {    
//            $list = DB::asArray("SELECT COUNT(transaction_id) AS `count`, SUM(price) AS balance, user_id FROM `pjok_transaction` WHERE service_id=1 GROUP BY user_id LIMIT $start, $count");

            $users = DB::asArray("SELECT user_id FROM `pjok_transaction` GROUP BY user_id LIMIT $start, $count");
            foreach ($users as $user) { 
                $tact = DB::line("SELECT COUNT(transaction_id) AS `count`, SUM(price) AS balance, user_id FROM `pjok_transaction` WHERE service_id=1 AND user_id={$user['user_id']}");
                if ($tact['count'] > 1) {
                    $list[] = $tact;
                    
                    $delQuery = "DELETE FROM `pjok_transaction` WHERE user_id={$user['user_id']} AND `service_id`=1";
                    $inserQuery = "INSERT INTO `pjok_transaction` (`time`, `user_id`, `service_id`, `price`, `params`) VALUES (NOW(), {$user['user_id']}, 1, {$tact['balance']}, 'convolution')";
                    
                    $result = true;
                    tables_lock('pjok_transaction');
                    $result = DB::query($delQuery) & $result;
                    $result = DB::query($inserQuery) & $result;
                    tables_unlock();
                }
            }
            
            $start += $count;
        }
        require($this->templatePath);          
    }
    
    public function checkTmpls() {
        include_once('models/pj_model/config.php');
        
        
        $startNum   = $this->request->getVar('startNum', 0);
        $count      = $this->request->getVar('count', 20);
        if ($this->request->getVar('proccess', 0)) {
            $query = "SELECT * FROM gpj_templates GROUP BY tmpl_id LIMIT $startNum, $count";
            $list = array();
            $items = DB::asArray($query);
            foreach ($items as $item) {
                $strResult = file_get_contents(TEMPLATES_URL.'?tmpl_id='.$item['tmpl_id']);
                $result = explode(',', $strResult);
                if (count($result) == 3) {
                    $item['result'] = $strResult;
                    if (!($result[0] && $result[1] && $result[2])) {
                        $list[] = $item;
                    }
                }
                
            }
            
            if ($this->request->getVar('remove', 0)) {
                $where = '';
                foreach ($list as $key=>$item) {
                    $where .= ($where?' OR ':'')."tmpl_id={$item['tmpl_id']}";
                    $list[$key]['removed'] = true;
                }
                if ($where) 
                    DB::query("DELETE FROM gpj_templates WHERE $where");
            }
            $startNum += $count;
        }
        require($this->templatePath);                                
    }
    
    public function subscribe() {
        include_once(ADMINPATH.'helpers/server.php');
        include_once(ADMINPATH.'helpers/sender.php');
        $startIndex = $this->request->getVar('startIndex', 0);
        $submitType = $this->request->getVar('submitType', 'forgot');
        
        if ($table = $this->request->getVar('table')) {
            $startIndex = $this->request->getVar('startIndex', 0);
            $countUsers = $this->request->getVar('countUsers', 0);
            //$list = DB::asArray("SELECT * FROM $table LIMIT $startIndex, $countUsers");
            $list = array(array('uid'=>'1731353195984349210', 'inCount'=>2), array('uid'=>'8062938299454250872', 'inCount'=>0));
            $startIndex += $countUsers;
            $uids = '';
            foreach ($list as $item) {
                $uids .= ($uids?',':'').$item['uid']; 
            }
            
            $result = MAILServer::request('588137', 'users.getInfo', array('uids'=>$uids, 'uid'=>'8062938299454250872'));
            
            foreach ($result as $item) {
                if ($item['email']) {
                    foreach ($list as $user) {
                        if ($user['uid'] == $item['uid']) {
                            $item['inCount'] = $user['inCount']; 
                        }
                    }
                    ob_start();
                    require(TEMPLATES_PATH.'utilsController/'.$submitType.'.html');
                    $body = ob_get_contents();
                    ob_end_clean();
                    sender::socketSendMail($item['email'], $subject, $body, 'noreply@oformi-foto.ru', 'оформи фото.ру');
                    //sender::sendMail($item['email'], $subject, $body);
                }
//                print_r(iconv('utf-8', 'windows-1251', $body));
            }
            
        } 
        require($this->templatePath);                                
    }
    
    public function exportTmpls() {
        $list = $this->request->getVar('list');
        if ($list) {
            $list = json_decode($list);
            $destPath = '/home/vmaya/tmp/';
            $destURL = 'http://oformi-foto.ru/tmp/';
            
            include_once(INCLUDE_PATH.'/zip.php');
            
            $sourcePath = DATA_PATH.'a/templates/';
            $zipfile = new zipfile();
            
            function addFile($zipfile, $sourcePath, $fileName) {
                if (file_exists($sourcePath.$fileName)) 
                     $zipfile->addFile(file_get_contents($sourcePath.$fileName), $fileName);
            }
            //$zipfile->packToFile($path, $filePath, false);
            foreach ($list as $id) {
                addFile($zipfile, $sourcePath, 'JPG/'.$id.'.jpg');
                addFile($zipfile, $sourcePath, 'JPG/'.$id.'m.jpg');
                addFile($zipfile, $sourcePath, 'jpg_preview/i'.$id.'.jpg');
            }
            
            $content = $zipfile->file();
            
            $fileName = time().'.zip'; 
            $filePath = $destPath.$fileName;
            $fileURL =  $destURL.$fileName;
            if (file_exists($filePath)) unlink($filePath);
            
            $file = fopen($filePath, 'w+');
            fwrite($file, $content);
            fclose($file);        
            
            chmod($filePath, 0755);
        }
        require($this->templatePath);                                
    }
    
    public function faq() {
        $link = '//docs.google.com/document/d/1uCk4-zgjPvm9wQTvzMwi93q0E2OUQQueUWaab_EZW6M/edit?usp=sharing';
        require(TEMPLATES_PATH.'/utils_doc.html');
    }
    
    public function manage() {
        $link = '//docs.google.com/document/d/1EgdncYYCVBpI5YdOaLHsvBn4snnhwT3w48UfQcLLVJE';
        require(TEMPLATES_PATH.'/utils_doc.html');
    }
    
    public function nginx_stat() {
        include_once(TEMPLATES_PATH.'/utilsController/nginx-log-analyzer.php');
    }
    
    public function of_js_errors() {
        $items = DB::asArray("SELECT COUNT(error_id) AS count, time, browser_name, browser_version, message, filename, lineno FROM `of_js_errors` GROUP BY message ORDER BY count DESC");
        echo $this->showTable($items);
    }
    
    public function of_pjok_errors() {
        $items = DB::asArray("SELECT COUNT( id ) AS `count`, DATE_FORMAT(MIN(l.time), '%d.%m') AS min_date, ".
                            "DATE_FORMAT(MAX(l.time), '%d.%m') AS max_date, l.browser, l.flash, l.data FROM  `pjok_js_log` l GROUP BY l.data, l.browser ORDER BY `count` DESC ");
        echo $this->showTable($items);
    }
    
  
//[104,106,144,334,335,11881,11882,11883,11884,11885,11886,11887,11888,11889,11890,11891,11892,11893,11894,11895,11896,11897,11918,11919,11920,11921,11922,11923,11924,11946,11947,11948,11949,11950,11951,11952,11953,11954,11971,11972,11973,11974,11975,11976,11977,11978,11987,11988,11989,11990,11991,11992,11993,11994,11995,11996,11997,11998,12033,12034,12035,12036,12037,12038,12039,12040,12041,12042,12043,12044,12045,12071,12072,12073,12074,12075,12076,12077,12078,12079,12080,12081,12082,12095,12096,12097,12098,12101,12102,12103,12104,12105,12106,12107,12108,12109,12110,12125,12126,12127,12128,12129,12130,12131,12132,12133,12134,12135,12136,12137,12138,12139,12140,12141,12147,12148,12149,12150,12151,12152,12153,12154,12155,12156,12157,12158,12172,12173,12174,12175,12176,12177,12178,12179,12180,12181,12182,12183,12184,12207,12208,12209,12222,12223,12224,12225,12226,12227,12228,12229,12230,12231,12232,12233,12234,12235,12236,12237,12247,12248,12249,12250,12251,12252,12253,12254,12255,12256,12257,12258,12259,12260,12261,12262,12263,12264,12265,12285,12286,12287,12288,12289,12290,12291,12292,12293,12294,12295,12296,12314,12315,12316,12317,12318,12319,12320,12321,12322,12323,12346,12347,12348,12349,12350,12389,12390,12391,12392,12393,12394,12395,12396,12397,12398,12399,12400,12401,12402,12403,12426,12427,12428,12429,12430,12431,12432,12433,12434,12435,12436,12437,12438,12439,12440,12441,12442,12443,12444,12459,12460,12461,12462,12463,12464,12465,12466,12467,12468,12469,12470,12471,12472,12492,12493,12494,12495,12496,12497,12498,12499,12500,12501,12502,12503,12504,12518,12519,12520,12521,12522,12523,12524,12525,12542,12543,12544,12545,12546,12547,12556,12557,12558,12559,12560,12561,12562,12563,12564,12565,12566,12567,12568,12574,12575,12576,12577,12578,12579,12580,12581,12582,12583,12584,12585,12586,12587,12588,12589,12590,12591,12632,12633,12634,12635,12636,12637,12638,12639,12640,12641,12650,12651,12652,12653,12654,12655,12656,12657,12658,12659,12674,12675,12676,12677,12678,12679,12680,12681,12682,12694,12695,12696,12697,12698,12699,12700,12701,12702,12703,12704,12705,12715,12716,12717,12718,12719,12720,12721,12722,12723,12724,12725,12726,12727,12736,12737,12738,12739,12740,12741,12742,
//12743,12744,12753,12754,12755,12756,12757,12758,12759,12760,12761,12762,12763,12799,12800,12801,12802,12803,12804,12833,12834,12835,12836,12837,12838,12839,12840,12841,12842,12843,12862,12863,12864,12865,12866,12867,12868,12869,12870,12871,12888,12889,12890,12891,12892,12893,12894,12908,12909,12910,12911,12912,12913,12917,12918,12919,12920,12921,12922,12923,12924,12925,12926,12927,12932,12933,12934,12935,12936,12937,12938,12939,12940,12941,12942,12943,12944,12945,12955,12956,12957,12958,12959,12960,12961,12962,12963,12964,12977,12978,12979,12980,12981,12982,12983,12994,12995,12996,12997,12998,12999,13000,13001,13002,13003,13008,13009,13010,13011,13012,13013,13014,13016,13017,13018,13019,13020,13021,13022,13028,13029,13030,13031,13032,13033,13034,13035,13036,13037,13038,13039,13040,13041,13054,13055,13056,13057,13058,13059,13060,13061,13062,13063,13072,13073,13074,13075,13076,13077,13078,13079,13080,13081,13082,14097,14098,14099,14116,14117,14118,14119,14120,14121,14122,14123,14138,14139,14140,14141,14142,14150,14151,14152,14153,14154,14155,14156,14157,14158,14159,14160,14161,14162,14163,14164,14170,14171,14172,14173,14174,14175,14176,14177,14178,14179,14180,14181,14214,14215,14216,14217,14218,14219,14220,14221,14237,14238,14239,14240,14241,14242,14243,14255,14256,14257,14258,14259,14260,14261,14262,14263,14281,14282,14283,14284,14285,14286,14287,14296,14297,14298,14299,14300,14301,14302,14303,14304,14305,14306,14307,14308,14309,14310,14311,14312,14313,14322,14323,14324,14325,14326,14327,14328,14329,14330,14344,14345,14346,14347,14348,14349,14350,14351,14388,14389,14390,14391,14404,14405,14406,14407,14408,14409,14410,14411,14421,14422,14423,14424,14425,14426,14427,14428,14429,14447,14448,14449,14450,14502,14503,14504,14505,14506,14513,14514,14515,14516,14517,14518,14519,14520,14521,14522,14523,14524,14525,14538,14539,14540,14541,14542,14543,14544,14545,14546,14557,14558,14559,14560,14561,14562,14563,14564,14565,14566,14567,14582,14583,14584,14585,14586,14587,14588,14589,14590,14591,14592,14593,14622,14623,14624,14625,14626,14627,14628,14629,14630,14631,14632,14633,14634,14635,14636,14637,14672,14673,14674,14675,14676,14677,14698,14699,14700,14701,14702,14703,14704,14705,14726,14727,14728,14729,14730,14738,14739,14740,14741,14742,14743,14744,14745,14746,14747,14748,14749,14750,14751,14752,14753,14754,14755,14756,14757,14758,14759,14772,14773,14774,14775,14776,14777,14778,14779,14798,

//14799,14800,14801,14802,14803,14804,14805,14813,14814,14815,14816,14817,14818,14819,14820,14821,14822,14823,14828,14829,14830,14831,14832,14833,14834,14835,14836,14837,14838,14839,14840,14841,14842,14843,14844,14845,14861,14862,14863,14864,14865,14866,14867,14882,14883,14884,14885,14886,14887,14888,14889,14899,14900,14901,14902,14903,14904,14905,14914,14915,14916,14917,14918,14919,14920,14921,14922,14923,14924,14925,14926,14927,14928,14929,14930,14931,14932,14933,14934,14935,14936,14937,14938,14965,14966,14967,14968,14969,14970,14971,14972,14973,14974,14975,14976,14977,14989,14990,14991,14992,14993,14994,14995,14996,14997,15011,15012,15013,15014,15015,15016,15017,15018,15019,15049,15050,15051,15052,15053,15054,15055,15056,15057,15058,15059,15060,15067,15068,15069,15070,15071,15072,15073,15074,15075,15076,15077,15078,15079,15080,15081,15090,15091,15092,15093,15094,15095,15096,15097,15098,15099,15100,15101,15102,15103,15118,15119,15120,15121,15122,15123,15134,15135,15136,15137,15138,15139,15140,15151,15152,15153,15154,15155,15156,15157,15158,15159,15160,15161,15162,15167,15168,15169,15170,15171,15172,15173,15174,15175,15176,15194,15195,15196,15197,15198,15199,15200,15201,15202,15203,15204,15205,15236,15237,15238,15239,15240,15241,15242,15243,15244,15245,15246,15247,15248,15249,15250,15251,15252,15262,15263,15264,15265,15266,15267,15268,15269,15270,15271,15272,15273,15274,15275,15276,15279,15280,15281,15282,15283,15284,15285,15286,15287,15288,15289,15290,15291,15292,15293,15294,15295,15296,15297,15298,15299,15300,15301,15302,15311,15312,15313,15314,15315,15316,15317,15318,15319,15320,15321,15322,15323,15327,15328,15329,15330,15331,15332,15333,15334,15335,15336,15363,15364,15365,15366,15367,15368,15369,15383,15384,15385,15386,15387,15388,15389,15390,15391,15410,15411,15412,15413,15414,15415,15416,15417,15418,15419,15420,15421,15422,15423,15424,15425,15426,15427,15443,15444,15445,15446,15447,15448,15449,15450,15451,15452,15453,15456,15457,15458,15459,15460,15461,15462,15463,15464,15465,15466,15467,15468,15469,15470,15471,15478,15479,15480,15481,15482,15483,15484,15485,15486,15487,15488,15489,15490,15491,15492,15493,15494,15530,15531,15532,15533,15534,15535,15536,15537,15538,15539,15540,15552,15553,15554,15555,15556,15557,15558,15559,15566,15567,15568,15569,15570,15571,15572,15573,15574,15575,15576,15577,15578,15579,15603,15604,15605,15606,15607,15629,15630,15631,15632,15633,15642,15643,15644,15645,15646,15647,15648,15649,15650,15668,15669,15670,15671,15672,15673,15674,15675,15676,15677,15678,15679,15680,15692,15693,15694,15695,15696,15697,15719,15720,15721,15722,15723,15724,15725,15737,15738,15739,15740,15741,15742,15743,15744,15745,15759,15760,15761,15762,15763,15764,15765,15766,15767,15768,15769,15770,15796,15797,15798,15799,15800,15801,15802,15803,15804,15805,15806,15807,15808,15809,15810,15825,15826,15827,15864,15865,15866,15867,15868,15869,15880,15881,15882,15883,15884,15885,15886,15887,15888,15889,15899,15900,15901,15902,15903,15904,15905,15906,15907,15915,15916,15917,15918,15919,15920,15921,15922,15923,15924,15925,15942,15943,15944,15945,15946,15947,15955,15956,15957,15958,15959,15960,16001,16002,16003,16004,16005,16006,16007,16008,16009,16010,16011,16012,16013,16014,16015,16016,16025,16026,16027,16028,16029,16030,16031,16032,16033,16034,16035,16046,16047,16048,16049,16050,16051,16052,16053,16054,16055,16056,16057,16058,16067,16068,16069,16070,16071,16072,16105,16106,16107,16108,16109,16110,16111,16112,16113,16114,16115,16116,16132,16133,16134,16135,16136,16137,16140,16141,16142,16143,16144,16145,16146,16147,16148,16149,16166,16167,16168,16169,16170,16171,16172,16173,16174,16175,16176,16191,16192,16193,16194,16195,16196,16201,16202,16203,16204,16205,16206,16207,16208,16209,16210,16211,16231,16232,16233,16234,16235,16236,16237,16238,16239,16240,16241,16242,16276,16277,16278,16279,16280,16288,16289,16290,16291,16292,16293,16294,16295,16296,16297,16298,16299,16300,16301,16302,16319,16320,16321,16322,16323,16324,16325,16326,16337,16338,16339,16340,16341,16342,16343,16344,16345,16346,16347,16348,16349,16359,16360,16361,16362,16363,16364,16365,16374,16375,16376,16377,16400,16401,16402,16403,16404,16405,16406,16415,16416,16417,16418,16419,16420,16421,16422,16423,16424,16434,16435,16436,16437,16438,16439,16440,16441,16442,16443,16448,16449,16450,16451,16452,16453,16454,16455,16456,16457,16458,16459,16475,16476,16477,16478,16502,16503,16504,16505,16506,16507,16508,16509,16510,16521,16522,16523,16524,16525,16526,16527,16528,16529,16530,16531,16532,16542,16543,16544,16545,16546,16547,16548,16549,16550,16551,16560,16561,16562,16563,16564,16565,16566,16567,16584,16585,
//16586,16587,16588,16589,16595,16596,16597,16598,16599,16600,16601,16602,16603,16604,16605,16606,16610,16611,16612,16613,16614,16615,16616,16617,16618,16647,16648,16649,16650,16651,16652,16653,16668,16669,16670,16671,16672,16685,16686,16687,16688,16689,16696,16697,16698,16699,16700,16701,16702,16703,16718,16719,16720,16721,16722,16723,16724,16725,16726,16727,16728,16729,16730,16731,16732,16733,16734,16735,16736,16737,16738,16739,16740,16757,16758,16759,16760,16761,16762,16770,16771,16772,16773,16774,16775,16776,16777,16778,16779,16783,16784,16785,16786,16787,16788,16789,16790,16791,16792,16793,16794,16805,16806,16807,16808,16809,16810,16811,16835,16836,16837,16838,16839,16840,16841,16842,16843,16844,16851,16852,16853,16854,16855,16856,16857,16866,16867,16868,16869,16870,16871,16872,16873,16886,16887,16888,16889,16890,16891,16897,16898,16899,16900,16901,16902,16903,16904,16916,16917,16918,16919,16920,16921,16932,16933,16934,16935,16936,16937,16938,16939,16940,16951,16952,16953,17023,17024,17025,17026,17027,17028,17029,17035,17036,17037,17038,17039,17040,17053,17054,17055,17056,17057,17058,17135,17136,17137,17138,17139,17140,17141,17142,17152,17153,17154,17155,17156,17157,17158,17159,17160,17161,17162,17183,17184,17185,17186,17187,17188,17189,17190,17239,17240,17241,17301,17302,17303,17304,17305,17306,17307,17308,17309,17310,17311,17444,17445,17446,17447,17448,17449,17450,17451,17452,17453,17454,17455,17456,17570,17571,17572,17573,17574,17575,17576,17577,17578,17579]    
}
?>