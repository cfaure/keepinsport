$(document).ready(function() { 
  geocoder = new google.maps.Geocoder();
  
  //on cache les champs et les labels
  $("input[id$=longitude]").css("display","none");
  $("label[for$=longitude]").css("display","none");
  $("input[id$=latitude]").css("display","none");
  $("label[for$=latitude]").css("display","none");
  $("input[id$=town]").css("display","none");
  $("label[for$=town]").css("display","none");
  $("input[id$=country_area]").css("display","none");
  $("label[for$=country_area]").css("display","none");
  $("input[id$=country_code]").css("display","none");
  $("label[for$=country_code]").css("display","none");
  
  var location = new google.maps.LatLng($("#formAdress").find(".latitude").val(), $("#formAdress").find(".longitude").val());
  map = new google.maps.Map(
  document.getElementById("map_canvas"), {
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      center: location,
      zoom : 10,
  });

  marker = new google.maps.Marker({
      map     : map,
      position: location,
      draggable : true
  });
  
  marker.setPosition(location);
  map.setCenter(location);
            
  $(function() {
    $("input[id$=full_address], input[id$=adress_name]").autocomplete({
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
        $("input[id$=latitude]").val(ui.item.latitude);
        $("input[id$=longitude]").val(ui.item.longitude);
        $("input[id$=country_area]").val(ui.item.area);
        $("input[id$=country_code]").val(ui.item.countryCode);
        $("input[id$=town]").val(ui.item.town);
        $("input[id$=full_address]").val(ui.item.formatted_address);
        $("input[id$=adress_name]").val(ui.item.formatted_address);
        var location = new google.maps.LatLng(ui.item.latitude, ui.item.longitude);
        map = new google.maps.Map(
        document.getElementById("map_canvas"), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: location,
            zoom : 10,
        });
        marker = new google.maps.Marker({
            map     : map,
            position: location,
            draggable : true
        });
        marker.setPosition(location);
        map.setCenter(location);
        google.maps.event.addListener(marker, 'drag', dragFunction, false);
        //$("#formAdress").submit();
      }
    });
  });
  
  var dragFunction = function (event) {
    geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        if (results[0]) {
          $("input[id$=latitude]").val(marker.getPosition().lat());
          $("input[id$=longitude]").val(marker.getPosition().lng());
          $("input[id$=country_area]").val(results[0].area);
          $("input[id$=country_code]").val(results[0].countryCode);
          $("input[id$=town]").val(results[0].town);
          $("input[id$=full_address]").val(results[0].formatted_address);
          $("input[id$=adress_name]").val(results[0].formatted_address);
        }
      }
    }
  )};
  
  google.maps.event.addListener(marker, 'drag', dragFunction, false);
})