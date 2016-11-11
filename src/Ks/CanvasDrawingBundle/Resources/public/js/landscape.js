var Landscape = function(c, imagesPath) {
    var ctx = c.getContext('2d');
    
    this.drawGrass = function (y) {
        grassImg = new Image();
        grassImg.src = imagesPath + "landscapes/grass.png";

        grassImg.onload = function() {            ctx.drawImage(grassImg, 0, y);};
    };
    
    this.drawMountainsBackground = function (y) {
        mountainsImg = new Image();
        mountainsImg.src = imagesPath + "landscapes/mountains.png";

        mountainsImg.onload = function() {            ctx.drawImage(mountainsImg, 0, y);};
    };
    
    this.drawCampaign = function () {
        moonImg = new Image();
        moonImg.src = imagesPath + "landscapes/nature/campaign.png";
        var landscapeH = 165;
        moonImg.onload = function() {            ctx.drawImage(moonImg, 0, 0, 1200, 395, 0, c.height - landscapeH , 500, landscapeH);};
    };
    
    this.drawMountains = function () {
        mountainsImg = new Image();
        mountainsImg.src = imagesPath + "landscapes/nature/mountains.png";
        mountainsImg.onload = function() {            ctx.drawImage(mountainsImg, 0, 0);};
    };
    
    this.drawRoad = function () {
        roadImg = new Image();
        roadImg.src = imagesPath + "landscapes/nature/road.png";
        var landscapeH = 140;
        roadImg.onload = function() {            ctx.drawImage(roadImg, 0, c.height - landscapeH);};
    };
    
    this.drawCity = function () {
        cityImg = new Image();
        cityImg.src = imagesPath + "landscapes/nature/city.png";
        var landscapeH = 302;
        cityImg.onload = function() {            ctx.drawImage(cityImg, 0, c.height - landscapeH + 90);};
    };
    
    this.drawTrack = function () {
        trackImg = new Image();
        trackImg.src = imagesPath + "landscapes/nature/track.png";
        var landscapeH = 131;
        trackImg.onload = function() {            ctx.drawImage(trackImg, 0, c.height - landscapeH);};
    };
    
    this.drawBeach = function () {
        trackImg = new Image();
        trackImg.src = imagesPath + "landscapes/nature/beach.png";
        var landscapeH = 219;
        trackImg.onload = function() {            ctx.drawImage(trackImg, 0, c.height - landscapeH + 20);};
    };
    
    this.drawNature = function (type) {        
        switch( type ) {
            case "beach":
                this.drawBeach();
                break;
                
            case "track":
                this.drawTrack();
                break;
                
            case "city":
                this.drawCity();
                break;
                
            case "road":
                this.drawRoad();
                break;
                
            case "mountain":
                this.drawMountains();
                break;
             
            case "campaign":
            default:
                this.drawCampaign();
                break;
        }
    };
    
    this.drawTennisCourt = function (type) {
        tennisCourtImg = new Image();
        
        var landscapeH = 305;
        
        switch( type ) {
            case "clay":
                tennisCourtImg.src = imagesPath + "landscapes/tennis/clay.png";
                break;
                
            case "grass":
                tennisCourtImg.src = imagesPath + "landscapes/tennis/grass.png";
                break;
                
            case "hard":
                tennisCourtImg.src = imagesPath + "landscapes/tennis/hard.png";
                break;
             
            case "normal":
            default:
                tennisCourtImg.src = imagesPath + "landscapes/tennis/normal.png";
                break;
        }
        
        //this.drawMountainsBackground(65 - 30)
        //this.drawGrass(65);
        
        var tennisCourtH = 305;
        var tennisCourtW = 500;
        var tennisCourtX = tennisCourtW > c.width ? (c.width - tennisCourtW) / 2 : 0;
        
        tennisCourtImg.onload = function() {
            ctx.drawImage(tennisCourtImg, 0, 0, 652, 398, tennisCourtX, c.height - landscapeH , tennisCourtW, tennisCourtH);
        };
    };
    
    this.drawFitnessCenter = function () {
        fitnessCenterImg = new Image();
        fitnessCenterImg.src = imagesPath + "landscapes/musculation/room.png";
        var fitnessCenterH = 317;
        var fitnessCenterW = 450;
        var fitnessCenterX = fitnessCenterW > c.width ? (c.width - fitnessCenterW) / 2 : 0;
        fitnessCenterImg.onload = function() {            ctx.drawImage(fitnessCenterImg, fitnessCenterX, c.height - fitnessCenterH);};
    };
    
    this.drawFootballField = function () {
        footballFieldImg = new Image();
        footballFieldImg.src = imagesPath + "landscapes/football/grass.png";
        
        var footballFieldH = 213;
        var footballFieldW = 450;
        var footballFieldX = footballFieldW > c.width ? (c.width - footballFieldW) / 2 : 0;
        var footballFieldY = c.height - footballFieldH + 10;
        
        footballFieldImg.onload = function() {            ctx.drawImage(footballFieldImg, footballFieldX, footballFieldY);};
    };
    
    this.drawBasketballField = function () {
        basketballImg = new Image();
        basketballImg.src = imagesPath + "landscapes/basketball/field.png";
        
        var basketballFieldH = 185;
        var basketballFieldW = 450;
        var basketballFieldX = basketballFieldW > c.width ? (c.width - basketballFieldW) / 2 : 0;
        var basketballFieldY = c.height - basketballFieldH - 87;
        
        basketballImg.onload = function() {            ctx.drawImage(basketballImg, basketballFieldX, basketballFieldY);};
    };
    
    this.drawSwimmingPool = function () {
        swimmingPoolImg = new Image();
        swimmingPoolImg.src = imagesPath + "landscapes/swimming/pool_indoor_short.png";
        
        var swimmingPoolH = 450;
        var swimmingPoolW = 314;
        var swimmingPoolX = 0;
        var swimmingPoolY = 0;
        
        swimmingPoolImg.onload = function() {            ctx.drawImage(swimmingPoolImg, swimmingPoolX, swimmingPoolY);};
    };
    
    this.drawSkiStation = function () {
        skiStationImg = new Image();
        skiStationImg.src = imagesPath + "landscapes/ski/ski_station.png";
        
        var skiStationH = 319;
        var skiStationW = 450;
        var skiStationX = skiStationW > c.width ? (c.width - skiStationW) / 2 : 0;
        var skiStationY = c.height - skiStationH + 100;
        
        skiStationImg.onload = function() {            ctx.drawImage(skiStationImg, skiStationX, skiStationY);};
    };
    
    this.drawGolfField = function () {
        golfFieldImg = new Image();
        golfFieldImg.src = imagesPath + "landscapes/golf/field.png";
        
        var golfFieldH = 319;
        var golfFieldW = 450;
        var golfFieldX = golfFieldW > c.width ? (c.width - golfFieldW) / 2 : 0;
        var golfFieldY = c.height - golfFieldH + 100;
        
        golfFieldImg.onload = function() {            ctx.drawImage(golfFieldImg, golfFieldX, golfFieldY);};
    };
    
    this.drawBoxingRing = function () {
        boxingRingImg = new Image();
        boxingRingImg.src = imagesPath + "landscapes/combat/ring.png";
        
        var boxingRingH = 267;
        var boxingRingW = 450;
        var boxingRingX = boxingRingW > c.width ? (c.width - boxingRingW) / 2 : 0;
        var boxingRingY = c.height - boxingRingH + 100;
        
        boxingRingImg.onload = function() {            ctx.drawImage(boxingRingImg, boxingRingX, boxingRingY);};
    };
    
    this.drawSkateRamp = function () {
        skateRampImg = new Image();
        skateRampImg.src = imagesPath + "landscapes/skate/ramp.png";
        
        var skateRampH = 317;
        var skateRampW = 450;
        var skateRampX = skateRampW > c.width ? (c.width - skateRampW) / 2 : 0;
        var skateRampY = c.height - skateRampH + 100;
        
        skateRampImg.onload = function() {            ctx.drawImage(skateRampImg, skateRampX, skateRampY);};
    };
    
    this.draw = function( params ) {
        if( params.codeSport ) {
            switch ( params.codeSport ) {
                case "tennis":
                    groundCode = "normal";
                    if( params.groundCode ) groundCode = params.groundCode;
                    this.drawTennisCourt( groundCode );
                    break;
                    
                case "musculation":
                case "yoga":
                case "spinning":
                case "elliptical":
                    this.drawFitnessCenter();
                    break;
                    
                case "football":
                    this.drawFootballField();
                    break;
                    
                case "basketball":
                    this.drawBasketballField();
                    break;
                    
                case "swimming":
                case "waterPolo":
                case "aquabike":
                case "aquagym":
                    this.drawSwimmingPool();
                    break;
                    
                case "running":
                case "cycling":
                case "hiking":
                case "mountain-biking":
                case "walking":
                    groundCode = "campaign";
                    if( params.groundCode ) groundCode = params.groundCode;
                    this.drawNature( groundCode );
                    break;
                    
                case "windsurfing":
                case "scuba-diving":
                case "surf":
                case "rowing":
                    this.drawBeach();
                    break;
                    
                case "climbing":
                    this.drawMountains();
                    break;
                    
                case "golf":
                    this.drawGolfField();
                    break;
                    
                case "ski":
                case "cross-country-skiing":
                case "snowboard":
                case "crossCountrySkiing":
                    this.drawSkiStation();
                    break;
                    
                case "boxe":
                case "karate":
                case "judo":
                case "boxeThai":
                case "jujitsu":
                case "mma":
                    this.drawBoxingRing();
                    break;
                    
                case "skateboard":
                    this.drawSkateRamp();
                    break;

                default:
                    this.drawCampaign();
            }
        } 
    };
};
