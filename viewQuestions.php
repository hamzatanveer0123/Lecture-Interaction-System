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
        //update active users in the session!
//        //todo: update the activity using javascript after every min
//        $smemb = sessionMember::retrieve($uinfo['uname'], $thisSession->id);
//        if($smemb == false)
//        {
//            $smemb = new sessionMember();
//            $smemb->session_id = $thisSession->id;
//            $smemb->userID = $uinfo['uname'];
//            $smemb->name = $uinfo['gn'].' '.$uinfo['sn'];
//            $smemb->email = $uinfo['email'];
//            $smemb->joined = time();
//            $smemb->lastresponse = time();
//            $smemb->insert();
//        }
//        else
//        {
//            echo "updatd";
//            echo time();
//            $smemb->lastresponse = time();
//            $smemb->update();
//        }

        if(isset($_REQUEST['sessionID']))
            $sessionId = intval($_REQUEST['sessionID']);
        $template->pageData['mainBody'] .= loadingScreen();
        $template->pageData['mainBody'] .= addSortButton();
        $template->pageData['mainBody'] .= addToggleButton();
        $template->pageData['mainBody'] .= displayQuestions($sessionId);
        $template->pageData['mainBody'] .= addQuestion($sessionId);

    }
    $template->pageData['logoutLink'] = loginBox($uinfo);

}

//header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );

echo $template->render();

