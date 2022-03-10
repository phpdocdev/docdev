<?

//usage:
//	require('lib.google.php');			
//	$Google = new google(0);		// 1 to show debug info
//	$rs = $Google->search_google('governor', 'http://www.accessarkansas.org/ag/opinions/docs');
//
//	// then, show the results (the links back to google wont work)
//	echo $Google->showResults($rs, 'governor');
//	
//	// or, loop through and show the results your way
//	foreach($ResultSet['Results'] as $result){
//		// see below for spec
//	}

//	You can supply additional request parameters as a hash (3rd argument to search_google)
//		sitesearch = url of directory to restrict to, i.e. http://www.accessarkansas.org/ag/opinions
//		q          = search query
//		output     = 'xml'
//		client     = 'State_Sites'
//		site       = 'State_Sites'
//		getfields  = 'description' (meta tags?)
//		numgm      = 5 - Number of KeyMatch 
//		num        = 10 - Number of results desired per page
//		start      = item number to start the results at or NULL
//		sort       = 'date:D:S:d1' or NULL

// The ResultSet is a hash that contains information about the
// search and all of the matches. The key name are strange, but
// I tried to stick to the names used by Google, so if you need
// more information you can look at the Google XML docs
//
//		ResultSet
//			Params
//				q          => the original search query                
//				output     => xml or whatever                          
//				client     => "State_Sites"                            
//				site       => "State_Sites"                            
//				sitesearch => restrict value if only one site          
//				getfields  => meta tags to retrieve, i.e. "description"
//				restrict   => ?                                        
//				numgm      => ?                                        
//				num        => ?                                        
//				ip         => IP of requestor                          
//				access     => "p"                                      
//				SN         => start number (i.e. "1")                  
//				EN         => end number (i.e. "10")                   
//				M          => total number of matches 
//        Suggestions => other suggested spellings
//      KeyMatch   => array of key matches, each element is an array(link, text)
//			Results
//				Array(0)
//					N    => "1"
//					MIME => the mime type, i.e. NULL
//					U    => link to document
//					UE   => ? also link to document ?
//					RK   => Provides a general rating of the relevance of the search result (0-10)
//					date => date of document
//					desc => description of document (from meta tag?)
//					S    => text excerpt from document where match took place
//					size => size of document
//					cid  => Google's unique id for document, used for showing cached copy or HTML version

class google{

	function google($debug=0){
		$this->debug=$debug;
		$this->ResultSet = '';
		$this->lastData = '';
	}
		
	function msg($msg){
		if($this->debug){
			echo "$msg<br>";
		}
	}
		
	function search_google($words, $restrict, $params=array()){
		//$query = "http://google.ark.org:7800/search?q=".urlencode($words)."+-doctype:application/msword&output=xml&client=State_Sites&site=State_Sites&getfields=description&sitesearch=" . urlencode($restrict);		
		//$this->msg("query is $query");
		
		if( is_array($restrict) ){
			$sites = array();
			foreach($restrict as $site){
				$sites[] = "site:$site";
			}
			$words .= ' ' . join(' OR ', $sites);		
		}else{
			$params['sitesearch'] = $restrict;
		}

		$params['q'] = urlencode($words);	
		
		if(!$params['output']){
			$params['output']     = 'xml';
		}			
		if(!$params['client']){		
			$params['client']     = 'State_Sites'; //'test',
		}			
		if(!$params['site']){		
			$params['site']       = 'State_Sites';		
		}			
		if(!$params['getfields']){		
			$params['getfields']  = 'description';
		}			
		if(!$params['numgm']){		
			$params['numgm']      = 5;
		}			
		if(!$params['num']){		
			$params['num']        = 10;
		}			
		if(!$params['start']){		
			$params['start']      = $_GET['start']; //$start,
		}			
		if(!$params['sort']){		
			$params['sort']       = $_GET['sort'] ? 'date:D:S:d1' : NULL; //$sort ? 'date:D:S:d1' : NULL,				
		}				
		
		$query = $this->googleUrl($params);
		
		$this->msg('<b>'.htmlspecialchars($query).'</b>');
		
		if($_GET['cache']){
			$_GET['cache'] .= '+' . urlencode($_GET['words']);
			$query = 'http://google.ark.org:7800/search?q=cache:'.urlencode($_GET['cache']).'&ie=&site=State_Sites&output=xml_no_dtd&client=State_Sites&lr=&proxystylesheet=State_Sites&oe=';	
			$html = join("", file($query));	
			exit;
		}
		
		$RS = $this->parseResult($query);
		return $RS;
		//$Content = $this->showResults($RS);
		//return $Content;
	}	
	
