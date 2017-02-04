/**
 * Created by Hamza Tanveer on 05/12/2016.
 */

//var height = $("#add_questionChat .submit").css("height");
// $("#add_questionChat .submit").css("width", height);


$('#add_questionChat').on('submit', function (e) {
    e.preventDefault();

    if ($('textarea[name="chatMessage"]').val()){
        $.ajax({
            type: 'POST',
            url: 'ajax_questionChat.php',
            data: $('form').serialize(),
            success: function (data) {
                console.log(data);
            },
            failure: function (data) {
                alert(data);
            }
        });
    } else {
        alert("please add message!");
    }
});

function selectBestAsnwer(questionId, messageId) {
    $.ajax({
        type: 'POST',
        url: 'ajax_setBestAnswer.php',
        data: {
            qID : questionId,
            mID : messageId
        },
        success: function (data) {
            console.log(data);
        },
        failure: function (data) {
            alert(data);
        }
    });
}