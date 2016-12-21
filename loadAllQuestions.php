<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
include_once('corelib/mobile.php');
$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];


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

    if(isset($_REQUEST['sessionID']))
        $sessionId = intval($_REQUEST['sessionID']);
    $template->pageData['mainBody'] .= displayQuestions($sessionId);

}

//header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );

echo $template->render();

function displayQuestions($sessionId)
{
    $out = "";
    $questions = studentsQuestion::retrieve_sessionQuestions($sessionId);

    $out .= "<div class='message-container'>";

    for ($i = 0; $i < sizeof($questions); $i++) {

        $question       = $questions[$i];
        $qId            = $questions[$i]->id;
        $needsAttention = $questions[$i]->needs_attention;
        $studentId      = $questions[$i]->student_id;
        $questionText   = $questions[$i]->question;

        $ifReaction = checkReaction($question);

        if($ifReaction){
            $badge = "<span class='bubble-for-badge'><img class='card-badge' src='html/icons/badge-like.png'/></span>";
            $class = "";
        } else {
            $badge = "<span class='bubble-for-badge close-unImpQuestion'><img class='card-badge' src='html/icons/badge-like.png'/></span>";
            $class = "no-reaction";
        }

        $needHelp = "<div class='needs-attention-badge' style='width: 100%;'>
                        <i  onclick='plusplusNeedHelp($qId)'  class='fa fa-exclamation' style='color: white; background: #197fcd; padding: 10px; border-radius: 10px' aria-hidden='true'>
                        ".$needsAttention."
                        </i>
                     </div>";

        //make div close when cross is pressed!
        if($questions[$i])
        {
            $out .= "<div class='col-sm-12 ask-question $class'>$badge<a class='link' href='ask_question_chat.php?quId=".$qId."&sessionID=$sessionId'><p title='Asked By: ".$studentId."'>".$questionText."</p></a>$needHelp</div>";
        } else {
            $out .= "no new questions<br/>";
        }
    }

    $out .= "</div>";
    return $out;
}

//need more conditions like
//if a question has been posted for more than 6hours - close it
//if a question has been answered - close it
//think of more
function checkReaction($q){
    $qId            = $q->id;
    $attentionCount = (int)$q->needs_attention;
    $answered       = $q->viewed;
    $messages       = chat_messages::retrieve_chat_messages_matching("question_id",$qId,"","","id DESC");

    if(isset($q)) {
        $date1 = dataConnection::time2db($messages[0]->posted);
        $date2 = dataConnection::time2db(time());

        $date1Timestamp = strtotime($date1);
        $date2Timestamp = strtotime($date2);

        $difference = ($date2Timestamp - $date1Timestamp)/60;
        //make a valid difference to make question inactive

        if(($difference > 60*10 || $attentionCount > 0) && (!$answered)){
            return true;
        }else {
            return false;
        }
    } else {
        return false;
    }
}

?>
<script src="javascript/loadAllQuestions.js"></script>
