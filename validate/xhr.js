function XmlHttp(settings)
{
    this.settings = settings;

    var url = location.href;
    if(settings.url)
        url = settings.url;

    var contentType = "application/x-www-form-urlencoded";
    if(settings.contentType)
        contentType = settings.contentType;

    var type = "GET";
    if(settings.type)
        type = settings.type;

    var data = null;
    if(settings.data) {
        data = settings.data;
        if(type == "GET")
            url = url + "?" + data;
    }

    var async = true;
    if(settings.async)
        async = settings.async;

    var showErros = true;
    if(settings.showErrors)
        showErrors = settings.showErrors;

    var xhr = XmlHttp.create();
    xhr.open(type, url, async);
    xhr.onreadystatechange = onreadystatechange;
    xhr.setRequestHeader("Content-Type", contentType);
    xhr.send(data);

    function displayError(message)
    {
        if(showErrors){
            alert("Error encountered: \n" + message);
        }
    }

    function readResponse()
    {
        try{
            var contentType = xhr.getResponseHeader("Content-Type");
            if(contentType == "application/json"){
                response = JSON.parse(xhr.responseText);
            } else if(contentType == "text/xml") {
                response = xhr.responseXml;
            } else {
                response = xhr.responseText;
            }

            if(settings.complete)
                settings.complete (xhr, response, xhr.status);
        } catch (e) {
            displayError(e.toString());
        }
    }

    function onreadystatechange()
    {
        if (xhr.readyState == 4){
            if(xhr.status == 200){
                try{
                    readResponse();
                } catch (e) {
                    displayError(e.toString());
                }
            }
        } else {
            displayError(xhr.statusText);
        }
    }
}

XmlHttp.create = function ()
{
    var xmlHttp;
    try{
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        try{
            xmlHttp = new ActiveXObject("Microsoft.XMLHttp");
        } catch (e) {
        }
    }
    if(!xmlHttp) {
        alert("Error creating the XMLHttpRequest object.");
    } else {
        return xmlHttp;
    }
}