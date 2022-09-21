<?
    define('MAXVIEWPAGES', 5);
    function paginator($curPage, $pageCount, $linkPattern) {
        $result = '';
        if ($pageCount > 1) {
            $result = '<div class="paginator">';
            if ($pageCount > MAXVIEWPAGES) $result .= '<p>Всего '.$pageCount.' страниц</p>';
            
            $count = $pageCount<MAXVIEWPAGES?$pageCount:MAXVIEWPAGES;
            $center = $count / 2; 
            if ($curPage < $center)
                $start = 0;
            else if ($curPage >= $pageCount - $center)
                $start = $pageCount - ceil($count);
            else $start =  $curPage - ceil($center);
            
            
            if ($curPage > 1) {
                if ($start > 0)
                    $result .= '<span class="back"><a href="'.sprintf($linkPattern, 1).'" title="Первая страница"><<</a></span>';
                $result .= '<span class="back"><a href="'.sprintf($linkPattern, $curPage - 1).'" title="Предыдущая страница"><</a></span>';
            }
            
            for ($i=1; $i<=$count; $i++) {
                $page = $i + $start;
                if ($page <= $pageCount) { 
                    $link = sprintf($linkPattern, $page);
                    
                    if ($page == $curPage) $result .= '<span class="current">'.$page.'</span>';
                    else $result .= '<span><a href="'.$link.'">'.$page.'</a></span>';
                }
            }             
               
            if ($curPage <= $pageCount - 1) {
                $result .= '<span class="forward"><a href="'.sprintf($linkPattern, $curPage + 1).'" title="Следующая страница">></a></span>';
                if ($start < $pageCount - $count - 1)
                    $result .= '<span class="back"><a href="'.sprintf($linkPattern, $pageCount).'" title="Последняя страница">>></a></span>';
            }
            $result .= '</div>';
        };
        return $result;
    }
    
    function charPaginator($curPage, $linkPattern, $absArr) {
        $result = '<div class="paginator">';
        $pageCount = count($absArr);
        $count = count($absArr);
        
        for ($i=0; $i<$count; $i++) {
            $char = $absArr[$i];
            $link = sprintf($linkPattern, $char);
            
            if ($char == $curPage) $result .= '<span class="current">'.$char.'</span>';
            else $result .= '<span><a href="'.$link.'">'.$char.'</a></span>';
        }
        $result .= '</div>';
        return $result;
    }
?>