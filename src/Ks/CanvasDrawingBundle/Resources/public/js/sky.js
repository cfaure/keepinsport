var Sky = function(c, imagesPath) {
    var ctx = c.getContext('2d');
    this.imagesPath = imagesPath;
    
    this.skyH = 150;
    
    
    var clouds = [];
    this.initClouds = function (numClouds) {
        var lastX = 0;
        var lastY = 0;
        for (var i=0; i < numClouds; i++) {
            lastX = getRandomInt(0,c.width);
            lastY = getRandomInt(0,this.skyH);
            clouds.push(new Cloud(c, lastX,lastY));
        }
    };
    
    //draw and move the clouds for a next possible drawClouds() call
    this.drawClouds = function() {
        var numClouds = clouds.length;
        var cloud;
        for (var i=0; i < numClouds; i++) {
            clouds[i].draw();
            clouds[i].move();
        }
    };
    
    this.drawNightSkyGradient = function () {
        var skyGradient = ctx.createLinearGradient(0,0,0,this.skyH);
        skyGradient.addColorStop(0,'#00074a');
        skyGradient.addColorStop(1,'#0650cb');

        ctx.fillStyle = skyGradient;
        ctx.fillRect(0,0,c.width,this.skyH);
    };
    
    this.drawSkyGradient = function () {
        var skyGradient = ctx.createLinearGradient(0,0,0,this.skyH);
        skyGradient.addColorStop(0,'#00aaff');
        skyGradient.addColorStop(1,'#ffffff');

        ctx.fillStyle = skyGradient;
        ctx.fillRect(0,0,c.width,this.skyH);
    };

    this.drawSkySolid = function () {
        ctx.fillStyle='#00aaff';
        ctx.fillRect(0,0,c.width,this.skyH);
    };

    this.drawSun = function () {
        sunImg = new Image();
        sunImg.src = imagesPath + "sun.png";
        sunImg.onload = function() {            ctx.drawImage(sunImg, 0, 0, 128, 128, c.width - 45, 5, 40, 40);};
    };
    this.drawMoon = function () {
        moonImg = new Image();
        moonImg.src = imagesPath + "moon.png";
        moonImg.onload = function() {            ctx.drawImage(moonImg, 0, 0, 128, 128, c.width - 45, 5, 40, 40);};
    };
    
    this.draw = function( params ) {
        
        if( params.drawNight ) {
            this.drawNightSkyGradient();
            this.drawMoon();
        } else if( params.drawDay ) {
            this.drawSkyGradient();
            this.drawSun();
        } else {
            this.drawSkyGradient();
            this.drawSun();
        }
        
        if( params.drawClouds ) {
            this.drawClouds();
        }
    };
};
