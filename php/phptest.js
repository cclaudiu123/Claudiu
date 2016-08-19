var xmlHttp = createXmlHttpRequestObject();

function createXmlHttpRequestObject()
{
    var xmlHttp;

    try{
        xmlHttp = new XMLHttpRequest();
    } catch (e){
        try{
            xmlHttp = new ActiveXObject("Microsoft.XMLHttp");
        } catch (e) {}
    }

    if(!xmlHttp){
        alert("Error creating the XMLHttpRequest object.");
    } else {
        return xmlHttp;
    }
}

function process()
{
    if(xmlHttp){
        try {
            xmlHttp.open("GET", "phptest.php", true);
            xmlHttp.onreadystatechange = handleRequestStateChange;
            xmlHttp.send(null);
        } catch (e) {
            alert("Can't connect to server:\n" + e.toString());
        }
    }
}

function handleRequestStateChange()
{
    if(xmlHttp.readyState == 4){
        if(xmlHttp.status == 200){
            try{
                handleServerResponse();
            } catch (e) {
                alert("Error reading the response:" + e.toString());
            }
        } else {
            alert("There was a problem retrieving the data:\n" + xmlHttp.statusText);
        }
    }
}

function handleServerResponse()
{
    var xmlResponse = xmlHttp.responseXML;
    xmlRoot = xmlResponse.documentElement;
    titleArray = xmlRoot.getElementsByTagName("title");
    isbnArray = xmlRoot.getElementsByTagName("isbn");

    var html = "";

    for(var i=0; i<titleArray.length; i++)
        html += titleArray.item(i).firstChild.data + ", "
            + isbnArray.item(i).firstChild.data + "<br/>";

    myDiv = document.getElementById("myDivElement");
    myDiv.innerHTML = "<p>Server says:</p>" + html;
}