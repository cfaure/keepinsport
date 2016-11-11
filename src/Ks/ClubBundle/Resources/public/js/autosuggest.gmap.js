$(document).ready(function() { 
   
 if( typeof( google.maps ) != 'undefined' ) {
    geocoder = new google.maps.Geocoder();

    //on cache les champs et les labels
    $("#ks_clubbundle_clubtype_longitude").css("display","none");
    $("label[for='ks_clubbundle_clubtype_longitude']").css("display","none");
    $("#ks_clubbundle_clubtype_town").css("display","none");
    $("label[for='ks_clubbundle_clubtype_town']").css("display","none");
    $("#ks_clubbundle_clubtype_latitude").css("display","none");
    $("label[for='ks_clubbundle_clubtype_latitude']").css("display","none");
    $("#ks_clubbundle_clubtype_country_area").css("display","none");
    $("label[for='ks_clubbundle_clubtype_country_area']").css("display","none");
    $("#ks_clubbundle_clubtype_country_code").css("display","none");
    $("label[for='ks_clubbundle_clubtype_country_code']").css("display","none");


    $(function() {
      $("#ks_clubbundle_clubtype_adress_name").autocomplete({
        //This bit uses the geocoder to fetch address values
        source: function(request, response) {
          geocoder.geocode( {'address': request.term}, function(results, status) {
            response($.map(results, function(item) {
              //Récupération des informations liées à l'adresse  
              arrayAdressComponents = item.address_components;
              town = "";
              area = "";
              countryCode = "";

              console.log(arrayAdressComponents);
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

          $("#ks_clubbundle_clubtype_latitude").val(ui.item.latitude);
          $("#ks_clubbundle_clubtype_longitude").val(ui.item.longitude);
          $("#ks_clubbundle_clubtype_country_area").val(ui.item.area);
          $("#ks_clubbundle_clubtype_country_code").val(ui.item.countryCode);
          $("#ks_clubbundle_clubtype_town").val(ui.item.town);



          var location = new google.maps.LatLng(ui.item.latitude, ui.item.longitude);
          marker.setPosition(location);
          map.setCenter(location);
        }
      });
    });

    google.maps.event.addListener(marker, 'drag', function() {
      geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          if (results[0]) {
            $('#ks_clubbundle_clubtype_adress_name').val(results[0].formatted_address);
            $('#ks_clubbundle_clubtype_latitude').val(marker.getPosition().lat());
            $('#ks_clubbundle_clubtype_latitude').val(marker.getPosition().lng());
          }
        }
      });
    });

    }
})