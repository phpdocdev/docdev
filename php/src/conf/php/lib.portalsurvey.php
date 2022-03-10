<?

function survey_user($projectid, $id){
	return;

	require_once('DB.php');
	
	$DB = DB::connect('mysql://portalsurvey@proddb/portalsurvey');
	
	$result = $DB->getOne("select submitted from accounts where id='$id'");

	if( !$result ){
		?>
			<script language="javascript" src="https://www.ark.org/portalsurvey/portalsurvey.js"></script>
			<script language="javascript">  showSurvey(<?=$projectid?>); </script>		
		<?
		$DB->query("insert into accounts(id, submitted)values('$id', NOW());");
	}
	
}

?>
