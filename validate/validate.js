var serverAdress = "validate.php";
var showErros = true;

function validate(inputValue, fieldID)
{
    var data = "validationType=ajax&inputValue=" + inputValue + "$fieldId=" + fieldID;
    var settings =
    {
        url: serverAdress,
        type: "POST",
        async: true,
        complete: function (xhr, response, status) {
            if (xhr.responseText.indexOf("ERRNO") >= 0
                || xhr.responseText.indexOf("error:") >= 0
                || xhr.responseText.length == 0)
            {
                alert(xhr.responseText == 0 ? "Server error." : response);
            }
            result = response.result;
            fieldID = response.fieldID;
            message = document.getElementById(fieldID + "Failed");
            message.className = (result == "0") ? "error" : "hidden";
        },
        data: data,
        showErrors: showErrors
    };
    var XmlHttp = new XmlHttp(settings);
}

function setFocus()
{
    document.getElementById("txtUsername").focus();
}