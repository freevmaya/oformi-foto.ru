<?

include('include/Memcache.php');

$key='mc2w1';                      

function getContext() {
    return 'Это контекст AWE ----------';
}

echo MCache::getValue($key, 'getContext');
?>