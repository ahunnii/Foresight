<?php # Kyle L. Oswald 10/17/12

	DEFINE('PAGINATOR_INDEX', '{start_index}');
	
	function print_pagination($current_page, $item_count, $page_count, 
		$length, $paginator_href, $paginator_replace=PAGINATOR_INDEX) {
		
		if ($length >= $page_count) {
			$start_page = 0;
			$end_page = $page_count;
		} else if ($page_count - $current_page < $length) {
			$start_page = $page_count - $length;
			$end_page = $page_count;
		} else {
			$start_page = $current_page;
			$end_page = $start_page + $length;
		}
		
		if ($start_page < 0) 
			$start_page = 0;
			
		echo '<div id="paginator"><div class="pagination">';
		
		# Echo first & previous paginators
		if ($start_page != 0) {
			$href = str_replace($paginator_replace, 0, $paginator_href);
			echo '<a href="' . $href . '" alt = "first page">&laquo;</a>';
			
			$href = str_replace($paginator_replace, ($current_page - 1) * $item_count, $paginator_href);
			echo '<a href="' . $href . '" alt = "previous">&lsaquo;</a>';
		}
		
		for ($i = $start_page; $i < $end_page; $i++) {
			if ($i == $current_page) {
				echo '<b>' . ($i + 1) . '</b>';
			} else {
				$href = str_replace($paginator_replace, $i * $item_count, $paginator_href);
				echo '<a href="' . $href . '">' . ($i + 1) . '</a>';
			}
		}
		
		# Echo last & next paginators
		if ($end_page != $page_count) {
			$href = str_replace($paginator_replace, ($current_page + 1) * $item_count, $paginator_href);
			echo '<a href="' . $href . '" alt = "next">&rsaquo;</a>';
			
			$href = str_replace($paginator_replace, ($page_count - 1) * $item_count, $paginator_href);
			echo '<a href="' . $href . '" alt = "last page">&raquo;</a>';
		}
		
		echo '</div></div>';
		
	}
?>