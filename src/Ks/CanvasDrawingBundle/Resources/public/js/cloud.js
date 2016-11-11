//"Class" Cloud, An object with a bunch of random circles.
//Made it into an object so I can repaint it the same
//exact cloud in diferent places, as if they were moving.
var Cloud = function(c, xPos,yPos) {
    this.ctx = c.getContext('2d');
    
  //vector form of this cloud
  this.circles = []; //vector to store circles positions and radius
  this.numCircles = 0;
  this.x = xPos;
  this.y = yPos;

  //wind speed...
  this.speedX = getRandomInt(1,2);
  //this.speedX = (this.speedX%2==0) ? this.speedX*1 : this.speedX*(-1);
  //this.speedX = (this.speedX%2==0) ? this.speedX*1 : this.speedX*(-1);

  this.NUM_CIRCLES = 20;

  this.initCircles = function() {
      //we init the circles
      var xOffset = 0;
      var lastRadius = 0;
      var lastAlpha = 0;

      for (var i=0; i < this.NUM_CIRCLES; i++) {
        lastRadius = getRandomInt(2,20);
        lastAlpha = getRandomInt(1,9);
	this.circles.push([xOffset+this.x+getRandomInt(50,100),//x
	                   this.y+getRandomInt(1,20),//y
	                   lastRadius, //radius
                           lastAlpha]);//alpha
	xOffset+=lastRadius/3;
      }
      this.numCircles = this.circles.length;
  } //initCircles

  this.initCircles();

  this.drawSquared = function() {

      for (var i=0; i < this.numCircles; i++) {
          //this.ctx.fillStyle='rgba(255,255,255,0.'+this.circles[i][3]+')';
          this.ctx.fillStyle='rgb(255,255,255)';
	  this.ctx.fillRect(this.circles[i][0],//center x
	          this.circles[i][1],//center y
                  this.circles[i][2],//circle radius
		       this.circles[i][2]);
      }
  }

  this.draw = function() {

      for (var i=0; i < this.numCircles; i++) {
          this.ctx.beginPath();
          this.ctx.fillStyle='rgba(255,255,255,0.'+this.circles[i][3]+')';
          //this.ctx.fillStyle='rgb(255,255,255)';
	  this.ctx.arc(this.circles[i][0],//center x
	          this.circles[i][1],//center y
                  this.circles[i][2],//circle radius
                  0,//arc starting angle
		  Math.PI*2,//arc ending angle
		  true); //counterClockwise
	  this.ctx.closePath();
	  this.ctx.fill();
      }
  } //drawCircles

  this.move = function() {
      var numCircles = this.circles.length;
      
      for (var i=0; i < numCircles; i++) {
          //Si le nuage n'est plus visible (en dessous de la zone de dessin)
            if (this.circles[i][0] - this.circles[i][3] > c.width) {  
                
              this.circles[i][0] = -10;
            } else {
            //they only move on the X axis for now.
            this.circles[i][0] = this.circles[i][0] + this.speedX;
            }
      }
  }
}