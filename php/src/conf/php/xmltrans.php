<%

class xmltrans {
	
	var $xmltext='';
	
	// function xmltrans() => object
	function xmltrans() {

	}
	
	function xml2php() {
	
	}
	
	function php2xml($struct, $level=0) {
		global $xmltext;
		$indent='';
		for ($i=0; $i<$level; $i++) {
			$indent.="\t";
		}
		if (!$level) {
			$xmltext='';
		}
		if (is_array($struct)) {
			foreach ($struct as $k => $v) {
				$xmltext.="\n$indent<$k>";
			//echo "\n$indent<$k>";
				if (is_array($v)) {
					$level++;
					$this->php2xml($v, $level);
					$level--;
				} else {
					$xmltext.="$v";
				//	echo "$v";
				}
				$xmltext.="</$k>";
				//echo "</$k>";
			}
		} else {
			$xmltext.="\n$indent<$struct></$struct>";
			//echo "\n$indent<$struct></$struct>";
		}
		if (!$level) {
			return $xmltext;
		} else {
			$level--;
			return;
		}
	}

}
%>
