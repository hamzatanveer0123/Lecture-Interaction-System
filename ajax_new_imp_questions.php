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

echo displayNewQuestions($sessionId, $lastMsgID);
function displayNewQuestions($sessionId, $lastMsgID)
{
    $uinfo = checkLoggedInUser();
    $loggedInUser = $uinfo['uname'];
    $questions = studentsQuestion::retrieve_sessionNewImpQuestions($sessionId, $lastMsgID);
    $out = "";
    if($questions) {
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
                    $buttons .= "<span class='card-buttons button-pressed'onclick='plusplusLike($sessionId,$qId,0)'>
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
                                <p class='question-$qId' style='font-size:$fontSize' title='Asked By: " . $studentId . "'>" . $questionText . "</p>
                                <hr/>
                                <div style='display: flex; text-align: center; color: #888'>
                                $badge
                                <span class='bubble-for-badge badge-close-$qId' style='display: none'>
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
                                <p class='question-$qId' style='font-size:$fontSize' title='Asked By: " . $studentId . "'>" . $questionText . "</p>
                                <hr/>
                                <div style='display: flex; text-align: center; color: #888'>
                                $badge
                                <span class='bubble-for-badge badge-close-$qId' onclick='closeQuestionCard($qId)'>
                                    <img class='card-badge' src='html/icons/icon-close.png'/>
                                </span>
                                $buttons
                                </div>
                            </div>
                         </div>";
                }
            }
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
