<pre>
<?
GLOBAL $_SERVER, $sort;

define('VISCOUNT', 30);
set_time_limit(0);

$log = isset($_GET['file'])?(dirname(__FILE__).'/'.$_GET['file']):'/var/log/nginx/access.log';
$file_time = filectime($log);
$sort = isset($_GET['sort'])?$_GET['sort']:'time';

if ($this->request->getVar('clear')) {
    echo '<div>Exec result: '.exec("cp -f /dev/null {$log}").'</div>';
} 


$filter = isset($_GET['filter'])?explode(':', $_GET['filter']):false;
$file_size = filesize($log);
$skip_factor = ceil($file_size / 8000000);

$r = fopen($log, 'r');

$count = array();
$time = array();

$c = 0;

$summary = array(
    'calls'=>array('Overall'=>0),
    'time'=>array('Overall'=>0), 
    'status'=>array(),  
    'urls'=>array(),
    'ip'=>array()
);

while ($line = fgets($r)){
	if ( $c % 50000 == 0 ) echo '.';
	if ( ++$c % $skip_factor != 0 ) continue;
	
	preg_match('/[^ ]+ \- \[(.+?)\] "(.+?)" "([^ ]+) ([^ ]+) .*?" ([0-9]+) \(([0-9]+)\) "(.*?)" "([^ ]+) (.*?)" \[([0-9.]+)\] "(.+)"/', $line, $m);
	
    if (isset($m[0])) {
        $ipa    = explode('-', $m[0]);
        $data   = array(
            'ip'=>$ip = $ipa[0],
            'utime'=>$utime = strtotime($m[1]),
        	'type'=>$m[3],
        	'time'=>$time = $m[10],
        	'status'=>$m[5],
        	'length'=>$m[6],
        	'uri'=>$m[4],
        	'url'=>$m[2] . '/' .$m[8] . '?' .$m[9],
        	'agent'=>$ipa[0].' '.$m[11]
        );
        /*
        $ip     = $ipa[0];
    	$type   = $m[3];
    	$time   = $m[10];
    	$status = $m[5];
    	$length = $m[6];
    	$uri    = $m[4];
        $url    = $m[2] . '/' .$m[8] . '?' .$m[9];
        $agent  = $ip.' '.$m[11];
        */
        
    	$data['url'] = preg_replace('/[0-9]+/', '[x]', $data['url']);
        
        if ($filter) {
            if (strpos($data[$filter[0]], $filter[1]) === false) continue;
        }
         
        if (isset($summary['ip'][$ip])) {
            $summary['ip'][$ip]['time'] += $time;
            $summary['ip'][$ip]['calls']++;
            $summary['ip'][$ip]['t_accum'][] = $utime; 
            $summary['ip'][$ip]['urls'][] = $data['url'];
        } else {
            $summary['ip'][$ip] = array('time'=>$time, 'calls'=>1, 't_accum'=>array($utime), 'urls'=>array($data['url']));
        }
        
    	$summary['agent'][$data['agent']] = isset($summary['agent'][$data['agent']])?($summary['agent'][$data['agent']] + $data['time']):$data['time'];
        
    	$summary['calls']['Overall']++;
    	$summary['calls'][$data['type']] = isset($summary['calls'][$data['type']])?($summary['calls'][$data['type']] + 1):1;
    	
    	$summary['time']['Overall'] += $data['time'];
    	$summary['time'][$data['type']] = isset($summary['time'][$data['type']])?($summary['time'][$data['type']] + $data['time']):$data['time'];
    	
    	$summary['status'][$data['status']] = isset($summary['status'][$data['status']])?($summary['status'][$data['status']] + 1):1;
        $summary['urls']['calls'][$data['url']] = isset($summary['urls']['calls'][$data['url']])?($summary['urls']['calls'][$data['url']] + 1):1;
        $summary['urls']['time'][$data['url']] = isset($summary['urls']['time'][$data['url']])?($summary['urls']['time'][$data['url']] + $data['time']):$data['time'];
    }
}

