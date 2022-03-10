<?php

/**
* Easy database searches
*
* @package  Roster
* @version  $Id: roster.php,v 1.4 2001/08/03 15:40:06 bob Exp $
* @author   Bob Sanders
* @see      makeSelect()
* @see      SQL
*
* To use this class you must create your own myRoster class. 
* You may then override or include selected functions to 
* customize the class
* 
* Here are some demos
* /web/html/demo/arpromise/search.php
* /web/html/pels/search.php
* /web/html/demo/towing/search.php
*/
  class Roster extends myRoster{
  
    function init(){
      $this->font='<font face="arial" size="2">';
    }
    
    /**
    * Description
    *
    * @param    mixed   
    * @access   public
    * @param    string  name
    * @return    boolean
    */

    function Roster($Settings, $SQL, $P, $G){

      $this->Config = $Settings['Config'];
      $this->Search = $Settings['Search'];
      $this->Results = $Settings['Results'];
      $this->Detail = $Settings['Detail'];
      $this->Purchase = $Settings['Purchase'];                        
      $this->Param = $Settings['SQL'];
      
      $this->Template = $this->Config['Template'];
      $this->TemplateKey = $this->Config['TemplateKey'] ? $this->Config['TemplateKey'] : 'CONTENT';
      $this->TemplateDirectory = $this->Config['TemplateDirectory'];

			$this->Trace = $this->Config['Trace'] ? 1 : 0;
			
      if(count($P)>0){
        $this->P = $P;
      }else{
        $this->P = $G;      
      }
			
			foreach($this->P as $k=>$v){
			  $this->P[$k] = ereg_replace(" +$", "", $v);
			}
			
      $this->SQL = $SQL;
      
			if( ($this->Config['DownloadLink']) && ($this->P['download']) ){
			  if($this->P['format'] == 'excel'){
				  $this->downloadExcel();
          exit;				
				}else if($this->P['format'] == 'text'){
				  $this->downloadText();				
  				exit;
				}
				
  		}			
			
      if($this->Template){

        ob_start();
        ob_implicit_flush(0);       
      }
      

      

      if($this->Param){
        $this->Param['VALUES'] = $this->P;
      }
  
  
      $this->init();
      
      if($this->P['SEARCH']){
        $this->doSearch();        
      }else if($this->P['DETAIL']){
        $this->doDetail();
      }else{
        $this->renderSearch();
      }
    
      if($this->Config['Datefile']){
        if( file_exists($this->Config['Datefile']) ){
          echo '<center><font face="arial" size="1"><i>Data current as of '.date( "l F dS, Y", filemtime($this->Config['Datefile']) ).'</i></font></center>';
        }
      }
    
		  if($this->Config['DownloadLink']){
			  ?>
          <center><font face="arial" size="1">
					Download database: 
					  <a href="<? echo $this->Config['Script'] ?>?download=1&format=excel">Excel</a> | 
					  <a href="<? echo $this->Config['Script'] ?>?download=1&format=text">Text (tab delimited)</a>
					</font></center>
				<?
			}
		
      if($this->Template){
        $Content = ob_get_contents();
        ob_end_clean(); 

        include "class.FastTemplate.php3";
        $tpl=new FastTemplate($this->TemplateDirectory);  
      
      	$tpl->define(array(
       	 "tt"=>$this->Template
      	));  
    
        $tpl->assign(array(
          $this->TemplateKey => $Content,
        ));
        
        $tpl->parse("GLOBAL", "tt");
        $tpl->FastPrint();           
      }    
    
    }// end func roster

		function Trace($msg){
		  if($this->Trace){
			  echo $msg . '<br>';
			}
		}

    function doDetail(){

      $sql = $this->makeDetailSQL($this->P, $this->SQL);
			$this->Trace($sql);
			
      $info = $this->SQL->fetchrow($sql);
      
      

      $info = $this->dformatter($info, &$this);
      
      if($this->Config['bgcolor']){
        echo '<table bgcolor="'.$this->Config['bgcolor'].'">';
      }else{
        echo '<table>';      
      }
      
      foreach($this->Detail['values'] as $k=>$v){
      
      
        echo '<tr>';
        
        $this->renderCell($v, '');
        $this->renderCell($info[$k], '');        
        
        echo '</tr>';
      }
      
      echo '</table>';
      
      $this->extraDetail($this->P, $this->SQL);
      
      $url = $this->makeUrl(array());      
      
      echo $this->font;
      echo '<a href="'.$this->Config['Script'].'?SEARCH=1&run='.($run).$url.'">Back to Search Results</a><br>';
      echo '<a href="'.$this->Config['Script'].'">Back to Search</a>';
      
    }

    function makeUrl($extra){
    
      $url = $this->Config['Script'] . '?';
    
      if( count($extra)>0 ){
        $t = array();
        foreach($extra as $k=>$v){
          array_push($t, urlencode($k) . '=' . urlencode($v));
        }
        $url .= implode('&', $t);
      }
    
      foreach($this->Search['Params'] as $k=>$v){
        $url .= '&' . $k . '=' . urlencode($this->P[$k]);
      }
      return $url;    
    }

    function matchRow($matches, $run){
      $fromText = ($run * $this->Config['Limit'])+1;
      $toText = (($run+1) * $this->Config['Limit']);      
    
      if($toText > $matches){ $toText = $matches; }

      $tr = '<tr><td colspan="'.(count($this->Results['showColumns'])).'"><table width="100%" cellpadding="0" cellspacing="0" border="0">';
    
      $tr .= '<tr>';
      $tr .= '<td></td>';                 
        
      $tr .= '<td align="center"> &nbsp; '.
             $this->font .
             "$fromText to $toText of $matches matches" .
             ' &nbsp; </td>';        
 
      $tr .= '<td></td>';            
              
      $tr .= '</tr></table></tr>';
      
      return $tr;
    
    }

    function topRow($matches, $run){
    
      $fromText = ($run * $this->Config['Limit'])+1;
      $toText = (($run+1) * $this->Config['Limit']);      
     
      if($matches <= $this->Config['Limit']){
        return '';
      }
      
    
      if($toText > $matches){ 
        $toText = $matches; 
      }
      
      

      $tr .= '<tr><td colspan="'.(count($this->Results['showColumns'])).'"><table width="100%" cellpadding="0" cellspacing="0" border="0">';
    
      $tr .= '<tr>';

      
      if($run == 0){
        $tr .= '<td>'.$this->font.'<a href="'.$this->Config['Script'].'"><< Back to Search </a></td>';            
      }else{
      $tr .= '<td>'.$this->font.
              '<a href="'.$this->makeUrl(array(SEARCH=>1, run=>$run-1)).'"> << Previous '.$this->Config['Limit'].'</a></td>';                      

      }
        
      $tr .= '<td align="center"></td>';        
 
      if($toText == $matches){
        $tr .= '<td align="right">'.$this->font.'<a href="'.$this->Config['Script'].'">Back to Search >> </a></td>';                  
      }else{       
        $tr .= '<td align="right">'.$this->font.
              '<a href="'.$this->makeUrl(array(SEARCH=>1, run=>$run+1)).'">Next '.$this->Config['Limit'].' >> </a></td>';                      
      }
        
      $tr .= '</tr></table></tr>';
      
      return $tr;
    
    }

    function doSearch(){

      $run = $this->P['run'];
      if(!$run){$run=0;}    

      if($this->Param){
        $sql = $this->SQL->makeSelect($this->Param);      
        $csql = $this->SQL->makeCount($this->Param);        
      }else{      
        $sql = $this->makeSQL($this->P, $this->SQL, $sql);      
        $csql = $this->makeCount($this->P, $this->SQL, $csql);                
      }
      
      $this->Trace($sql);
      $this->Trace($csql);			

      if($csql){
        list($matches) = $this->SQL->fetchrow($csql);
      }

      if($matches == 0){
        echo $this->font.'<b>No matches were found.</b><br>
              <a href="'.$this->Config['Script'].'">Please try again.</a></font>';
        return '';
      }


      $topRow = $this->topRow($matches, $run);
    
      $start = $run * $this->Config['Limit'];
      $qry = $this->SQL->Run($sql . ' LIMIT '.$start.','.$this->Config['Limit']);
      
      if($this->Config['bgcolor']){
        echo '<table bgcolor="'.$this->Config['bgcolor'].'" border=0 cellpadding="3" cellspacing="0">';
      }else{
        echo '<table border=1 cellpadding="3" cellspacing="0">';
      }      
      
      if($this->Config['Title']){
      echo '<tr>
              <td align="center" colspan="'.(count($this->Results['showColumns'])).'">
              '.$this->font.'<b>
               '.$this->Config['Title'].'
              </td>
            </tr>';
      }
      
      echo $this->matchRow($matches, $run); 
      echo $topRow;

      if(! method_exists($this, 'format_result') ){      
        echo '<tr>';      
        foreach($this->Results['showColumns'] as $k=>$v){
          $this->renderCell('<b>' . $v, 'bgcolor="'.$this->Results['HeaderBGColor'].'"');
        }      
        echo '</tr>';
      }
      

      
      if($this->Results['RowColor1'] && $this->Results['RowColor2']){
        $bgcolor = array(
          1 => 'bgcolor="'.$this->Results['RowColor1'].'"',
          -1 => 'bgcolor="'.$this->Results['RowColor2'].'"'
        );
        $idx = 1;
      }
      
      
      $cnt= ($run * $this->Config['Limit'])+1;
      $url = $this->makeUrl(array());      
      
      if( method_exists($this, 'format_result') ){      
        // start a single row
        echo '<tr><td colspan="'.(count($this->Results['showColumns'])).'"><table border=0 cellpadding="3" cellspacing="0">';
      }
      
      if(method_exists($this, 'makeResultHeader')){
        $this->makeResultHeader();
      }      
      
      $i=1;
      while($row = $this->SQL->fetch($qry)){
        if( method_exists($this, 'format_result') ){
          $this->format_result($row, $i*=-1);
        }else{
          $row = $this->formatter($row);       
          echo '<tr>';
          
          $row['roster_idx'] = $cnt;
          
          foreach($this->Results['showColumns'] as $k=>$v){
            if($this->Results['addLinks'][$k]){
//              $row[$k] = '<a href="'.$this->makeUrl(array(DETAIL=>1, 
//                                                    $k => $row[$this->Results['addLinks'][$k]])).'">'.$row[$k].'</a>';
              if( is_array($this->Results['addLinks'][$k]) ){
                $row[$k] = '<a href="'.$this->makeUrl(array(DETAIL=>1, 
							                                      $this->Results['addLinks'][$k][0] => $row[$this->Results['addLinks'][$k][1]])).'">'.$row[$k].'</a>';							
							}else{
                $row[$k] = '<a href="'.$this->makeUrl(array(DETAIL=>1, 
							                                      $k => $row[$this->Results['addLinks'][$k]])).'">'.$row[$k].'</a>';
              }
            }
            
  //              if($this->Results['detailLink'] == $k){
  //                $row[$k] = '<a href="'.$this->Config['Script'].'?DETAIL='.$row[$this->Config['UniqueId']].$url.'">'.$row[$k].'</a>';
  //              }
            
            if(!$row[$k]){ $row[$k] = '&nbsp;'; }
            $this->renderCell($row[$k], $bgcolor[$idx]);
          }
          $idx*=-1;        
          $cnt++;
          echo '</tr>';      
        }

      }
      
      if( method_exists($this, 'format_result') ){      
        // start a single row
        echo '</table></td></tr>';
      }      
      
      echo $topRow;      
      
      echo '<tr>
              <td colspan="'.(count($this->Results['showColumns'])).'" align="center">
                '.$this->font.'
                <a href="'.$this->Config['Script'].'">Back to Search</a>
              </td>
            </tr>';
      
      echo '</table>';
      
      if($this->Config['DateFile']){
        $dt = date("m/d/Y", filemtime($this->Config['DateFile']));
        echo '<p align="center"><font face="arial" size="2">Data current as of '.$dt.'</font></p>';
      }
      
    
    }

    function renderSearch(){
    
      if(!$this->Search['Layout']['SearchButton']){
        $this->Search['Layout']['SearchButton'] = 'Search';
      }
    
      echo '<form action="'.$this->Config['Script'].'" method="post">';
    

		
      echo '<table '.$this->Search['Layout']['Table'].'>';

      echo '<tr>
              <td colspan="2" align="left"><b>
              '.$this->font.'
              '.$this->Config['Title'].'
              </td>
            </tr>';
            
      if($this->Config['Description']){
        echo '<tr>
                <td colspan="2" align="left">
                '.$this->font.'
                '.$this->Config['Description'].'
                <br><br></td>
              </tr>';            
      }
      
		  $bg = $this->Search['Layout']['Left']['bgcolor']?'bgcolor="'.$this->Search['Layout']['Left']['bgcolor'].'"':'';
		
      foreach($this->Search['Params'] as $k=>$v){
        echo '<tr>';
        
        if( is_array($v) ){
          $v['title'] = $this->Search['Layout']['Left']['font'].$v['title'];
          switch($v['type']){
            case 'text':     $this->renderText($k,$v, $bg.' align="'.$this->Search['Layout']['Left']['align'].'"'); break;
            case 'hidden':   $this->renderHidden($k,$v, $bg.' align="'.$this->Search['Layout']['Left']['align'].'"'); break;            
            case 'select':   $this->renderSelect($k,$v, $bg.' align="'.$this->Search['Layout']['Left']['align'].'"'); break;
            case 'radio':    $this->renderRadio($k,$v, $bg.' align="'.$this->Search['Layout']['Left']['align'].'"'); break;
            case 'checkbox': $this->renderCheckbox($k,$v, $bg.' align="'.$this->Search['Layout']['Left']['align'].'"'); break;
          }
        }else{
//          echo $this->renderCell($this->Search['Layout']['Left']['font'].
//          , 'bgcolor="'.$this->Search['Layout']['Left']['bgcolor'].'" align="'.$this->Search['Layout']['Left']['align'].'"');        
          echo $this->renderCell($this->Search['Layout']['Left']['font'].$v, $bg.' align="'.$this->Search['Layout']['Left']['align'].'"');
          echo $this->renderCell('<input type="text" name="'.$k.'">', '');              
        
        }
        
        echo '</tr>';
      }
      
      echo '<tr>
              <td colspan="2" align="center">
              '.$this->font.'
              <input type="submit" name="SEARCH" value="'.$this->Search['Layout']['SearchButton'].'">
              </td>
            </tr>';
      
      echo '</table>';
    
      echo '</form>';
      
      if($this->Config['DownloadFile']){
        echo '<p align="center"><font face="arial" size="2"><a href="'.$this->Config['DownloadFile'].'">Download database</a></font></p>';
      }
      
    }
    
    function showResults(){
    
    }
    
    function renderCell($val, $attrib){
      echo '<td '.$attrib.'>'.$this->font.$val.'</td>'."\n";
    }
    
    
    
    function renderText($k,$v, $attrib=''){
      if($v['value']){ $value = 'value="'.$v['value'].'"'; }
      if($v['size']){ $size = 'size="'.$v['size'].'"'; }      
      
      echo $this->renderCell($v['title'], $attrib);
      echo $this->renderCell('<input type="text" name="'.$k.'" '.$value.' '.$size.'>', '');
      
    }

    function renderHidden($k,$v){
      if($v['value']){ $value = 'value="'.$v['value'].'"'; }
     
      echo $this->renderCell($v['title'], '');
      echo $this->renderCell('<input type="hidden" name="'.$k.'" '.$value.'>', '');
      
    }


    function renderSelect($k,$v, $attrib=''){

      if($v['multiple']){ $multiple = 'multiple'; }
      if($v['size']){ $size = 'size="'.$v['size'].'"'; }      

      foreach($v['values'] as $k1=>$v1){
        if($v['selected'] == $k1){
          $ch = 'selected';
        }else{
          $ch='';
        }
      
        $options.= '<option '.$ch.' value="'.$k1.'">'.$v1.'</option>'."\n";
      }

      echo $this->renderCell($v['title'], $attrib);
      echo $this->renderCell('<select name="'.$k.'" '.$size.' '.$multiple.'>'."\n".
                             $options."\n".
                             '</select>', '');
    
    
    }

    function renderRadio($k,$v, $attrib=''){
    
      echo $this->renderCell($v['title'], $attrib);
      foreach($v['values'] as $k1=>$v1){
        if($v['checked'] == $k1){
          $ch = 'checked';
        }else{
          $ch='';
        }
        $options .= '<input '.$ch.' type="radio" name="'.$k.'" value="'.$v1.'"> '.$k1.'<br>'."\n";
      }      
      echo $this->renderCell($options, '');
    
    }

    function renderCheckbox($k,$v, $attrib=''){

      if($v['value']){
        $val = $v['value'];
      }else{
        $val = 'ON';
      }
    
      if($v['checked'] == 1){
        $ch = 'checked';
      }else{
        $ch='';
      }        

      echo $this->renderCell($v['title'], $attrib);
      echo $this->renderCell('<input type="checkbox" name="'.$k.'" value="'.$val.'" '.$ch.'>', '');            
    
    }

	function downloadExcel(){
		// print out download header			
		header( "Content-type: application/x-gzip" ); 
		header( "Content-Disposition: atachment; filename=data.xls" ); 
		$this->outputData();
	}

	function downloadText(){
		// print out download header
		header( "Content-type: application/x-gzip" ); 
		header( "Content-Disposition: atachment; filename=data.txt" ); 
		$this->outputData();		
	}
		
		function outputData($fieldDel = "\t", $lineDel = "\n"){
			
			// print out data from database
			$qry = $this->SQL->Run($this->Config['DownloadSQL']);
			
			// print out data
			$header = 0;
			while( $row = $this->SQL->fetch($qry)){
				
			  if($header == 0){
				  // print the headers
					foreach($row as $k=>$v){
					  echo $k . $fieldDel;
					}
					echo $lineDel;
					$header=1;
				}
  			foreach($row as $k=>$v){
				  echo $v . $fieldDel;
				}
				echo $lineDel;								
			}		
		}
		
  
  }// end class roster

?>