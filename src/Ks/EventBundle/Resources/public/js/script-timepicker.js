$(function() {  
    $('#datetimeStart').datetimepicker();
    $('#datetimeEnd').datetimepicker();
    
    
 
    var digitpattern = /\d+/g;
    /*Date start */
    
    $('#datetimeStart').change(function(){
        datetime = $(this).val();
        matches = datetime.match(digitpattern);
        
        for(i=0;i<5;i++){
            if(matches[i].substring(0,1)=="0"){
               matches[i] = matches[i].substring(1,2);
            }
        }
        
        
        month = matches[0];
        day = matches[1];
        year = matches[2];
        hour = matches[3];
        minutes = matches[4];
        

        $('#ks_eventbundle_eventtype_startDate_date_year').val(year);
        $('#ks_eventbundle_eventtype_startDate_date_month').val(month);
        $('#ks_eventbundle_eventtype_startDate_date_day').val(day);
        $('#ks_eventbundle_eventtype_startDate_time_hour').val(hour);
        $('#ks_eventbundle_eventtype_startDate_time_minute').val(minutes);
    });
    
    /*Date End */
    $('#datetimeEnd').change(function(){
        datetime = $(this).val();
        matches = datetime.match(digitpattern);
        
        for(i=0;i<5;i++){
            if(matches[i].substring(0,1)=="0"){
               matches[i] = matches[i].substring(1,2);
            }
        }
        
        
        month = matches[0];
        day = matches[1];
        year = matches[2];
        hour = matches[3];
        minutes = matches[4];
        
        $('#ks_eventbundle_eventtype_endDate_date_year').val(year);
        $('#ks_eventbundle_eventtype_endDate_date_month').val(month);
        $('#ks_eventbundle_eventtype_endDate_date_day').val(day);
        $('#ks_eventbundle_eventtype_endDate_time_hour').val(hour);
        $('#ks_eventbundle_eventtype_endDate_time_minute').val(minutes);
    });
    
    //start 
    $('#ks_eventbundle_eventtype_startDate_date').css("display","none");
    $('#ks_eventbundle_eventtype_startDate_date_year').css("display","none");
    $('#ks_eventbundle_eventtype_startDate_date_month').css("display","none");
    $('#ks_eventbundle_eventtype_startDate_date_day').css("display","none");
    $('#ks_eventbundle_eventtype_startDate_time_hour').css("display","none");
    $('#ks_eventbundle_eventtype_startDate_time_minute').css("display","none");
    $('#ks_eventbundle_eventtype_startDate_time').css("display","none");

    
    //end
    $('#ks_eventbundle_eventtype_endDate_date_year').css("display","none");
    $('#ks_eventbundle_eventtype_endDate_date_month').css("display","none");
    $('#ks_eventbundle_eventtype_endDate_date_day').css("display","none");
    $('#ks_eventbundle_eventtype_endDate_time_hour').css("display","none");
    $('#ks_eventbundle_eventtype_endDate_time_minute').css("display","none");
    $('#ks_eventbundle_eventtype_endDate_date').css("display","none");
    $('#ks_eventbundle_eventtype_endDate_time').css("display","none");
    
    
});   



