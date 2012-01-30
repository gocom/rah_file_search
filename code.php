<?php	##################
	#
	#	rah_file_search-plugin for Textpattern
	#	version 0.2
	#	by Jukka Svahn
	#	http://rahforum.biz
	#
	###################

/**
	The tag. Returns the list of matching files.
*/

	function rah_file_search($atts, $thing = NULL) {
		
		global $pretext, $has_article_tag;
		
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
				"(filename like '%$q%' or description like '%$q%') and status=4"
			);
		
		if(!$rs)
			return;
		
		$ids = array();
		
		foreach($rs as $a) 
			$ids[] = $a['id'];
		
		/*
			Return the file list
		*/
		
		$atts['id'] = implode(',',$ids);
		
		return
			file_download_list($atts, $thing);
	}
?>