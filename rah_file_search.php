<?php	

/**
 * Rah_file_search plugin for Textpattern CMS
 *
 * @author Jukka Svahn
 * @date 2008-
 * @license GNU GPLv2
 * @link http://rahforum.biz/plugins/rah_file_search
 *
 * Requires Textpattern v4.2.0 or newer.
 *
 * Copyright (C) 2012 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

	function rah_file_search($atts, $thing=NULL) {
		
		global $pretext, $has_article_tag, $thispage;
		
		$grand_total = isset($atts['grand_total']) ? $atts['grand_total'] : 1;
		
		if($grand_total == 1)
			$thispage['grand_total'] = 0;
		
		if(empty($pretext['q']))
			return;

		$has_article_tag = true;
		$q = $pretext['q'];
		$q = trim($q);
		$quoted = ($q[0] === '"') && ($q[strlen($q)-1] === '"');
		$q = doSlash($quoted ? trim(trim($q, '"')) : $q);
		
		$q = 
			preg_replace('/\s+/', ' ', 
				str_replace(
					array('\\','%','_','\''),
					array('\\\\','\\%','\\_', '\\\''),
					$q
				)
			);
		
		$fields =
			array(
				'filename',
				'title',
				'description'
			);
		
		if($quoted) {
			foreach($fields as $field)
				$sql[] = "lower($field) like lower('%$q%')";
		}
		else {
			$words = explode(' ', $q);

			foreach($words as $word) {
				$fsql = array();
				foreach($fields as $field)
					$fsql[] = "lower($field) like lower('%$word%')";
				$sql[] = '('.implode(' or ', $fsql).')';
			}
		}

		$rs = 
			safe_column(
				'id',
				'txp_file',
				'('.implode($quoted ? ' or ' : ' and ', $sql).') and status=4'
			);
		
		if(!$rs)
			return;
		
		if($grand_total == 1)
			$thispage['grand_total'] = count($rs);
		
		$atts['id'] = implode(',', $rs);
		unset($atts['grand_total']);
		
		return file_download_list($atts, $thing);
	}
?>