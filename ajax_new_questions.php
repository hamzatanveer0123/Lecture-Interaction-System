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
    $questions = studentsQuestion::retrieve_sessionNewQuestions($sessionId, $lastMsgID);
    $out = "";
    if($questions) {
        for ($i = 0; $i < sizeof($questions); $i++) {

            $question = $questions[$i];
            $qId = $questions[$i]->id;
            $needsAttention = $questions[$i]->needs_attention;
            $studentId = $questions[$i]->student_id;
            $questionText = $questions[$i]->question;

            $ifReaction = checkReaction($question);
            $hiddenQiD = "<input type='hidden' class='lastMsgID' value='$qId'>";

            if ($ifReaction) {
                $badge = "<span class='bubble-for-badge' onclick='plusplusNeedHelp($qId)'><img class='card-badge' src='html/icons/badge-like.png'/></span>";
                $class = "";
            } else {
                $badge = "<span class='bubble-for-badge close-unImpQuestion' onclick='plusplusNeedHelp($qId)'><img class='card-badge' src='html/icons/badge-like.png'/></span>";
                $class = "no-reaction";
            }

            $needHelp = "<div class='needs-attention-badge' style='width: 100%;'>
                        <i  onclick='plusplusNeedHelp($qId)'  class='fa fa-exclamation' style='color: white; background: #197fcd; padding: 10px; border-radius: 10px' aria-hidden='true'>
                        " . $needsAttention . "
                        </i>
                     </div>";

            //make div close when cross is pressed!
            if ($questions[$i]) {
                $out .= "<div class='col-sm-12 ask-question $class'>$hiddenQiD$badge<a class='link' href='ask_question_chat.php?quId=" . $qId . "&sessionID=$sessionId'><p title='Asked By: " . $studentId . "'>" . $questionText . "</p></a>$needHelp</div>";
            }

        }
    }
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