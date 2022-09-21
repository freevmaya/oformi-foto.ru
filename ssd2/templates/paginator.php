<?
    define('MAXVIEWPAGES', 5);
    
    function paginator($curPage, $pageCount, $linkPattern) {
        GLOBAL $locale;
        
        function pattern($linkPattern, $num) {
            return preg_replace("/\s/i", '+', sprintf($linkPattern, $num));
        }
        
        $result = '';
        if ($pageCount > 1) {
            $result = '<div class="paginator">';
            if ($pageCount > MAXVIEWPAGES) $result .= '<p>'.sprintf($locale['TOTALPAGES'], $pageCount).'</p>';
            
            $count = $pageCount<MAXVIEWPAGES?$pageCount:MAXVIEWPAGES;
            $center = $count / 2; 
            if ($curPage < $center)
                $start = 0;
            else if ($curPage >= $pageCount - $center)
                $start = $pageCount - ceil($count);
            else $start =  $curPage - ceil($center);
            
            
            if ($curPage > 1) {
                if ($start > 0)
                    $result .= '<span class="back"><a href="'.pattern($linkPattern, 1).'" title="'.$locale['FIRSTPAGE'].'"><<</a></span>';
                $result .= '<span class="back"><a href="'.pattern($linkPattern, $curPage - 1).'" title="'.$locale['PREVPAGE'].'"><</a></span>';
            }
            
            for ($i=1; $i<=$count; $i++) {
                $page = $i + $start;
                if ($page <= $pageCount) { 
                    $link = pattern($linkPattern, $page);
                    
                    if ($page == $curPage) $result .= '<span class="current"><a href="'.$link.'">'.$page.'</a></span>';
                    else $result .= '<span><a href="'.$link.'">'.$page.'</a></span>';
                }
            }             
               
            if ($curPage <= $pageCount - 1) {
                $result .= '<span class="forward"><a href="'.pattern($linkPattern, $curPage + 1).'" title="'.$locale['NEXTPAGE'].'">></a></span>';
                if ($start <= $pageCount - $count - 1)
                    $result .= '<span class="back"><a href="'.pattern($linkPattern, $pageCount).'" title="'.$locale['ENDPAGE'].'">>></a></span>';
            }
            $result .= '</div>';
        };
        return $result;
    }
?>