$(document).ready(function(){
    $('#upload').click(function(){
        doUpload;
    });
    $('#uploadprogress').hide();
});

function doUpload(){
    var iframe;
    try{
        iframe = document.createElement('<iframe name="uploadiframe">');
    } catch(ex) {
        iframe = document.createElement('iframe');
        iframe.name = 'uploadiframe';
    }
    iframe.src = 'javascript:false';
    iframe.id = 'uploadiframe';
    iframe.className = 'iframe';
    document.body.appendChild(iframe);

    $('#form').atr('target','uploadiframe');
    $('#uploadform').hide();
    $('#uploadprogress').show();
    $('#uploadiframe').load(function(){
        $('#uploadprogress').hide();
        $('#uploadform').show();

        var result = $('body', this.contentWindow.document).html();

        if(result == 1){
            $('#result').html('The file upload was successful!');
        } else {
            $('#result').html('There was an error while uploading the file!');
        }
        setTimeout(function() {
            $('#uploadiframe').remove();
        }, 50);
    });
}