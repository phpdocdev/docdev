<?php


function smarty_resource_db_source($tpl_name, &$tpl_source, &$smarty) {
  $conn = getSmartyDatabaseConnection();
  $stmt = $conn->Prepare("select name, timestamp, tag, template from gtemplate where name = ? ");
  $rs = $conn->Execute($stmt, array($tpl_name));
  if (($rs) && (!$rs->EOF)) {
    $tpl_source = $rs->fields[3];
    return true;
  }
  return false;
}

function smarty_resource_db_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
  $conn = getSmartyDatabaseConnection();
  $stmt = $conn->Prepare("select name, timestamp, tag, template from gtemplate where name = ? ");
  $rs = $conn->Execute($stmt, array($tpl_name));
  if (($rs) && (!$rs->EOF)) {
    $tpl_timestamp = strtotime($rs->fields[1]);
    return true;
  }
  return false;
}
function smarty_resource_db_secure($tpl_name, &$smarty) {
  return true;
}
function smarty_resource_db_trusted($tpl_name, &$smarty) {
  return true;
}


?>