/**
 * Created by Hamza Tanveer on 05/12/2016.
 */

var timelineInterval;

$( document ).ready(function() {

    setListener();
    startTimelineInterval();

});

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

$('#add_studentsQuestion').on('submit', function (e) {
    e.preventDefault();
    if ($('textarea[name="question"]').val()) {
        $.ajax({
            type: 'post',
            url: 'ajax_studentsQuestion.php',
            data: $('form').serialize(),
            success: function (data) {
                console.log(data);
                $(".message-container").animate({ scrollTop: $(".message-container").prop("scrollHeight")}, "slow");
                $('textarea[name="question"]').val("");
            },
            failure: function (data) {
                alert(data);
            }
        });
    } else {
        alert("please add a question!");
    }
});

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
    $(".pin-container").css("left","-40%");
});

$(".badge-sort").click(function (e) {
    sort();
});

function sort() {
    var divList = $(".ask-question");
    divList.sort(function(a, b){
        return $(a).data("attention") < $(b).data("attention") ? 1 : -1;
    });
    $(".message-container").html(divList);
    setListener();
}

function closeQuestionCard(qId) {

    var qDiv = $(".badge-close-"+qId).parentsUntil($("ask-question"),".col-sm-12");

    var contentDiv = $(qDiv).find('div')[0];
    $(contentDiv).hide();
    $(qDiv).addClass("hide-unImpQuestion");

    //to create smooth animation  of opening and closing
    // var WAIT = 40;
    // setTimeout(function(){
    //     //toggle to open and close card
    //     $(qDiv).addClass("hide-unImpQuestion");
    // }, WAIT);
}

function plusplusLike(sId,qId,liked) {
    $.ajax({
        type: 'get',
        url: 'ajax_likeQuestion.php?sessionID='+sId+'&questionID='+qId+'&liked='+liked,
        success: function (data) {
            console.log(data);
            if(liked == 1) {
                $(".badge-question-" + qId).css("color", "#197fcd");
                $(".badge-question-" + qId).css("font-weight", "800");
                $(".badge-question-" + qId).attr("onclick","plusplusLike("+sId+","+qId+",0)");
            }
            else {
                $(".badge-question-" + qId).css("color", "#888");
                $(".badge-question-" + qId).css("font-weight", "300");
                $(".badge-question-" + qId).attr("onclick","plusplusLike("+sId+","+qId+",1)");
            }
        },
        failure: function (data) {
            alert(data);
        }
    });
}


function setListener() {

    //expand on click
    $(".hide-unImpQuestion").click(function (e) {

        //if close button is pressed!
        var target = e.target.className;
        if(target == "card-badge" || (target.indexOf("bubble-for-badge") == 0)) return false;

        //to create smooth animation  of opening and closing
        var WAIT = 40;

        var cardContent     = $(this).find('.question-content');
        // $(cardContent).show();

        setTimeout(function(){
            $(cardContent).show();
        }, WAIT);

        //toggle to open and close card
        $(this).removeClass("hide-unImpQuestion");
        checkOverflow();
    });

}

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

//experimental
function pinQuestion(qID, thisPin, isPinned, sessionID) {
    console.log(qID);
    console.log(thisPin);

    if(isPinned == 0){
        var bottom = $(".question-"+qID).css("bottom");
        $(".question-"+qID).addClass("pinned");
    } else {
        var bottom = 0;
        $(".question-"+qID).removeClass("pinned");
    }

    $.ajax({
        type: 'post',
        url: 'ajax_setQuestionPosition.php?questionID='+qID+'&position='+bottom,
        success: function (data) {
            console.log(data);
            if(isPinned == 0){
                $(thisPin).attr("onclick","pinQuestion("+qID+",this,1,"+sessionID+")");
                var question = $($(".question-"+qID)[0]).find("p").text();
                var link = "<a href='ask_question_chat.php?quId="+qID+"&sessionID="+sessionID+"'>"+question+"</a>"
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
        if(!$(thisDiv).hasClass("pinned")){
            $(thisDiv).css("bottom",(parseInt(bottom)+2)+"px");
        }
    }
}

//
// Archived
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

