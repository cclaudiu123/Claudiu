function process()
{
    oP = document.createElement("p");
    oHelloText = document.createTextNode("Hey dude! Here's a cool list of colors for you:");
    oP.appendChild(oHelloText);

    oUl = document.createElement("ul");

    oLiBlack = document.createElement("li");
    oBlack = document.createTextNode("Black");
    oLiBlack.appendChild(oBlack);

    oLiOrange = document.createElement("li");
    oOrange = document.createTextNode("Orange");
    oLiOrange.appendChild(oOrange);

    oLiPink = document.createElement("li");
    oPink = document.createTextNode("Pink");
    oLiPink.appendChild(oPink);

    oUl.appendChild(oLiBlack);
    oUl.appendChild(oLiOrange);
    oUl.appendChild(oLiPink);

    myDiv = document.getElementById("myDivElement");
    myDiv.appendChild(oHelloText);
    myDiv.appendChild(oUl);
}