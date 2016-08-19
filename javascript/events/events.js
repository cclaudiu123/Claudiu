function process()
{
    var string;
    string = "<ul>"
            + "<li>Black</li>"
            + "<li>Orange</li>"
            + "<li>Pink</li>"
            + "</ul>";

    myDiv = document.getElementById("myDivElement");
    myDiv.innerHTML = string;
}