echo "\n\n";

$summary_types = '';
$summary_timing = '';
$summary_statuses = '';

$file_sizeMB = round($file_size / 1024 /1024);

echo "Пропуск: $skip_factor, date: ".date('d.m.Y H:i:s').", file time: ".date('d.m H:i', $file_time)."\n";
echo "Размер файла: {$file_sizeMB}Mb\n\n";

echo "= Summary ===========================================================================\n";

foreach ( $summary['calls'] as $type => $count ) $summary_types .= "{$type}: {$count}  ";
printf('| %-25s: %s' . "\n", 'Request types', $summary_types );

foreach ( $summary['time'] as $type => $timing ) $summary_timing .= "{$type}: {$timing}  ";
printf('| %-25s: %s' . "\n", 'Request timing', $summary_timing );

foreach ( $summary['status'] as $status => $count ) $summary_statuses .= "{$status}: {$count}  ";
printf('| %-25s: %s' . "\n", 'Request statuses', $summary_statuses );


echo "\n";

arsort($summary['urls']['time']);

function cmd_items($itm1, $itm2) {
    GLOBAL $sort;
    return ($itm1[$sort] < $itm2[$sort])?1:-1;
}
uasort($summary['ip'], 'cmd_items');

arsort($summary['agent']);

echo "= Details ===========================================================================\n";
printf('| %15s |  %20s |  %25s |  %-15s ' . "\n", 'Calls', 'Total time (sec)', 'Reponse rate (sec/call)', 'URL pattern');

$ri = 0;
foreach ( $summary['urls']['time'] as $url => $time ) {
	if ( ++$ri >= 35 ) break;
	$calls = $summary['urls']['calls'][$url];
	
	printf('| %15d |  %20.3f |  %25.3f |  %-15s ' . "\n", $calls, $time, $time/$calls, $url );
}
?>
</pre>
<h2>IP</h2>
<table class="report">
    <tr><th>IP</th><th><a href="?task=utils,nginx_stat&sort=time">Accum time</a></th><th><a href="?task=utils,nginx_stat&sort=calls">Calls</a></th><th>period</th><th>Продолжительность (минуты)</th><th></th></tr>
<?
$ri = 0;
//print_r($summary['ip']);
foreach ( $summary['ip'] as $ip => $item ) {
    $list = $item['t_accum']; 
    $time = $item['time'];
    $count = count($list);
    
	if ((++$ri >= VISCOUNT) || ($time < 0.001)) break;
    $title = '';
    
    if ($count > 0) { 
        arsort($list);
        $tcount = $list[$count - 1] - $list[0];
    } else $tcount = 0;
    
    $urls = '';
    foreach ($item['urls'] as $url) $urls .= $url."\n";
        
	printf('<tr><td><a href="'.$_SERVER['REQUEST_URI'].'&filter=ip:'.$ip.'">%-10s</a></td><td>%20.3f</td><td>%8d</td><td>%s</td><td>%10.2f</td><td title="%s">%s</td></tr>'."\n", 
                $ip, $time, $count, date('H:i', $list[0]).' '.date('H:i', $list[$count - 1]), $tcount / 60, $urls, substr($item['urls'][0], 0, 100));

}
?>
</table>
<h2>Agents</h2>
<table class="report">
    <tr><th>Agent</th><th>accum time</th></tr>
<?
$ri = 0;
foreach ( @$summary['agent'] as $agent => $time ) {
	if ((++$ri >= VISCOUNT) || ($time < 0.001)) break;
	printf('<tr><td>%-20s</td><td>%20.3f</td></tr>'."\n", $agent, $time);
}

?>
</table>
<form method="POST" action="<?=MAINURL.$_SERVER['REQUEST_URI']?>">
    <input type="hidden" value="1" name="clear">
    <input type="submit" value="Clear">
</form>