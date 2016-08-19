var chatURL = "chat.php";
var colorURL = "color.php";
var updateInterval = 2000;
var debugMode = true;
var lastMessageID = -1;

function displayError(message)
{
    alert("Error accessing the server! " + (debugMode ? message : ""));
}
function displayPHPError(error)
{
    displayError("Error number :" + error.errno + "\r\n" +
                 "Text: " + error.text + "\r\n" +
                 "Location: " + error.location + "\r\n" +
                 "Line:" + error.line + +  "\r\n");
}
function retrieveNewMessages()
{
    $.ajax({
        url: chatURL,
        type: 'POST',
        data: $.param({
            mode: 'RetrieveNew',
            id: lastMessageID
        }),
        dataType: 'json',
        error: function(xhr, textStatus, errorThrown){
            displayError(textStatus);
        },
        success: function(data, textStatus){
            if(data.errno != null) {
                displayPHPError(data);
            } else {
                readMessages(data);
            }
            setTimeout("retrieveNewMessages();", updateInterval);
        }
    });
}
function sendMessage()
{
    var message = $.trim($('#messageBox').val());
    var color = $.trim($('#color').val());
    var username = $.trim($('#userName').val());

    if(message != '' && color != '' && username != ''){
        var params = {
            mode: 'SendAndRetrieveNew',
            id: lastMessageID,
            color: color,
            name: username,
            message: message
        };
        $.ajax({
            url: 'chat.php',
            type: 'POST',
            data: $.param(params),
            dataType: 'json',
            error: function(xhr, textStatus, errorThrown){
                displayError(textStatus);
            },
            success: function(data, textStatus, errorThrown){
                if(data.errno != null){
                    displayPHPError(data);
                } else {
                    readMessages(data);
                }
                setTimeout("retrieveNewMessages();", updateInterval);
            }
        });
    }
}
function deleteMessages()
{
    $.ajax({
        url: chatURL,
        type: 'POST',
        success: function(data, textStatus){
            if(data.errno != null){
                displayPHPError(data);
            } else {
                readMessages(data);
            }
            setTimeout("retrieveNewMessages();", updateInterval);
        },
        data: $.param({
            mode: 'DeleteAndRetrieveNew'
        }),
        dataType: 'json',
        error: function(xhr, textStatus, errorThrown){
            displayError(textStatus);
        }
    });
}
function readMessages(data, textStatus)
{
    clearChat = data.clear;

    if(clearChat == 'true'){
        $('#scroll')[0].innerHTML = "";
        lastMessageID = -1;
    }
    if(data.messages.length > 0){
        if(lastMessageID > data.messages[0].id)
            return;
        lastMessageID = data.messages[data.messages.length - 1].id;
    }
    $.each(data.messages, function(i, message) {

        var htmlMessage = "";
        htmlMessage += "<div class=\"item\" style=\"color:" +
            message.color + "\">";
        htmlMessage += "[" + message.time + "] " + message.name +
            " said: <br/>";
        htmlMessage += message.message;
        htmlMessage += "</div>";

        var isScrolledDown = ($('#scroll')[0].scrollHeight -
        $('#scroll')[0].scrollTop <=
        $('#scroll')[0].offsetHeight);
        $('#scroll')[0].innerHTML += htmlMessage;
        $('#scroll')[0].scrollTop = isScrolledDown ? $('#scroll')[0].scrollHeight : $('#scroll')[0].scrollTop;
    });
}
$(document).ready(function()
{
    $('#userName').blur(
        function(e){
            if(this.value == "")
                this.value = "Guest" + Math.floor(Math.random() * 1000);
        }
    );
    $('#userName').triggerHandler('blur');
    $('#palette').click(
        function(e){
            var x = e.pageX - $('#palette').position().left;
            var y = e.pageY - $('#palette').position().top;

            $.ajax({
                url: colorURL,
                success: function (data, textStatus) {
                    if(data.errno != null){
                        displayPHPError(data);
                    } else {
                        $('#color')[0].value = data.color;
                        $('#sampleText').css('color', data.color);
                    }
                },
                data: $.param({
                    offsetx: x,
                    offsety: y,
                }),
                dataType: 'json',
                error: function(xhr, textStatus, errorThrown){
                    displayError(textStatus);
                }
            });
        }
    );
    $('#sampleText').css('color', 'black');
    $('#send').click(
        function(e){
            sendMessage();
        }
    );
    $('#delete').click(
        function(e){
            deleteMessages();
        }
    );
    $('#messageBox').attr('autocomplete','off');
    $('#messageBox').keydown(
        function (e) {
            if(e.keyCode == 13){
                sendMessage();
            }
        }
    );
    retrieveNewMessages();
});

