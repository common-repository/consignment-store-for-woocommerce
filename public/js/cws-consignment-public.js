(function( $ ) {
	'use strict';
	
	$( window ).load(function() {
		$('.toggledivbyid').on("click", function() {
			// first get data 
			var divid = $(this).data("divid");
			if ($('#' + divid).hasClass("cwshidden")) {
				$('#' + divid).removeClass("cwshidden");
				if (divid == "catprices") { // call ajax function to show avg prices in the store
					$('#catprices').html('<p class="warnmsg">Fetching prices... please wait</p>');
					var this2 = this;                      //use in callback
					$.post(my_ajax_obj.ajax_url, {         //POST request
						action: "cwscs_ajax_add_item",
						security: my_ajax_obj.nonce,
						thiscat: $('#item_cat').val(), 		// data
						thistask: "getcatprices"
					}, function(results) {                    //callback
						if (!results) {
							$('#catprices').html("Could not fetch at this time.");
							console.log('Could not fetch');
						} else if (results.status) {
							console.log('In here and ' + results.status);
							if (results.status == -1) { // no results
								$('#catprices').html('<p class="failmsg">Sorry! There are no prices available to show at this time.</p>');
								console.log("NO RESULTS");
							} else if (results.status == 0) { // error
								$('#catprices').html('<p class="failmsg">Sorry! There are no prices available to show at this time.</p>');
								console.log("status is 0");
							} else {
								var ct = showCatPrices(results.data);
								$('#catprices').html(ct);
							}
						}
					});
					
				}
			} else {
				$('#' + divid).addClass("cwshidden");
			}
		}); // END toggledivbyid
		
		// Handle additem form submit - if recaptcha v3 then have to intercept
		$('#cwscs_formadditem').submit(function() {
			cwsStartSpinner("Please wait...") ;
		}); // END additem submit
	}); // END load
	////////////////////////////////  SPINNER  FUNCTIONS  /////////////////////////////////
	function cwsStartSpinner(title) {
		console.log("Start spinner");
		jQuery('body').append('<div class="overlay_spinner" id="myoverlay"><div><h3 id="overlaymsg">' + title + '</h3><i class="fa fa-spinner fa-spin" id="myspinner"></i></div></div>');
		console.log ('added overlay')
		return true;
	}
	function cwsStopSpinner() {
		jQuery('#myoverlay').remove();
	}
	
	
	$('#cws_showcatprices').change(function() {
		console.log('showcatprices clicked');
	});
})( jQuery );

function showCatPrices(data) {
	var ct = '<div class="div_showcatprices"><p>' + data.length + ' result(s).</p>';
	if (data) {
		ct += '<table class="table borders" width="100%"> <tbody> <tr><th>Category</th><th class="text-center"># Items in Store</th><th class="text-right">Lowest Price</th><th class="text-right">Highest Price</th> <th class="text-right">Average</th></tr>';
		// loop through
		for (var i=0; i<data.length; i++) {
			if (data[i]['total_items'] > 0) {
				ct += '<tr><td>' + data[i]['name'] + '</td><td class="text-center">' + data[i]['total_items'] + '</td> <td class="text-right">$' + data[i]['lowest'] + '</td> <td class="text-right">$' + data[i]['highest'] + '</td> <td class="text-right">$' + data[i]['average'] + '</td> </tr>';
			}
		}
		ct += '</tbody></table>';
	} 
	ct += '</div>';
	return ct;
}

