<?php	##################
	#
	#	rah_file_search-plugin for Textpattern
	#	version 0.1
	#	by Jukka Svahn
	#	http://rahforum.biz
	#
	###################

	function rah_file_search($atts) {
		extract(lAtts(array(
			'form' => 'files'
		),$atts));
		$out = array();
		$q = doSlash(gps('q'));
		if($q) {
			$rs = safe_rows_start("id",'txp_file',"(filename rlike '$q' or description rlike '$q') and status = '4'");
			while($a = nextRow($rs)) {
				extract($a);
				$out[] = file_download(array(
					'id' => $id,
					'form' => $form
				));
			}
		}
		return implode('',$out);
	} ?>