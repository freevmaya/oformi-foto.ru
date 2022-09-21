<?

if (count($_GET) == 2) {
    if (file_exists($_GET['new']) && file_exists($_GET['old'])) {
        rename($_GET['old'], 'tmp_'.$_GET['old']);
        rename($_GET['new'], $_GET['old']);
        rename('tmp_'.$_GET['old'], $_GET['new']);
        echo 'SWAP '.$_GET['old'].'=>'.$_GET['new'];
    }
} else echo 'error params';
?>