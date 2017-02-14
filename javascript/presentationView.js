/**
 * Created by Hamza Tanveer on 05/12/2016.
 */

var timelineInterval;

$( document ).ready(function() {

    startTimelineInterval();

});


//=======================//
//intervals for time-line//
//=======================//
function startTimelineInterval() {
    timelineInterval = setInterval("timeline()",1000);
    $(".message-container").removeClass("make-scrollable");
    $(".ask-question").removeClass("bottom");

}

function stopTimelineInterval() {
    clearInterval(timelineInterval);
    $(".message-container").addClass("make-scrollable");
    $(".ask-question").addClass("bottom");

    //animate to bottom
    $(".message-container").animate({
        scrollTop: $(".message-container")[0].scrollHeight
    }, 2000);
}
//--------------------------------------------------------//

//listeners
$(".badge-toggle").click(function (e) {
    iconClass = $(".badge-toggle").find("i")[0];
    var className = $(iconClass).attr("class");
    if(className.indexOf("pause") < 0){
        $(iconClass).attr("class","fa fa-pause");
        $(".ask-question").click();
    } else {
        $(iconClass).attr("class","fa fa-play");
        $(".button-close").click();
    }

    if(!$(".ask-question").hasClass("bottom")){
        stopTimelineInterval();
    } else {
        startTimelineInterval();
    }
});

$(".badge-pin-container").click(function (e) {
    $(".pin-container").css("left","0%");
});

$(".badge-container-close").click(function (e) {
    $(".pin-container").css("left","-30%");
});

//standard
// function pinQuestion(qID, thisPin, isPinned) {
//     console.log(qID);
//     console.log(thisPin);
//
//     if(isPinned == 0){
//         var bottom = $(".question-"+qID).css("bottom");
//         $(".question-"+qID).addClass("pinned");
//         // $(".question-"+qID).css("bottom", bottom + " !important");
//     } else {
//         var bottom = 0;
//         $(".question-"+qID).removeClass("pinned");
//     }
//
//     $.ajax({
//         type: 'post',
//         url: 'ajax_setQuestionPosition.php?questionID='+qID+'&position='+bottom,
//         success: function (data) {
//             console.log(data);
//             if(isPinned == 0){
//                 $(thisPin).attr("onclick","pinQuestion("+qID+",this,1)");
//                 var question = $($(".question-"+qID)[0]).find("p").text();
//                 $(".pinned-questions").append("<div class='pin-container-question'>"+question+"</div>");
//
//             } else {
//                 $(thisPin).attr("onclick","pinQuestion("+qID+",this,0)");
//             }
//         },
//         failure: function (data) {
//             console.log(data);
//         }
//     });
// }

//pin an important question
function pinQuestion(qID, thisPin, isPinned, sessionID) {

    // achieved
    // if(isPinned == 0){
    //     var bottom = $(".question-"+qID).css("bottom");
    // } else {
    //     var bottom = 0;
    // }
    //
    // if(bottom == "auto"){
    //     bottom = 1;
    // }

    //no need to keep track of pin location
    //so using inPinned as booleant to show
    //if question is pinned or not.........

    var bottom;

    if(isPinned == 0)
        bottom = 1;
    else
        bottom = 0;

    $.ajax({
        type: 'POST',
        url: 'ajax_setQuestionPosition.php',
        data:{
            questionID: qID,
            position: bottom
        },
        success: function (data) {
            console.log(data);
            if(isPinned == 0){
                $(thisPin).attr("onclick","pinQuestion("+qID+",this,1,"+sessionID+")");
                var question = escapeHTML($($(".question-"+qID)[0]).find("p").text());
                var link = "<a href='ask_question_chat.php?qId="+qID+"&sessionID="+sessionID+"'>"+question+"</a>"
                var html = "<div class='pin-container-question pinned-"+qID+"'>"+link+"</div>";
                $(".pinned-questions").append(html);

            } else {
                $(thisPin).attr("onclick","pinQuestion("+qID+",this,0,"+sessionID+")");
                $(".pinned-"+qID).remove();
            }
        },
        failure: function (data) {
            console.log(data);
        }
    });
}

function clearPinned(qID) {
    $(".pinned-"+qID).remove();
    bottom
    $.ajax({
        type: 'POST',
        url: 'ajax_setQuestionPosition.php',
        data:{
            questionID: qID,
            position: bottom
        },
        success: function (data) {
            console.log(data);
        },
        failure: function (data) {
            console.log(data);
        }
    });
}

function timeline() {

    // Archived
    //to scroll to divs bottom
    // $(".message-container").animate({
    //     scrollTop: $(".message-container")[0].scrollHeight
    // }, 1000);

    var divs = $(".ask-question");

    for(var i=0; i < divs.length; i++){
        var thisDiv = $(divs[i]);
        var bottomValue = $(thisDiv).css("bottom");
        if(bottomValue == "auto"){
            var  bottom = 0;
        } else {
            var bottom = bottomValue.split("px")[0];
        }
        $(thisDiv).css("bottom",(parseInt(bottom)+1)+"px");
    }
}

function escapeHTML(text) {
    return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Archived
//
// used for auto shrinking
//
// function checkOverflow() {
//
//     to have scrolling
//     $(".message-container").addClass("make-scrollable");
//     $(".message-container").scrollTop($(".message-container")[0].scrollHeight);
//
//
//     this is to set auto shrink
//     if ($('.message-container')[0].scrollHeight >  $('.message-container').height()) {
//
//         var overflown  =  $('.message-container')[0].scrollHeight;
//         var visibleHeight =  $('body').height();
//         var difference = overflown -  visibleHeight;
//
//         if(difference > 1){
//             var fontSize  = $(".ask-question p").css("font-size");
//             var fontValue = parseInt(fontSize.split("px")[0]);
//
//             var zoom = $(".ask-question").css("zoom");
//             $(".ask-question").css("zoom",zoom-0.05);
//             // $(".ask-question").css("-moz-transform","scale("+(zoom-0.05)+","+(zoom-0.05)+");");
//
//
//
//             // if(fontValue > 8) {
//             //     $(".ask-question p").css("font-size", fontValue - 1 + "px");
//             // }
//
//        }
//     }
// }

