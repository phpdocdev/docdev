<%
require_once('FormGenie.php');

$FG = new FormGenie($HTTP_POST_VARS);

$FG->processEmail();

$page = $FG->getConfirmationPage();
echo $page;
%>