// When user clicks on an image this is run to resize the image first
window.uploadPhotos = function(){
    // Read in file
    var file = event.target.files[0];
	var thisid = event.target.id;
	jQuery('#tmpfilename').val(file.name);
    var mime = file.type; // store mime for later
    // Ensure it's an image
    if(file.type.match(/image.*/)) {
        // Load the image
        var reader = new FileReader();
        reader.onload = function (readerEvent) {
            var image = new Image();
            image.onload = function (imageEvent) {

                // Resize the image
                var canvas = document.createElement('canvas'),
                    max_size = 544,
                    width = image.width,
                    height = image.height;
                if (width > height) {
                    if (width > max_size) {
                        height *= max_size / width;
                        width = max_size;
                    }
                } else {
                    if (height > max_size) {
                        width *= max_size / height;
                        height = max_size;
                    }
                }
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(image, 0, 0, width, height);
                var dataUrl = canvas.toDataURL('image/jpeg'); // convert the canvas to dataurl
				var resizedImage = dataURLToBlob(dataUrl);
                jQuery.event.trigger({
                    type: "imageResized",
                    blob: resizedImage,
                    url: dataUrl,
					thisid: thisid
                });
            }
            image.src = readerEvent.target.result;
        }
        reader.readAsDataURL(file);
    }
};
/* Utility function to convert a canvas to a BLOB */
var dataURLToBlob = function(dataURL) {
    var BASE64_MARKER = ';base64,';
    if (dataURL.indexOf(BASE64_MARKER) == -1) {
        var parts = dataURL.split(',');
        var contentType = parts[0].split(':')[1];
        var raw = parts[1];

        return new Blob([raw], {type: contentType});
    }

    var parts = dataURL.split(BASE64_MARKER);
    var contentType = parts[0].split(':')[1];
    var raw = window.atob(parts[1]);
    var rawLength = raw.length;

    var uInt8Array = new Uint8Array(rawLength);

    for (var i = 0; i < rawLength; ++i) {
        uInt8Array[i] = raw.charCodeAt(i);
    }

    return new Blob([uInt8Array], {type: contentType});
}
/* End Utility function to convert a canvas to a BLOB      */
/* Handle image resized events */
jQuery(document).on("imageResized", function (event) {
    if (event.blob && event.url && event.thisid) {
		var this2 = this;                      //use in callback
		var formdata = false;
		if (window.FormData) {
			formdata = new FormData();
			var form = jQuery('#cwscs_formadditem')[0];
      		formdata = new FormData(form);
			console.log("Formdata initialized");
		} else {
			console.log("FormData not supported")
		}
        formdata.append("action", "cwscs_ajax_add_item");
		formdata.append("security", my_ajax_obj.nonce);
		formdata.append("thistask", "uploadimage");
		formdata.append('image_data', event.blob);
		formdata.append('tmpfilename', jQuery('#tmpfilename').val());
		jQuery.ajax({
			url:my_ajax_obj.ajax_url,
			type:"POST",
            contentType: false,
            processData: false,
            cache: false,
			crossDomain: true,
			dataType: 'json',
    		data: formdata,
			fail: function(results){
				console.log('FAIL: ', results)
				jQuery('#cwscs_errormsg').html("Image upload failed");
				jQuery('#cwscs_errormsg').removeClass("cwshidden");
				jQuery('#cwscs_errormsg').addClass("failmsg");
				jQuery('#cwscs_errormsg').removeClass("successmsg");
			},
			error: function(results){
				console.log('ERROR: ', results)
				jQuery('#cwscs_errormsg').html("Image upload failed");
				jQuery('#cwscs_errormsg').removeClass("cwshidden");
				jQuery('#cwscs_errormsg').addClass("failmsg");
				jQuery('#cwscs_errormsg').removeClass("successmsg");
			},
			success: function(results){
				console.log('SUCCESS: ', results)
				jQuery('#cwscs_errormsg').html("");
				jQuery('#cwscs_errormsg').addClass("cwshidden");
				jQuery('#cwscs_errormsg').addClass("failmsg");
				jQuery('#cwscs_errormsg').removeClass("successmsg");
				if (!results) {
					jQuery('#cwscs_errormsg').html("Could not upload the image at this time.");
					jQuery('#cwscs_errormsg').removeClass("cwshidden");
				} else if (results.status) {
					if (results.status == 0) { // error
						if (results.msg && results.msg != "") {
							jQuery('#cwscs_errormsg').html(results.msg);
						} else {
							jQuery('#cwscs_errormsg').html("There was an error.");
						}
						jQuery('#cwscs_errormsg').removeClass("cwshidden");
					} else {
						if (results.data && results.data.partimgurl) {
							console.log('populating filename');
							var thisid = event.thisid;
							var el = thisid.replace("image", "filename");
							jQuery('#' + el).val(results.data.partimgurl);
							// show on form
							var el = thisid.replace("image", "tmp-img");
							jQuery('#' + el).attr("src", results.data.partimgurl);
							console.log('# + ' + el + ' set to ' + results.data.partimgurl);
							jQuery('#' + el).removeClass("cwshidden");
						} else {
							console.log('No partimgurl');
							jQuery('#cwscs_errormsg').html("Could not upload the image.");
							jQuery('#cwscs_errormsg').removeClass("cwshidden");
						}
					}
				} // END check on status
			} // END success
		});
    }
});

function cc_enableSubmitBtn() {
	document.getElementById("cc_additem").disabled = false;
}
