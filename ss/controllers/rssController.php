<?
include_once(INCLUDE_PATH.'/_edbu2.php');

class rssController extends controller {
    
    protected function view() {
    	echo 'rss';
    	ss::setTemplate('json.html');
    }
    
    protected function ya_turbo() {
    	$items = DB::asArray(
    		"SELECT tmo.*, a.name AS autor
    		FROM gpj_tmplOptions tmo LEFT JOIN gpj_autors a ON a.autor_id = tmo.autor_id
    		WHERE tmo.name > '' AND tmo.desc > '' ORDER BY tmo.`insertTime` DESC LIMIT 200"
    	); 
    	ss::setTemplate('json.html');
    	require(TEMPLATES_PATH.'rss/ya-turbo.php');
    }
}
?>