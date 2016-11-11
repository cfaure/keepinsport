function traceEvolutionBySaisonGraph(id, userId, clicActive) {
    $.get(
        Routing.generate('ksDashboard_getDataGraphPointsBySportByMonth', {'id' : userId} ), 
        function(response) {
            $("#"+ id + "Loader").hide();

            if( Object.keys(response.chart.points).length > 0) {
                var highchartsOptions = {};
                highchartsOptions.chart = {
                    renderTo: id + 'Container',
                    type: 'column'
                };

                highchartsOptions.title = {
                    text: ''
                };
                
                highchartsOptions.series = [];
                highchartsOptions.xAxis = xAxisOptions;
                highchartsOptions.yAxis = yAxisOptions;
                highchartsOptions.yAxis[0].stackLabels.useHTML = true; 
                //On monte le label pour pouvoir rentrer le spoints et les étoiles
                        highchartsOptions.yAxis[0].stackLabels.y = -20;
                highchartsOptions.yAxis[0].stackLabels.formatter = function() {
                    var str = '';
                    //console.log();
                    var leagueId = response.chart.leaguesIds[this.x];
                    var league = getLeague( response.leagues, leagueId);

                    if( league != null ) {
                        
                        
                        //On écrit les étoiles
                        str += '<span class="' + league.categoryCode + '">';
                        
                        switch( parseInt( league.starNumber ) ) {
                            case 3:
                                str += '<i class="icon-star"></i><i class="icon-star"></i><i class="icon-star"></i>';
                                break;
                            case 2:
                                str += '<i class="icon-star"></i><i class="icon-star"></i><i class="icon-star-empty"></i>';
                                break;
                            case 1:
                                str += '<i class="icon-star"></i><i class="icon-star-empty"></i><i class="icon-star-empty"></i>';
                                break;
                            default:
                                str += '<i class="icon-star-empty"></i><i class="icon-star-empty"></i><i class="icon-star-empty"></i>';
                                break;
                        }
                        
                        str += '</span>';
                    }
                    str += '<br/>';
                    str += this.total;
                    return str; 
                };
                highchartsOptions.tooltip = tooltipStackingGraphOptions;

                /*highchartsOptions.labels = {
                    items: [{
                        html: 'Cumul des points: ' +response.cumulPoints,
                        style: {
                            left: '40px',
                            top: '8px',
                            color: 'black'
                        }
                    }]
                };*/
                highchartsOptions.plotOptions = plotOptionsGraphOptions;
                highchartsOptions.plotOptions.column.stacking = "normal";
                //highchartsOptions.plotOptions.column.dataLabels.zIndex = 10;
                //highchartsOptions.plotOptions.bubble.zIndex = 0;


                //FMO : volontairement désactiver car pas trop de sens d'afficher le détail par mois
                /*
                if( clicActive ) {
                    highchartsOptions.plotOptions.series.point.events.click = function(e) {
                        var nbMonths = this.series.xData.length;
                        indexPreviousMonthSelected = ( nbMonths - 1 ) - this.x;
                        sportIdSelected = this.series.options.id ? this.series.options.id : this.options.id;
                        var parameters = {
                            "userId"                : userId,
                            "sportId"               : sportIdSelected,
                            "indexPreviousMonth"    : indexPreviousMonthSelected
                        };
                        
                        getDataGraphDependingOnSport( parameters );
                        loadActivitiesByParameters( parameters );
                    }
                }
                */
                
                //console.log(response.chart.leaguesIds);
                
                /*highchartsOptions.plotOptions.line = {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: false
                }*/
                
                /*highchartsOptions.plotOptions.column = {
                    stacking: "normal",
                    stackLabels: {
                      enabled: true,
                      useHTML: true,
                      formatter: function() {
                        return '<i class="icon-star"></i><i class="icon-star-empty"></i><i class="icon-star-empty"></i>'; 
                      }
                      //y: 0
                    }
                };*/

                highchartsOptions.credits = creditsOptions;

                $.each( response.sports, function(sportId, sportLabel) {

                    highchartsOptions.series.push({
                        id:   sportId,
                        name: sportLabel,
                        data: response.chart.points[sportId]
                    });

                });
                
                /*highchartsOptions.series.push({
                    name: 'Ligues',
                    color: '#89A54E',
                    type: 'scatter',
                    data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
                    yAxis: 1,
                    tooltip: {
                        valueSuffix: '°C'
                    }
                });*/
                

                //Camembert pour cumul des sports
                /*highchartsOptions.series.push({
                    type:   'pie',
                    name: "cumul",
                    data: response.chart.cumulForPie,
                    center: [100, 80],
                    size: 100,
                    showInLegend: false,
                    dataLabels: {
                        enabled: false
                    }
                });*/

                highchartsOptions.xAxis.categories = response.periods;
                new Highcharts.Chart(highchartsOptions);
            } else {
                $("#"+ id + 'Container').html("Tu n'as pas encore réalisé d'activité !")
            }
        }
    );
}

function getLeague( leagues, leagueId ) {
    var returnLeague = null;
    $.each( leagues, function( key, league ) {
        //console.log( league.id+ " - " + leagueId)
        if( league.id == leagueId) {
            returnLeague = league;
            return false;
        }
    });
    return returnLeague;
}