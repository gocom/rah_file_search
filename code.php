<?php	##################
	#
	#	rah_file_search-plugin for Textpattern
	#	version 0.5
	#	by Jukka Svahn
	#	http://rahforum.biz
	#
	###################

/**
	The tag. Returns the list of matching files.
*/

	function rah_file_search($atts, $thing = NULL) {
		
		global $pretext, $has_article_tag, $thispage;
		
		/*
			Allow turn grand_total counting off
			by setting "grand_total" attribute to 0
		*/
		
		$grand_total = isset($atts['grand_total']) ? $atts['grand_total'] : 1;
		
		if($grand_total == 1)
			$thispage['grand_total'] = 0;
		
		if(empty($pretext['q']))
			return;
		
		/*
			We are an article tag,
			well kinda.
		*/
		
		$has_article_tag = true;
		$q = $pretext['q'];
		
		/*
			Quotes should be stripped
			from quote surrounded string
		*/
		
		$quoted = ($q[0] === '"') && ($q[strlen($q)-1] === '"');
		$q = doSlash($quoted ? trim(trim($q, '"')) : $q);
		
		/*
			Clean whitespace and escape the
			special syntax MySQL's like operator uses
		*/
		
		$q = 
			preg_replace('/\s+/', ' ', 
				str_replace(
					array('\\','%','_','\''),
					array('\\\\','\\%','\\_', '\\\''),
					$q
				)
			);
		
		/*
			Get matching IDs
		*/
		
		$rs = 
			safe_rows(
				'id',
				'txp_file',
				"(filename like '%$q%' or title like '%$q%' or description like '%$q%') and status=4"
			);
		
		if(!$rs)
			return;
		
		if($grand_total == 1)
			$thispage['grand_total'] = count($rs);
		
		$ids = array();
		
		foreach($rs as $a) 
			$ids[] = $a['id'];
		
		/*
			Return the file list
		*/
		
		$atts['id'] = implode(',',$ids);
		
		unset(
			$atts['grand_total']
		);
		
		return
			file_download_list($atts, $thing);
	}
?>