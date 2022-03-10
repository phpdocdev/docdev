<?

function process_refund($transactions, $appname="", $unrestricted=false){
	$Refund = new RefundTrans();
	
	if(!trim($transactions)){
		return "No Orderid Specified";
	}
	$result="";
	$msg="";
	$error =0;
		
		$orderid = $transactions;
	
	if($unrestricted){
		list($successful, $msg) =  $Refund->unrestricted_refund($orderid, $appname);
	}else{
		list($successful, $msg) = $Refund->restricted_refund($orderid, $appname);
	}
	
		if(!$successful){
			$error = 1;
			$error_msg = $msg."\n";
		}
		
		$result = $msg."\n";
	
	if($successful==="Skipping"){
		$successful=0;
	}
	
	if($error){
		$successful=0;
		$summary="Failed to refund $appname transaction";
		$description='Failed to refund '.$error_msg.'. Please investigate and refund.'. $output;
		$Refund->sendToMantis(92,58,97,50,40,$summary,$description);
	}
	
	return array($successful, $result);
}

class RefundTrans{

	function RefundTrans(){
		require_once('DB.php');	
		$Opts = readRefundConf('/web/php/INA/Refunds/conf.properties');
		$this->Opts=$Opts;
		
		$this->DB = DB::connect($this->Opts['DB_DSN'], false);
		if( DB::isError($this->DB) ){
			echo $this->DB->getMessage() . ' ' . $this->DB->getDebugInfo();
		}
		$this->DB->setFetchMode(DB_FETCHMODE_ASSOC);
	}
	
	//send requested orderid to web service
	function restricted_refund($orderid, $appname){
		
		list($trans_orderid, $early_date, $late_date, $disbursed_date, $group_type) = $this->DB->getRow("select
								th.orderid as orderid,
								date_format((th.date + interval 2 day), '%Y%m%d') as early_date,
								date_format((th.date  + interval 22 day), '%Y%m%d') as late_date,
								if (disb.req_date is not null, date_format(disb.req_date, '%m/%d/%Y'), '') as req_date,
								th.group_type as group_type
							from trans_history th
								left join disburse_orders on disburse_orders.orderid=th.orderid
								left join disburse disb on disb.id=disburse_orders.id
							where th.orderid='".mysql_real_escape_string($orderid)."' and th.status='F'", DB_FETCHMODE_ORDERED);
		if(!$trans_orderid){
			//check for refund
			list($trans_orderid) = $this->DB->getRow("select
								th.orderid as orderid
							from trans_history th
							where th.orderid='".mysql_real_escape_string($orderid)."' and th.status='G'", DB_FETCHMODE_ORDERED);
			if(!$trans_orderid){
				$msg = "Transaction was not found";
			}
			else{
				$msg = "Transaction has already been refunded";
			}
			
			return array("Skipping", "Could not refund transaction $orderid. $msg");
		}
		
		if($disbursed_date){
			return array("Skipping", "Could not refund transaction $orderid. Transaction has already been disbursed." );
		}
		
		if(date('Ymd') < $early_date && $group_type=='E'){
			return array("Skipping", "Could not refund Echeck transaction $orderid.  Transaction must be more than two days old." );
		}
		
		if(date('Ymd') >= $late_date){
			return array("Skipping", "Could not refund transaction $orderid. Transaction is more than three weeks old." );
		}
		
		list($successful, $msg) = $this->send_orderid($orderid, $appname);
		
		return array($successful, $msg);
	}
	
	function unrestricted_refund($orderid, $appname){
			
		list($trans_orderid, $late_date, $disbursed_date) = $this->DB->getRow("select
								th.orderid as orderid, 
								date_format((th.date  + interval 31 day), '%Y%m%d') as late_date,
								if (disb.req_date is not null, date_format(disb.req_date, '%m/%d/%Y'), '') as req_date
								from trans_history th
			left join disburse_orders on disburse_orders.orderid=th.orderid
			left join disburse disb on disb.id=disburse_orders.id
		where th.orderid='".mysql_real_escape_string($orderid)."' and th.status='F'", DB_FETCHMODE_ORDERED);

		if(!$trans_orderid){
				//check for refund
			list($trans_orderid) = $this->DB->getRow("select
								th.orderid as orderid
							from trans_history th
							where th.orderid='".mysql_real_escape_string($orderid)."' and th.status='G'", DB_FETCHMODE_ORDERED);
			if(!$trans_orderid){
				$msg = "Transaction was not found";
			}
			else{
				$msg = "Transaction has already been refunded";
			}
			
			return array("Skipping", "Could not refund transaction $orderid. $msg");
		}
		
		if(date('Ymd') >= $late_date){
			return array("Skipping", "Could not refund transaction $orderid. Transaction is more than 30 days old." );
		}
	
		list($successful, $msg) =$this->send_orderid($orderid, $appname);
		
		if($successful){
			
			if($disbursed_date){
				$summary="Refund made on a disbursed $appname transaction.";
				$description="Please collect on transaction $orderid.";
			
				$this->sendToMantis(92,58,97,40,40,$summary,$description);
			}
		}
		return array($successful, $msg);
	}
	
	function sendToMantis($project_id,$reporter_id,$handler_id,$priority,$severity,$summary,$description){
		$postvar=array(
			"action=new_issue",
			"project_id=$project_id",
			"reporter_id=$reporter_id",
			"handler_id=$handler_id",
			"priority=$priority",
			"severity=$severity",
			"summary=$summary",
			"category=Programming",
			"description=$description"
		);
		
		$query = join("&", $postvar);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://app02-php4.ark.org/mantis/manage/svc.php");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 40);
		
		$return_data = curl_exec($ch);		
		
		$return_error = curl_error($ch);
		
		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			
	}
	
	function send_orderid($orderid, $appname){
		$query = sprintf('do:action_refund_trans=1&key=%s&refund_trans_note=%s&orderid=%s', 
			'VY5uE9Zm8SapHGh3N662P8gs35E3a63e9f47xWj5',
			'refund for '.$appname.' transaction', 
			$orderid);
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->Opts['PT_WEB_SERVICE']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_POST, 1);	
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 40);
		
		$return_data = curl_exec($ch);		
		$return_error = curl_error($ch);
		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		preg_match('/(Error: [^<]+)/', $return_data, $m);
		if($m[1]){
			$msgs = $m[1]."\n";
		}
		
		// make sure it was refunded
			$status = $this->DB->getOne("select status from trans_history where orderid='".mysql_real_escape_string($orderid)."'");
		
		if($status == 'F'){
			$msgs .="Could not refund transaction $orderid due to error.\n";
		}
		
		if($msgs){
			return array(false, $msgs);
		}else{
			return array(true, "Transaction $orderid was refunded successfully.");
		}
	}
}
function readRefundConf($configFile="conf.properties"){

	$lines = file($configFile, 1) or die( "Can't read config file" ) ;

	$Opts=array();
	foreach ($lines as $line){
		if (strpos($line, "#") === 0){
			# Ignore commented line.
		}elseif (strpos($line, "=")){
			list($key, $value) = explode('=', $line) ;
			$key = strtoupper(trim($key)) ;
			$value = trim($value) ;
			#define("$key", $value) ;
			$Opts["$key"] = $value;
		}
	}
	return $Opts;
}
?>
