<?
    function showTable($list) {
        if (count($list) > 0) {
            $keys = array_keys($list[0]); 
            echo '<table class="report"><tr>';
            foreach ($keys as $key)
                echo "<th>$key</th>";
            echo '</tr>';
            foreach ($list as $column) {
                echo '<tr>';
                foreach ($keys as $key)
                    echo "<td>{$column[$key]}</td>";
                echo '</tr>';
            }
            echo '</table>'; 
        }
    }
?>