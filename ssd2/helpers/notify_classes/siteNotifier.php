<?
class siteNotifier extends baseNotifier {
    public function send($user, $callback, $msg_body) {
        $this->createNotify('site', $user, $callback, $msg_body);
        return DB::lastID(); 
    }
}
?>