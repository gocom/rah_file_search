<?php	##################
	#
	#	rah_file_search-plugin for Textpattern
	#	version 0.7
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
		
		$q = trim($q);
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
			Searchable fields
		*/
		
		$fields =
			array(
				'filename',
				'title',
				'description'
			);
		
		/*
			If quoted
		*/
		
		if($quoted)
			foreach($fields as $field)
				$sql[] = "lower($field) like lower('%$q%')";
		else {
			
			/*
				Go thru the words
			*/
			
			$words = explode(' ', $q);
			foreach($words as $word) {
				$fsql = array();
				foreach($fields as $field)
					$fsql[] = "lower($field) like lower('%$word%')";
				$sql[] = '('.implode(' or ',$fsql).')';
			}
		}
		
		/*
			Get matching IDs
		*/
		
		$rs = 
			safe_rows(
				'id',
				'txp_file',
				'('.implode(($quoted ? ' or ' : ' and '),$sql).') and status=4'
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