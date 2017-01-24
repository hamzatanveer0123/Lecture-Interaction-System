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

$sessionId = isset($_REQUEST['sID'])? $_REQUEST['sID']:"0";
$lastMsgID = isset($_REQUEST['mID'])? $_REQUEST['mID']:"0";
$position  = isset($_REQUEST['pos'])? $_REQUEST['pos']:"";

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

            if($question) {

                $fontSize = (13 + ($needsAttention / 0.5));

                if ($fontSize > 40) {
                    $fontSize = "40px";
                } else {
                    $fontSize = $fontSize . "px";
                }

                $ifActive       = ifActive($question);
                $beingDiscussed = ifBeingDiscussed($question);

                $showBadge = "<span class='bubble-for-badge badge-discussion-$qId'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

                $hideBadge = "<span class='bubble-for-badge badge-discussion-$qId' style='display: none'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

                if($position == "left"){
                    $float = "float: right !important;";
                    $badgeSide = "right: -10px !important";
                    //to get new questions after the ID
                    $hiddenQiD = "<input type='hidden' style='float: right' class='lastMsgID' value='$qId'>";
                    $arrow = "<div class='arrow-right'></div>";
                    $clear = "";
                } else {
                    $float = "float: left !important;";
                    $badgeSide = "left: -16px !important; right: auto !important";
                    //to get new questions after the ID
                    $hiddenQiD = "<input type='hidden' style='float: left' class='lastMsgID' value='$qId'>";
                    $arrow = "<div class='arrow-left'></div>";
                    $clear = "<div style='clear:both'></div>";
                }

                //check if there is any reaction on question
                if ($ifActive || $beingDiscussed) {
                    $badge = ($beingDiscussed) ? $showBadge : $hideBadge;
                    $out .= "<div class='col-sm-12 ask-question question-$qId' data-attention='$needsAttention' style='$float; bottom: 0px'>
                            <div class='question-content'>
                                $hiddenQiD
                                <p class='txt-question-$qId' style='font-size:$fontSize' title='Asked By: " . $studentId . "'>" . $questionText . "</p>
                                <div style='display: flex; text-align: center; color: #888'>
                                $badge
                                <span class='bubble-for-badge button-close badge-close-$qId' style='display: none; $badgeSide'>
                                    <img class='card-badge' src='html/icons/icon-close.png'/>
                                </span>
                                </div>
                            </div>
                            $arrow
                         </div>$clear";
                } else {
                    $hide  = "hide-card-details";
                    $badge = ($beingDiscussed) ? $showBadge : $hideBadge;
                    $out .= "<div class='col-sm-12 ask-question question-$qId hide-unImpQuestion' data-attention='$needsAttention' style='$float;  bottom: 0px'>
                            <div class='question-content $hide'>
                                $hiddenQiD
                                <p class='txt-question-$qId' style='font-size:$fontSize' title='Asked By: " . $studentId . "'>" . $questionText . "</p>
                                <div style='display: flex; text-align: center; color: #888'>
                                $badge
                                <span class='bubble-for-badge button-close badge-close-$qId' onclick='closeQuestionCard($qId)' style='$badgeSide'>
                                    <img class='card-badge' src='html/icons/icon-close.png'/>
                                </span>
                                </div>
                            </div>
                            $arrow
                         </div>$clear";
                }
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