	function googleUrl($p){
		
		$params = array();
		
		$url = 'http://google.ark.org:7800/search?';	
		
		foreach($p as $k=>$v){
			if($v){
				$params[] = "$k=$v";
			}
		}
		
		$url .= join('&', $params);	
		return $url;
	}
	
	
	function getRoot($path){
	  $parts = split("/", $path);
	  return $parts[2];
	}
	
	
	function getPath($path){
	  $parts = split("/", $path);
		array_shift($parts);
		array_shift($parts);	
		array_shift($parts);		
		
	  return join('/', $parts);
	}	
	
	function parseResult($query){
		$xml = join("", file($query));
		$this->msg('<pre>'.htmlspecialchars($xml).'</pre>');
		
		$xml_parser = xml_parser_create();
		xml_set_object($xml_parser, &$this);
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($xml_parser, "characterData");

		$this->ResultSet = array(
			Params  => array(),
			Results => array(),
			Suggestions => array(),
			KeyMatch => array(),
		);
		$this->lastData = '';

    if (!xml_parse($xml_parser, $xml, 1)) {
        die(sprintf("XML error: %s at line %d",
        	xml_error_string(xml_get_error_code($xml_parser)),
     		  xml_get_current_line_number($xml_parser)));
    }
				
		xml_parser_free($xml_parser);			
		
	/*	echo '<pre>';
		var_dump($this->ResultSet);
		echo '</pre>'; */
		
		return $this->ResultSet;
	}
	
	
	function startElement($parser, $name, $attrs) {

		// echo "Parsing $name<BR>\n";
		
		switch($name){	
			case 'PARAM':
				$this->ResultSet['Params'][$attrs[NAME]] = $attrs[VALUE];
			break;
		
			case 'RES':
				$this->ResultSet['Params']['SN'] = $attrs[SN];
				$this->ResultSet['Params']['EN'] = $attrs[EN];
			break;
			
			case 'R':
				$this->ResultSet[Results][] = array(
					N => $attrs[N],
					MIME => $attrs[MIME],
				);
			break;
			
			case 'FS':
				$this->ResultSet[Results][count($this->ResultSet[Results])-1]['date'] = $attrs[VALUE];
			break;
			
			case 'C':
				if($attrs[SZ]){
					$this->ResultSet[Results][count($this->ResultSet[Results])-1]['size'] = $attrs[SZ];
				}
				if($attrs[CID]){			
					$this->ResultSet[Results][count($this->ResultSet[Results])-1]['cid'] = $attrs[CID];
				}
			break;		
		
			case 'MT':
				$this->ResultSet[Results][count($this->ResultSet[Results])-1]['desc'] = $attrs[V];
			break;
			
			case 'SUGGESTION':
				$this->ResultSet[Suggestions][] = $attrs[Q];				
			break;
			
		}
		
		$this->lastData = '';
	
	}
	
	function endElement($parser, $name) {
		
		switch($name){
			
			case 'GL':
				$this->ResultSet[KeyMatch][] = $this->lastData;					
			break;
			
			case 'GD':
				$this->ResultSet[KeyMatch][] = $this->lastData;			
			break;
			
			case 'U':
				$this->ResultSet[Results][count($this->ResultSet[Results])-1][U] = $this->lastData;
			break;
			
			case 'UE':
				$this->ResultSet[Results][count($this->ResultSet[Results])-1][UE] = $this->lastData;
			break;
			
			case 'T':
				$this->ResultSet[Results][count($this->ResultSet[Results])-1][T] = $this->lastData;
			break;				
			
			case 'S':
				$this->ResultSet[Results][count($this->ResultSet[Results])-1][S] = $this->lastData;
			break;			
			
			case 'RK':
				$this->ResultSet[Results][count($this->ResultSet[Results])-1][RK] = $this->lastData;
			break;
								
			case 'M':
				$this->ResultSet['Params']['M'] = $this->lastData;
			break;			
			
		}
		
		$this->lastData = '';
	
	}
	
