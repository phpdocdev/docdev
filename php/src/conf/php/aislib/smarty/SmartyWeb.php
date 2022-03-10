<?php

function smarty_resource_web_source($tpl_name, &$tpl_source, &$smarty) {
  //  print("HELLO! ($tpl_name)\n");
  $fp1 = @fopen($tpl_name, "r");
  if ($fp1 == null) {
    return false;
  }
  $line = "";
  while(!feof($fp1)) {
    $line .= fgets($fp1, 1024);
  }
  $tpl_source = $line;
  return true;
}

function smarty_resource_web_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
  $url = parse_url($tpl_name);
  if ($scheme == "https") {
    $fp = fsockopen ($url['host'], 443, $errno, $errstr, 30);
  } else {
    $fp = fsockopen ($url['host'], 80, $errno, $errstr, 30);
  }
  $header = "HEAD ".$url['path']." HTTP/1.0\r\nHost: ".$url['host']."\r\n\r\n";
  if (!$fp){
    echo "$errstr ($errno)";
    return false;
  }
  fputs ($fp, $header);
  while (!feof($fp)) {
    $output .= fgets ($fp,128);
  }
  fclose ($fp);
  $begin = strpos($output, "Last-Modified: ") + 15;
  if ($begin == 15) {//no last-modified (like yahoo.com)
    return time();
  }
  $end = strpos($output, "\n", $begin);
  $time = substr($output, $begin, $end-$begin-1);
  $tpl_timestamp = strtotime($time);
  return true;
}

function smarty_resource_web_secure($tpl_name, &$smarty) {
  return true;
}

function smarty_resource_web_trusted($tpl_name, &$smarty) {
  return true;
}

?>