<?
class statistic {
    public static function add($intKey, $charKey, $value) {
        $query = "INSERT INTO gpj_statistic (`varInt`, `varChar`, `value`) VALUES ($intKey, '$charKey', '$value')";
        return DB::query($query);
    }
}
?>