	function characterData($parser, $data){		
		$this->lastData .= $data;
	}	

	
	function showResults($RS, $words){
		//global $sort;
		//global $start;
	
		$words = stripslashes($words);
		
		ob_start();
		ob_implicit_flush(0);
		
		if($RS[Params][M]==0){				
			?>
			<p align="center">
			No results found matching "<b><?=$words?></b>"
			</p>
			<?
		}			
		
		if(count($RS['Suggestions'])>0){
			?>
				<p align="center">
				<font color="red">Did you mean:</font>
				<b><a href="<?=$_SERVER['SCRIPT_NAME']?>?words=<?=$RS['Suggestions'][0]?>&start=0&sort=date"><?=urldecode($RS['Suggestions'][0])?></a></b>
				</p>
			<?
		}

	
		if($RS[Params][M]==0){
			$Content = ob_get_contents();
			ob_end_clean(); 
			return $Content;
		}			
				
		?>		
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<td bgcolor="silver">
						<font face="arial" size="2">
						Searched for <b><?=$RS[Params][q]?></b>
					</td>
					<td align="right" bgcolor="silver">
						<font face="arial" size="2">
						Results <b><?=$RS[Params][SN]?></b> - <b><?=$RS[Params][EN]?></b> of <b><?=$RS[Params][M]?></b>
					</td>				
				</tr>
				<tr>
					<td><b>
						<font face="arial" size="2">
						<? if($RS[Params][SN] > 1): ?>						
							<a href="<?=$_SERVER['SCRIPT_NAME']?>?words=<?=urlencode($words)?>&sort=<?=$sort?>&start=<?=($RS[Params][SN]-11)?>"><< Previous</a>
						<? endif; ?>
						&nbsp;
						<? if($RS[Params][EN] < $RS[Params][M]): ?>
						 <a href="<?=$_SERVER['SCRIPT_NAME']?>?words=<?=urlencode($words)?>&sort=<?=$sort?>&start=<?=($RS[Params][EN])?>">Next >></a>
						<? endif; ?>
					</td>
					<td align="right">
						<font face="arial" size="2">
						Sort by: 
						<?if($sort):?>					
							<b>Date</b>
							/
							<a href="<?=$_SERVER['SCRIPT_NAME']?>?words=<?=urlencode($words)?>&start=0&sort=">Relevance</a>										
						<?else:?>
							<a href="<?=$_SERVER['SCRIPT_NAME']?>?words=<?=urlencode($words)?>&start=0&sort=date">Date</a>
							/
							<b>Relevance</b>
						<?endif;?>
					</td>				
				</tr>			
			</table>
			
			<?if(count($RS[KeyMatch])>0):?>
		 	<?for($i=0; $i<count($RS[KeyMatch]); $i+=2){?>									 		
			<p align=center>
	     <table cellpadding="5" cellspacing="0" border="0" height="40" width="100%">
	        <tr>
	           <td nowrap="0" bgcolor="#E8E8E8" height="40"><font face="arial" size="2">
							 	<b><a href="<?=$RS[KeyMatch][$i]?>"><?=$RS[KeyMatch][$i+1]?></a>
								<br><?=$RS[KeyMatch][$i]?></font>
							</td>
	           <td bgcolor="#E8E8E8" height="40" align="right" valign="top">
						 	<b><font size="-1" color="#2255aa">KeyMatch</font></b>
						</td>
	        </tr>
	     </table>			
			<?}?>		 
			<?endif;?>
			
		<?	
		foreach($RS[Results] as $Result){
		
			switch(true){
				case $Result[MIME] == 'application/pdf': $pre='<font size="-2"><b>[PDF]</b></font> '; break;
				case $Result[MIME] == 'application/ms-word': $pre='<font size="-2"><b>[MS WORD]</b></font> '; break;
				default: $pre = '';
			}
		
			?>
				<p>
					<?=$pre?><a href="<?=$Result[U]?>"><?=$Result[T]?></a>
					<?if($Result[S]): ?>
						<br>
					<? endif;?>
					<?=str_replace("<br>", "", $Result[S])?>		
					<br>
					
					<?
						$attrib = array();
						$url = $_SERVER['SCRIPT_NAME'] . "?&cache=".$Result['cid'].$Result[U].'&words='.urlencode($words);
	
	//					echo '('.$Result['cid'].')';
						
						if($Result[U]){
							$attrib[] = '<font color="green">...'.substr($Result[U], -35).'</font>';
						}
						if($Result['size']){
							$attrib[] = $Result['size'];
						}
						if($Result['date']){
							$attrib[] = $Result['date'];
						}					
						if($Result[MIME] == 'application/pdf'){
							$attrib[] = '<a href="'.$url.'">Text Version</a>';					
						}else if($Result[MIME] == 'application/ms-word'){
							// TODO						
							$attrib[] = '<a href="'.$url.'">Text Version</a>';
							//http://google.ark.org:7800/search?q=cache:nJJk9bH3HqY:http://www.sosweb.state.ar.us/register/june_02/002.00.02-005.pdf+fishing+license&ie=&site=State_Sites&output=xml_no_dtd&client=State_Sites&lr=&proxystylesheet=State_Sites&oe=
							//http://google.ark.org:7800/search?q=cache:nJJk9bH3HqY:http://www.sosweb.state.ar.us/register/june_02/002.00.02-005.pdf+fishing+license&ie=&site=State_Sites&output=xml_no_dtd&client=State_Sites&lr=&proxystylesheet=State_Sites&oe=
							//http://google.ark.org:7800/search?q=cache:Hu1-K8hCxuw:http://www.agfc.com/rules_regs/hunting_regs.html+fishing+license&ie=&site=State_Sites&output=xml_no_dtd&client=State_Sites&lr=&proxystylesheet=State_Sites&oe=
						}else{
							$attrib[] = '<a href="'.$url.'">Cached</a>';					
						}
						echo join(' - ', $attrib);
					?>				
				</p>
			<?
		}
		
		?>
		
			<?if($RS[Params][M] > 10):?>
			<p align="center">
				Result Page:  
							
				<? if($RS[Params][SN] > 1): ?>						
					<a href="<?=$_SERVER['SCRIPT_NAME']?>?words=<?=urlencode($words)?>&sort=<?=$sort?>&start=<?=($RS[Params][SN]-11)?>"><< Previous</a>
				<? endif; ?>
				
				<?
				
					$page = ($start / 10)+1;
					$pages = ($RS[Params][M]/10);
					$begin = 0;
	
					if($pages > 10){
					
						if($start){
							$limit = $page + 10;
							if($limit > 20){
								$begin = $limit - 20;
							}
						}else{
							$limit = 10;
						}
					
					}else{
						$limit = $pages;
					}
				
					for($i=$begin; $i<$limit; $i++){
						?>
							<? if( $RS[Params][SN] >= (($i*10)+1) && $RS[Params][EN] <= (($i+1)*10) ): ?>
								<b><?=($i+1)?></b>
							<? else: ?>
								<a href="<?=$_SERVER['SCRIPT_NAME']?>?words=<?=urlencode($words)?>&sort=<?=$sort?>&start=<?=($i*10)?>"><?=($i+1)?></a>
							<? endif; ?>						
						<?
					}
				?>
				
				<? if($RS[Params][EN] < $RS[Params][M]): ?>
				 <a href="<?=$_SERVER['SCRIPT_NAME']?>?words=<?=urlencode($words)?>&sort=<?=$sort?>&start=<?=($RS[Params][EN])?>">Next >></a>
				<? endif; ?>			
			</p>	
			<?endif;?>
		
			<p align="center">
				<form action="http://www.accessarkansas.org/search/gsearch.php" method="get">
				<input type="hidden" name="profile" value="<?=$profile?>">
				<input type="text" name="words" size="25" value="<?=urldecode($words)?>"> 
				<input type="submit" value="Search">
				<a href="<?=$_SERVER['SCRIPT_NAME']?>?help=1">Search Tips</a>
				</form>
			</p>
		<?
		
		
		$Content = ob_get_contents();
		ob_end_clean(); 
		return $Content;
	}
	
	
}

?>