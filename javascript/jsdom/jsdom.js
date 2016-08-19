var date = new Date();
var hour = date.getHours();

if(hour >= 22 || hour <= 5){
    document.write("You should go to sleep.");
} else {
    document.write("Hello world!");
}