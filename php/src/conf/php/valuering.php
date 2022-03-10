<%
	class ValueRing {

		function ValueRing () {
			$this->ring=array();
		}
		
		function put ($value) {
			if (is_array($value)) {
				foreach ($value as $val) {
					$this->ring[]=$val;
				}
			} else {
				$this->ring[]=$value;
			}
		}
		
		function get () {
			$cur=array_shift($this->ring);
			$this->ring[]=$cur;
			return $cur;
		}
		
		function getlast() {
			$cur=array_pop($this->ring);
			$this->ring[]=$cur;
			return $cur;
		}

		function del ($value) {
			for($i=0; $i<count($this->ring); $i++) {
				if ($this->ring[$i] == $value) {
					$prev=array_slice($this->ring,0,$i);
					$x=$i+1;
					$post=array_slice($this->ring,$x);
					$this->ring=array_merge($prev,$post);
					return 1;
				}
			}
			return 0;
		}

	}
%>
