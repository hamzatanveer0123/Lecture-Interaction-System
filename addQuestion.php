<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/questionTypes.php');
include_once('corelib/mobile.php');
$template = new templateMerge($TEMPLATE);
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$_REQUEST['sessionID']}&mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$_REQUEST['sessionID']}&mode=mobile'>Use mobile mode</a>";

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php">YACRS</a></li>';


if((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
{
    $serverURL = 'https://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 443)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
else
{
    $serverURL = 'http://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 80)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
$serverURL .= $_SERVER['SCRIPT_NAME'];

if($uinfo==false)
{
    header("Location: index.php");
    // actually should allow join a session as guest...
}
else
{
    $template->pageData['mainBody'] = '';
    $thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
    if($thisSession == false)
    {
        $template->pageData['mainBody'] = "<p><b>Invalid session or missing number.</b></p>".sessionCodeinput();
    }
    else
    {
        $template->pageData['breadcrumb'] .= "<li>{$thisSession->title}</li>";
        $template->pageData['breadcrumb'] .= '</ul>';

        if(isset($_REQUEST['sessionID']))
            $sessionId = intval($_REQUEST['sessionID']);
        $template->pageData['mainBody'] .= addQuestion($sessionId, true);

    }
    //$template->pageData['mainBody'] .= '<pre>'.print_r($uinfo,1).'</pre>';
    //$template->pageData['mainBody'] .= '<pre>'.print_r($smemb,1).'</pre>';
    $template->pageData['logoutLink'] = loginBox($uinfo);

}

echo $template->render();

function addQuestion($sessionId, $forceTitle=false)
{
    $questionForm = new add_studentsQuestion();
    switch($questionForm->getStatus())
    {
        case FORM_NOTSUBMITTED:
            //$exampleform->setData($existingdata);
            $output = $questionForm->getHtml();
            break;
        case FORM_SUBMITTED_INVALID:
            $output = $questionForm->getHtml();
            break;
        case FORM_SUBMITTED_VALID:
            $data = new stdClass();
            $questionForm->getData($data);
            // Do stuff with $data
            // A redirect is likely here, e.g. header('Location:document.php?id='.$data->id);
            break;
        case FORM_CANCELED:
            header('Location:index.php');
            break;
    }

    $data = (object) ['session_id' => $sessionId];
    $questionForm -> setData($data);
    $out = $questionForm->getHtml();

    $uinfo = checkLoggedInUser();
    $questions = studentsQuestion::retrieve_studentsQuestion_matching("student_id", $uinfo['uname']);

    $out .= "<div class='row'>";
    $out .= "<h1>Recently Asked Questions</h1>";
    for ($i = 0; $i < sizeof($questions); $i++) {
        if($questions[$i])
        {
            $out .= "<div class='col-sm-12'><p class='ask-question' title='Asked By: ".$questions[$i]->student_id."'>".$questions[$i]->question."</p></div>";
        }
    }
    $out .= "</div>";

    return $out;
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<script src="javascript/add_studentsQuestion.js"></script>