function displayQuestions($sessionId)
{
    global $uinfo;

    $loggedInUser = $uinfo['uname'];
    $questions = studentsQuestion::retrieve_sessionQuestions($sessionId);
    $out = "<div class='message-container'>";

    for ($i = 0; $i < sizeof($questions); $i++) {

        $question       = $questions[$i];
        $qId            = $questions[$i]->id;
        $needsAttention = $questions[$i]->needs_attention;
        $studentId      = $questions[$i]->student_id;
        $questionText   = $questions[$i]->question;


        if($question) {

            $fontSize = (13 + ($needsAttention / 0.5));

            if ($fontSize > 40) {
                $fontSize = "40px";
            } else {
                $fontSize = $fontSize . "px";
            }

            $ifActive       = ifActive($question);
            $beingDiscussed = ifBeingDiscussed($question);

            $liked = question_liked::checkIfLiked($qId, $loggedInUser);

            //to get new questions after the ID
            $hiddenQiD = "<input type='hidden' class='lastMsgID' value='$qId'>";

            $buttons = "<a class='card-buttons comments' href='ask_question_chat.php?quId=$qId&sessionID=$sessionId'>
                            <i class='fa fa-comments-o' aria-hidden='true'></i> discuss
                        </a>";

            if ($liked) {
                $buttons .= "<span style='color: #197fcd; font-weight: 800;' class='card-buttons badge-question-$qId' onclick='plusplusLike($sessionId,$qId,0)'>
                                <i class='fa fa-exclamation' aria-hidden='true'></i> important
                              </span>";
            } else {
                $buttons .= "<span class='card-buttons badge-question-$qId' onclick='plusplusLike($sessionId,$qId,1)'>
                                <i class='fa fa-exclamation' aria-hidden='true'></i> important
                              </span>";
            }

            $showBadge = "<span class='bubble-for-badge badge-discussion-$qId'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

            $hideBadge = "<span class='bubble-for-badge badge-discussion-$qId' style='display: none'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

            //check if there is any reaction on question
            if ($ifActive || $beingDiscussed) {
                $badge = ($beingDiscussed) ? $showBadge : $hideBadge;
                $out .= "<div class='col-sm-12 ask-question question-$qId' data-attention='$needsAttention'>
                            <div class='question-content'>
                                $hiddenQiD
                                <p class='txt-question-$qId' style='font-size:$fontSize'>" . $questionText . "</p>
                                <hr/>
                                <div style='display: flex; text-align: center; color: #888'>
                                $badge
                                <span class='bubble-for-badge button-close badge-close-$qId' style='display: none'>
                                    <img class='card-badge' src='html/icons/icon-close.png'/>
                                </span>
                                $buttons
                                </div>
                            </div>
                         </div>";
            } else {
                $hide  = "hide-card-details";
                $badge = ($beingDiscussed) ? $showBadge : $hideBadge;
                $out .= "<div class='col-sm-12 ask-question question-$qId hide-unImpQuestion' data-attention='$needsAttention'>
                            <div class='question-content $hide'>
                                $hiddenQiD
                                <p class='txt-question-$qId' style='font-size:$fontSize'>" . $questionText . "</p>
                                <hr/>
                                <div style='display: flex; text-align: center; color: #888'>
                                $badge
                                <span class='bubble-for-badge button-close badge-close-$qId' onclick='closeQuestionCard($qId)'>
                                    <img class='card-badge' src='html/icons/icon-close.png'/>
                                </span>
                                $buttons
                                </div>
                            </div>
                         </div>";
            }
        } else { $out .= ""; }
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
            $out = $questionForm->getHtml();
            break;
        case FORM_SUBMITTED_INVALID:
            $out = $questionForm->getHtml();
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
    $out .= "<p style='color: grey;'><span id='chars'>240</span> characters remaining</p>";
    $out .= "</div>";

    $out .= "";
    return $out;
}

//need more conditions like
//if a question has been posted for more than 6hours - close it
//if a question has been answered - close it
//think of more
function ifActive($q){
    $qId            = $q->id;
    $posted         = $q->timeadded;
    $attentionCount = (int)$q->needs_attention;
    //$answered       = $q->viewed;

    $timePosted     = dataConnection::time2db($posted);

    // check if question has been posted within last
    // two hours than keep it open!
    if($timePosted){
        $timeNow = dataConnection::time2db(time());

        $date1Timestamp = strtotime($timePosted);
        $date2Timestamp = strtotime($timeNow);

        //find difference between two times
        $difference = round(abs($date1Timestamp - $date2Timestamp) / 60,2);
        if($difference < 60*2) return true;
    }
    return false;
}

function ifBeingDiscussed($q){

    $qId            = $q->id;
    $posted         = $q->timeadded;
    $attentionCount = (int)$q->needs_attention;
    //$answered       = $q->viewed;

    // else if question has been posted more than two
    // hours ago then check other conditions and dec-
    // ide either it will stay open or close.

    // conditions for it to stay open
    // 1. If it has any active conversation since 2hrs
    // 2. If it has been marked important more than twice

    $messages = chat_messages::retrieve_chat_messages_matching("question_id",$qId,"","","id DESC");

    if($messages) {
        $date1 = dataConnection::time2db($messages[0]->posted);
        $date2 = dataConnection::time2db(time());

        $date1Timestamp = strtotime($date1);
        $date2Timestamp = strtotime($date2);

        $difference = round(abs($date1Timestamp - $date2Timestamp) / 60,2);

        //1. add '&& (!$answered)' if u want to check if
        //question has been answered or not...
        if(($difference < 60*2)){
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
            <i class='fa fa-circle-o-notch fa-spin fa-5x fa-fw'></i>
            <span class='sr-only'>Loading...</span>
            </div>";
    return $out;
}

function addToggleButton(){
    $out = "<span class='bubble-for-badge badge-toggle' style='cursor: pointer'>
            <i class='fa fa-eye fa-2x' aria-hidden='true' style='color: #ececec'></i>
            </span>";

    return $out;
}

function addSortButton(){
    $out = "<span class='bubble-for-badge badge-sort' style='cursor: pointer'>
            <i class='fa fa-sort-amount-desc' aria-hidden='true' style='color: #373737'></i>
            </span>";
    return $out;
}

?>
<script src="javascript/jquery.min.js"></script>
<script src="javascript/viewQuestions.js"></script>

<!--if new question is added, view all questions dont work-->

<script>
    setInterval("updateQuestions()",1000);
    function updateQuestions() {
        var lastMsgVal = 0;
        //improve this
        var divList    = $(".lastMsgID");
        divList.sort(function(a, b){
            return parseInt(a.value) > parseInt(b.value) ? 1 : -1;
        });
        var lastIdInput = divList.last()[0];
        if(lastIdInput != null){
            lastMsgVal  = lastIdInput.value;
        }
        $.ajax({
            type: "POST",
            url: "ajax_new_questions.php",
            data: {
                sID: <?php echo $sessionId; ?>,
                mID: lastMsgVal
            },
            success: function(html) {
                if (html.indexOf("ask-question") >= 0){
                    $(".message-container").append(html);
                }
            }
        });
    }

    setInterval("checkFontSize()",3000);
    function checkFontSize() {
        $.ajax({
            type: "POST",
            url: 'ajax_attention_changes.php',
            data: {
                sID: <?php echo $sessionId; ?>
            },
            success: function(output) {
                if (output){
                    var pair = output.split(',');
                    for(var i = 0; i < (pair.length-1); i++){
                        var val = pair[i].split("|");
                        console.log(val[0]+" -> "+val[1] + " -> " + val[2]);

                        var q       = parseInt(val[0]);
                        var font    = parseInt(val[1]);
                        var convo   = parseInt(val[2]);
                        //change font size if attention changes
                        var fontSize = (13+(font/0.5));
                        if(fontSize > 40) fontSize = "40px";
                        else fontSize = fontSize+"px";
                        $(".txt-question-"+q).css("font-size",fontSize);

                        //open/close question if in convo
                        //not closing any div right now
                        if(convo == 1){
                            $(".badge-discussion-"+q).show();
                            $(".badge-close-"+q).hide();
                            $(".question-"+q).click();
                        }
                        else {
                            //$(".badge-discussion-" + q).hide();
                            //$(".badge-close-" + q).show();
                            //closeQuestionCard(q);
                        }
                    }
                }
            }
        });
    }

    setInterval(updateUserActivity(), 60000);
    function updateUserActivity() {
        $.ajax({
            type: "POST",
            url: "ajax_update_user_activity.php",
            data: {
                session_id: <?php echo $sessionId; ?>
            },
            success: function(){
                console.log("updated!");
            }
        });
    }
</script>