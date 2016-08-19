var xmlHttp = createXmlHttpRequestObject();

function createXmlHttpRequestObject()
{
    var xmlHttp;

    try{
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        try {
            xmlHttp = new ActiveXObject("Microsoft.XMLHttp");
        } catch (e) {

        }
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
        try{
            xmlHttp.open("GET", "async.txt", true);
            xmlHttp.onreadystatechange = handleRequestStateChange;
            xmlHttp.send(null);

            document.body.style.cursor = "wait";
        } catch (e) {
            alert("Can't connect to server:\n" + e.toString());
            dpcument.body.style.cursor = "default";
        }
    }
}

function handleRequestStateChange()
{
    myDiv = document.getElementById("myDivElement");

    if(xmlHttp.readyState == 1){
        myDiv.innerHTML += "Request status: 1 (loading) <br/>";
    } else if(xmlHttp.readyState == 2){
        myDiv.innerHTML += "Request status: 2 (loaded) <br/>";
    } else if(xmlHttp.readyState == 3){
        myDiv.innerHTML += "Request status: 3 (interactive) <br/>";
    } else if(xmlHttp.readyState == 4){
        document.body.style.cursor = "default";
        if(xmlHttp.status == 200){
            try{
                response = xmlHttp.responseText;
                myDiv.innerHTML += "Request status: 4 (complete). Server said: <br/>";
                myDiv.innerHTML += response;
            } catch (e){
                alert("Eroor reading the response:" + e.toString());
            }
        } else {
            alert("There was a problem retrieving the data:\n" + xmlHttp.statusText);
            document.body.style.cursor = "default";
        }
    }
}