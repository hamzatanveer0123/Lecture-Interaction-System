<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" media="Screen" href="html/yacrs-new-theme.css" />
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
include_once('corelib/mobile.php');

$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();

//store id of last message
$LAST_MESSAGE_ID = 0;

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
    $thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
    if($thisSession == false)
    {
        $template->pageData['mainBody'] = "<p><b>Invalid session or missing number.</b></p>".sessionCodeinput();
    }
    else
    {
        if(isset($_REQUEST['sessionID']))
            $sessionId = intval($_REQUEST['sessionID']);
        $template->pageData['mainBody'] .= loadingScreen();
        $template->pageData['mainBody'] .= displayQuestions($sessionId);
        $template->pageData['mainBody'] .= addQuestion($sessionId);

    }
    $template->pageData['logoutLink'] = loginBox($uinfo);

}

//header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );

echo $template->render();

function displayQuestions($sessionId)
{
    global $LAST_MESSAGE_ID;

    $out = "";
    $questions = studentsQuestion::retrieve_sessionQuestions($sessionId);

    $out .= "<div class='message-container'>";

    for ($i = 0; $i < sizeof($questions); $i++) {

        $question       = $questions[$i];
        $qId            = $questions[$i]->id;
        $needsAttention = $questions[$i]->needs_attention;
        $studentId      = $questions[$i]->student_id;
        $questionText   = $questions[$i]->question;
        //$timeadded      = $questions[$i]->timeadded;

        $LAST_MESSAGE_ID = $qId;

        $ifReaction = checkReaction($question);
        $hiddenQiD = "<input type='hidden' class='lastMsgID' value='$qId'>";

        if($ifReaction){
            $badge = "<span class='bubble-for-badge' onclick='plusplusNeedHelp($qId)'><img class='card-badge' src='html/icons/badge-like.png'/></span>";
            $class = "";
        } else {
            $badge = "<span class='bubble-for-badge close-unImpQuestion' onclick='plusplusNeedHelp($qId)'><img class='card-badge' src='html/icons/badge-like.png'/></span>";
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
            $out .= "<div class='col-sm-12 ask-question $class'>$hiddenQiD $badge<a class='link' href='ask_question_chat.php?quId=".$qId."&sessionID=$sessionId'><p title='Asked By: ".$studentId."'>".$questionText."</p></a></div>";
        } else {
            $out .= "no new questions<br/>";
        }
    }
    $out .= "</div>";
    return $out;
}

function addQuestion($sessionId)
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
    $out = "<div class='form-container'>";
    $out .= $questionForm->getHtml();
    $out .= "</div>";

    $out .= "";
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

function loadingScreen(){
    $out = "<div class='loading-screen' style='color: #197fcd; text-align: center; margin-top: 25%'>
            <i class=\"fa fa-circle-o-notch fa-spin fa-5x fa-fw\"></i>
            <span class=\"sr-only\">Loading...</span>
            </div>";
    return $out;
}

?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<script src="javascript/add_studentsQuestion.js"></script>
<script>
    setInterval("updateQuestions()",1000);
    function updateQuestions() {
        var lastIdInput = $(".lastMsgId").last()[0];
        var lastMsgVal  = lastIdInput.value;
        $.ajax({
            url: 'ajax_new_questions.php?sID=<?php echo $sessionId; ?>&mID='+lastMsgVal,
            success: function(html) {
//                $(".message-container").append("<center>New Questions</center>");
                if (html.indexOf("ask-question") >= 0){
                    $(".message-container").append(html);
                }
            }
        });
    }
</script>