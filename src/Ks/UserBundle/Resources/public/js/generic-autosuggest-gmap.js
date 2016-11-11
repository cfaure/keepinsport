function autocompleteGmap(){  

    //on cache les champs et les labels
    $("#generic_longitude").css("display","none");
    $("label[for='generic_longitude']").css("display","none");
    $("#generic_town").css("display","none");
    $("label[for='generic_town']").css("display","none");
    $("#generic_latitude").css("display","none");
    $("label[for='generic_latitude']").css("display","none");
    $("#generic_country_area").css("display","none");
    $("label[for='generic_country_area']").css("display","none");
    $("#generic_country_code").css("display","none");
    $("label[for='generic_country_code']").css("display","none");
    
    if( typeof( google.maps ) != 'undefined' ) {
        geocoder = new google.maps.Geocoder(); 

        $("#generic_adress_name").autocomplete({
            //This bit uses the geocoder to fetch address values
            source: function(request, response) {
                geocoder.geocode( {'address': request.term}, function(results, status) {
                response($.map(results, function(item) {
                    //Récupération des informations liées à l'adresse  
                    arrayAdressComponents = item.address_components;
                    town = "";
                    area = "";
                    countryCode = "";


                    //console.log(arrayAdressComponents);
                    $.each(arrayAdressComponents, function(key, value) {


                        //Récupération des informations
                        if(value.types[0]=="locality"){

                            if(value.long_name!=""){
                                town = value.long_name;
                            }
                        }

                        if(value.types[0]=="administrative_area_level_1"){

                            if(value.long_name!=""){
                                area = value.long_name;
                            }

                        }

                        if(value.types[0]=="country"){ 

                            if(value.long_name!=""){
                            countryCode = value.short_name;
                            }
                        }

                    });

                    return {
                    label:  item.formatted_address,
                    value: item.formatted_address,
                    latitude: item.geometry.location.lat(),
                    longitude: item.geometry.location.lng(),
                    town: town,
                    area: area,
                    countryCode: countryCode



                    }
                }));
                })
            },
            //This bit is executed upon selection of an address
            select: function(event, ui) {  

                    $("#generic_latitude").val(ui.item.latitude);
                    $("#generic_longitude").val(ui.item.longitude);
                    $("#generic_country_area").val(ui.item.area);
                    $("#generic_country_code").val(ui.item.countryCode);
                    $("#generic_town").val(ui.item.town);



                    var location = new google.maps.LatLng(ui.item.latitude, ui.item.longitude);
                    marker.setPosition(location);
                    map.setCenter(location);
                }
            });


        google.maps.event.addListener(marker, 'drag', function() {
            geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    $('#generic_adress_name').val(results[0].formatted_address);
                    $('#generic_latitude').val(marker.getPosition().lat());
                    $('#generic_latitude').val(marker.getPosition().lng());
                }
            }
        });
        });
    }
}


