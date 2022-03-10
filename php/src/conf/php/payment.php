<%
	class payment {
		var $PProjID;
		var $PDemo;
		var $PEmail;
	
	function payment ($demo, $email, $proj='') {
		$this->PDemo=$demo;
		$this->PProjID=$proj;
		$this->PEmail=$email;
	}
	
	function CCscreen () {
	
	}
	
	function ECscreen () {
	
	}
	
	function RecordTrans () {
	
	}
	
	function EmailReceipt ($user, $method, $msg='', $details='', $items='') {
		$body="\n";
		$divider="\n------------------------------------------------------------\n";
		switch ($method) {
			case 'C':
				$action="credit card will be charged";
				break;
			case 'E':
				$action="bank account will be drafted";
				break;
		}
		$footer="\n\nThank you. Your $action by AR Website License/Pmt. If you have any problems with this receipt or notice any errors, please call the Information Network of Arkansas at 877-727-3468\n";
		if ($msg[0]) {
			$body.="$msg[0]\n";
			if ($msg[1] || is_array($details) || is_array($items)) {
				$body.=$divider;
			}
		}
		if (is_array($details)) {
			$body.="\nTRANSACTION DETAILS\n";
			$max=0;
			foreach (array_keys($details) as $k) {
				if (strlen($k) > $max) {
					$max=strlen($k);
				}
			}
			$max+=2;
			foreach (array_keys($details) as $k) {
				$name=$k.":";
				$body.="\n    ".sprintf("% -${max}s", $name);
				$body.="$details[$k]";
			}
			$body.="\n";
			if ($msg[1] || is_array($items)) {
				$body.=$divider;
			}
		}
		if (is_array($items)) {
			$body.="\nPAYMENT DETAILS\n";
			$total=0;
			foreach (array_keys($items) as $k) {
				$body.="\n    ".sprintf("% -50s", $k);
				$body.="     ".sprintf("$%6.2f", $items[$k]);
				$total+=$items[$k];
			}
			$body.="\n    ".sprintf("% -50s", '');
			$body.="     ".sprintf("%s", '----------');
			$body.="\n    ".sprintf("% -50s", 'TOTAL');
			$body.="     ".sprintf("$%6.2f", $total)."\n";
			if ($msg[1]) {
				$body.=$divider;
			}
		}
		if ($msg[0]) {
			$body.="\n$msg[1]\n";
		}
		$body.=$footer;
		if ($this->PProjID) {
			$body.="\nIf you have an emotional issue, take 2 prozac and follow this link. http://www.accessarkansas.org/support/public.php?projectid=".$this->PProjID."\n";;
		}
		mail($user, "Receipt for on-line transaction", $body);
	}
}
%>
