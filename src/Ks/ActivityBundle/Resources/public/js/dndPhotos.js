//$(function(){
    
    //var dropbox = $('#dropbox'), 
    

    //Pour l'ajout d'une photo sur un article
    /*dropbox.filedrop({
        // The name of the $_FILES entry:
        paramname:'pic',

        maxfiles: 5,
        maxfilesize: 2,
        url: Routing.generate('ksActivity_imagesUpload', {  "uploadDirName" : "wiki" }),//'post_file.php',

        uploadFinished:function(i,file,response){

            // response is the JSON object that post_file.php returns
            if ( response.uploadResponse == 1 ) {
                $.data(file).addClass('done');
                $( "#article_uploaded_photos" ).append(
                    $("<input>", {})
                        .addClass("uploaded_photo")
                        .val(response.tmpPath)
                );

            } else {
                $.data(file).addClass('fail');
                alert( response.errorMessage );
            }
        },

        error: function(err, file) {
            switch(err) {
                case 'BrowserNotSupported':
                    showMessage('Your browser does not support HTML5 file uploads!');
                    break;
                case 'TooManyFiles':
                    alert('Too many files! Please select 5 at most! (configurable)');
                    break;
                case 'FileTooLarge':
                    alert(file.name+' is too large! Please upload files up to 2mb (configurable).');
                    break;
                default:
                    break;
            }
        },

        // Called before each upload is started
        beforeEach: function(file){
            if(!file.type.match(/^image\//)){
                alert('Only images are allowed!');

                // Returning false will cause the
                // file to be rejected
                return false;
            }
        },

        uploadStarted:function(i, file, len){
            createImage(file);
        },

        progressUpdated: function(i, file, progress) {
            $.data(file).find('.progressBar').width(progress);
        }

    });*/
    
    //Pour l'ajout d'une photo sur le fil d'activité
    
    
    function attachDndEvent(elt, uploadDirName, maxfiles, maxfilesize, erase, imgPreviewWidth, imgWidthHeight) {
        var uploadedPhotosBloc = elt.find(".uploaded_photos");
        var message = $('.message', elt);
        var uploaded_photos = elt.find(".uploaded_photos");
        
        if (!erase) erase = false;
        if (!imgPreviewWidth) imgPreviewWidth = 100;
        if (!imgWidthHeight) imgWidthHeight = 100;
        elt.filedrop({
            // The name of the $_FILES entry:
            paramname:'pic',

            maxfiles: maxfiles,
            maxfilesize: maxfilesize,
            url: Routing.generate('ksActivity_imagesUpload', { "uploadDirName" : uploadDirName}),

            uploadFinished:function(i,file,response){console.log("uploadFinished")

                // response is the JSON object that post_file.php returns
                if ( response.uploadResponse == 1 ) {
                    $.data(file).addClass('done');
                    uploadedPhotosBloc.append(
                        $("<input>", {})
                            .addClass("uploaded_photo")
                            .val(response.tmpPath)
                    );
                        
                    //uploaded_photos.change();

                } else {
                    $.data(file).addClass('fail');
                    alert( response.errorMessage );
                }
            },

            error: function(err, file) {console.log("error")
                switch(err) {
                    case 'BrowserNotSupported':
                        showMessage('Your browser does not support HTML5 file uploads!');
                        break;
                    case 'TooManyFiles':
                        alert('Too many files! Please select ' + maxfiles +' at most!');
                        break;
                    case 'FileTooLarge':
                        alert(file.name+' is too large! Please upload files up to ' + maxfilesize +'mb.');
                        break;
                    default:
                        alert("!!!")
                        break;
                }
            },

            // Called before each upload is started
            beforeEach: function(file){console.log("before")
                //On efface les images existantes
                if( erase ) {
                    var previews = elt.find(".preview");
                    var uploaded_photos_inputs = elt.find(".uploaded_photos > input");
                    
                    previews.each(function () {
                        $(this).remove();
                    });
                    
                    uploaded_photos_inputs.each(function () {
                        $(this).remove();
                    });
                    
                }
                if(!file.type.match(/^image\//)){
                    alert('Only images are allowed!');

                    // Returning false will cause the
                    // file to be rejected
                    return false;
                }
            },

            uploadStarted:function(i, file, len){console.log("uploadStarted")
                createImage(file);
            },

            progressUpdated: function(i, file, progress) {console.log("progressUpdated")
                $.data(file).find('.progressBar').width(progress);
            }
        });
        
        function createImage(file){
            var template = '<div class="preview">'+
                            '<span class="imageHolder">'+
                                '<img />'+
                                '<span class="uploaded"></span>'+
                            '</span>'+
                            '<div class="progressHolder">'+
                                '<div class="progressBar"></div>'+
                            '</div>'+
                        '</div>'; 

            var preview = $(template), 
                image = $('img', preview);

            var reader = new FileReader();

            image.width = imgPreviewWidth;
            image.height = imgWidthHeight;
            //image.css('width', imgPreviewWidth);
            //image.css('height', imgWidthHeight);

            reader.onload = function(e){

                // e.target.result holds the DataURL which
                // can be used as a source of the image:

                image.attr('src',e.target.result);
            };

            // Reading the file as a DataURL. When finished,
            // this will trigger the onload function above:
            reader.readAsDataURL(file);

            message.hide();
            preview.appendTo(elt);

            // Associating a preview container
            // with the file, using jQuery's $.data():

            $.data(file,preview);
        }
        
        function showMessage(msg){
            message.html(msg);
        }
    }

    

    
    
//});
