 var colors = Highcharts.getOptions().colors;
  
 xAxisOptions = {
    categories: []
};

yAxisOptions = [{
    min: 0,
    title: {
        text: 'Points'
    },
    //Label des valeurs cumulées en haut des barres
    stackLabels: {
        enabled: true,
        style: {
            fontWeight: 'bold',
            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
        },
        useHTML: false,
        formatter: function() {
            return this.total; 
        },
        y: 0
    },
    labels: {
        formatter: function() {
            //Pour ne pas que ça transforme "1000" en "1k"
            return this.value;
        }
    }
}/*,{ min: 0,
    title: {
        text: 'Ligue'
    },
    //Label des valeurs cumulées en haut des barres
    stackLabels: {
        style: {
            fontWeight: 'bold',
            color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
        }
    },
    labels: {
        formatter: function() {

            return this.value;
        }
    },
    opposite: true
}*/];

tooltipStackingGraphOptions = {
    formatter: function() {
        var s;
        if (this.point.name) { // the pie chart
            s = this.point.name +': '+ this.y +' points';
        } else {
            s = '<b>'+ this.x +'</b><br/>'+
            this.series.name +': '+ this.y +'<br/>'+
            'Total: '+ this.point.stackTotal;
        }
        return s;
    }
};

tooltipGraphOptions = {
    formatter: function() {
        return '<b>'+ this.x +'</b><br/>'+
        this.series.name +': '+ this.y;
    }
};

plotOptionsGraphOptions = {
    column: {
        stacking: 'normal',
        dataLabels: {
            enabled: false,
            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
        }
    },
    series: {
        cursor: 'pointer',
        point: {
            events: {}
        }
    },
    pie: {
        colors: {0: 'rgb(67, 67, 72)', 1: 'rgb(124, 181, 236)'}
    }
};

plotOptionsPieOptions = {
    pie: {
        //allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
            enabled: true,
            //distance: -30,
            //color: 'white',
            formatter: function() {
                return this.y;
            }
        },
        showInLegend: true
    },
    series: {
        cursor: 'pointer',
        point: {
            events: {}
        }
    }
};

creditsOptions = {enabled:false};

