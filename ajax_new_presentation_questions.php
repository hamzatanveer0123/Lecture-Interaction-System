<?php
/**
 * Created by PhpStorm.
 * User: hamzamalik0123
 * Date: 21/12/2016
 * Time: 16:32
 */

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
include_once('corelib/mobile.php');

$sessionId = isset($_POST['sID'])? $_POST['sID']:"0";
$lastMsgID = isset($_POST['mID'])? $_POST['mID']:"0";
$position  = isset($_POST['pos'])? $_POST['pos']:"";

echo displayNewQuestions($sessionId, $lastMsgID);
function displayNewQuestions($sessionId, $lastMsgID)
{
    $uinfo = checkLoggedInUser();
    $loggedInUser = $uinfo['uname'];
    $questions = studentsQuestion::retrieve_sessionNewQuestions($sessionId, $lastMsgID);
    $out = "";
    if($questions) {
        global $position;
        for ($i = 0; $i < sizeof($questions); $i++) {

            $question       = $questions[$i];
            $qId            = $questions[$i]->id;
            $needsAttention = $questions[$i]->needs_attention;
            $studentId      = $questions[$i]->student_id;
            $questionText   = $questions[$i]->question;
            $pinLocation    = $questions[$i]->pin_location;
            $timeadded      = $questions[$i]->timeadded;

            if($question) {

                //TODO: keep track of number of characters as well.
                //if more characters than keep font size proportional.
                $fontSize = (13 + ($needsAttention / 0.5));

                if ($fontSize > 20) {
                    $fontSize = "20px";
                } else {
                    $fontSize = $fontSize . "px";
                }

                $timeAdded     = dataConnection::time2db($timeadded);
                if($timeAdded){
                    $timeNow = dataConnection::time2db(time());

                    $date1Timestamp = strtotime($timeAdded);
                    $date2Timestamp = strtotime($timeNow);

                    //find difference between two times
                    $difference = round(abs($date1Timestamp - $date2Timestamp));
                }

                $JUMP   = 1;
                $bottom = "bottom: ".($difference*$JUMP)."px";

                $pinClass = "";
                $pinned = 0;
                if(intval($pinLocation) > 0){
                    $pinClass = "pinned";
                    $bottom = "bottom: ".$pinLocation."px";
                    $pinned = 1;
                }

                $uinfo = checkLoggedInUser();

                if($position == "left"){
                    $pos    = "right";
                    $float  = "float: right !important;";
                    $badgeSide = "right: -10px !important";
                    $arrow = "<div class='arrow-right'></div>";
                    $clear = "";
                    if(true) {
                        $pin = "<span onclick='pinQuestion($qId, this, $pinned, $sessionId)' class='bubble-for-badge pin' style='left: -10px !important; right: auto !important; background-color: #009688;'>
                            <img class='card-badge' src='html/icons/icon-pin-right.png'/>
                        </span>";
                    }
                } else {
                    $pos    = "left";
                    $float  = "float: left !important;";
                    $badgeSide = "left: -16px !important; right: auto !important";
                    $arrow = "<div class='arrow-left'></div>";
                    $clear = "<div style='clear:both'></div>";
                    if(true) {
                        $pin = "<span onclick='pinQuestion($qId, this, $pinned, $sessionId)' class='bubble-for-badge pin' style='background-color: #009688;'>
                            <img class='card-badge' src='html/icons/icon-pin-left.png'/>
                        </span>";
                    }
                }

                $beingDiscussed = ifBeingDiscussed($question);

                $showBadge = "<span class='bubble-for-badge badge-discussion-$qId faa-pulse animated' style='$badgeSide'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

                $hideBadge = "<span class='bubble-for-badge badge-discussion-$qId faa-pulse animated' style='display: none; $badgeSide'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

                //to get new questions after the ID
                $hiddenQiD = "<input type='hidden' class='lastMsgID' style='float: $pos' value='$qId'>";

                //check if there is any reaction on question
                $badge = ($beingDiscussed) ? $showBadge : $hideBadge;
                $out .= "<div class='col-sm-12 ask-question question-$qId $pinClass' data-attention='$needsAttention' style='$float $bottom'>
                        <div class='question-content'>
                            $hiddenQiD
                            <p class='txt-question-$qId' style='font-size:$fontSize' title='Asked By: " . $studentId . "'>" . $questionText . "</p>
                            <div style='display: flex; text-align: center; color: #888'>
                            $badge
                            $pin
                            <span class='bubble-for-badge button-close badge-close-$qId' style='display: none; $badgeSide'>
                                <img class='card-badge' src='html/icons/icon-close.png'/>
                            </span>
                            </div>
                            $arrow
                        </div>
                     </div>$clear";
            } else { $out .= "<div class='col-sm-12 ask-question no-question'><p>no new questions ...</p></div>"; }
        }
    }
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
        //var_dump("last message " . ($difference) . " mins ago");

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
