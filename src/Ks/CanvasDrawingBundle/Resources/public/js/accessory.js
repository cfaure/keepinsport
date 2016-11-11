var Accessory = function(c, imagesPath){ 
    var ctx = c.getContext('2d');
    
    imagePath = imagesPath;
    
    this.persoX = 0;  
    this.persoY = 0;
    
    this.drawFootball_cage = function() {
        ctx.save();
        cageImg = new Image();
        cageImg.src = imagePath + "cage_foot.png";
        
        cageImg.onload = function() {
            ctx.drawImage(cageImg, 0, 0, 576, 351, 10, 70, 200, 122);
        };
        ctx.restore();
    };
    this.drawFootball_ball = function() {
        ctx.save();
        ballImg = new Image();
        ballImg.src = imagePath + "balls/football.png";
        
        ballImg.onload = function() { 
            ctx.drawImage(ballImg, 0, 0, 64, 64, 130, c.height - 40, 40, 40);
        };
        ctx.restore();
    };
    
     this.drawWoodPanel = function( params ) {
        ctx.save();
        woodPannelImg = new Image();
        woodPannelImg.src = imagePath + "wood_pannel.png";
        
        var woodPannelW = 130;
        var woodPannelH = 144;
        var woodPannelX = 100;//c.width - woodPannelW;
        var woodPannelY = 0;//c.height - woodPannelH + 20;
        
        woodPannelImg.onload = function() {
            ctx.drawImage(woodPannelImg, 0, 0, 174, 193, woodPannelX, woodPannelY, woodPannelW, woodPannelH);
            var nextIconY = woodPannelY + 15;
        
            
            //Temps
            duration = new Image();
            duration.src = imagePath + "endurance/timer.png";
            duration.onload = function() {            
                ctx.drawImage( duration, 0, 0, 30, 30, woodPannelX + 20, nextIconY, 20, 20);
                ctx.textBaseline = "top";
                ctx.font = "10pt Arial"; 
                ctx.fillStyle = "#5d3a1d";
                if( params.duration ) ctx.fillText(params.duration, woodPannelX + 45, nextIconY + 5);
                nextIconY += 20;
                
                //Nombre de kilomètres
                distance = new Image();
                distance.src = imagePath + "endurance/road.png";
                distance.onload = function() {            
                    ctx.drawImage( distance, 0, 0, 30, 30, woodPannelX + 20, nextIconY, 20, 20);
                    ctx.textBaseline = "top";
                    ctx.font = "10pt Arial"; 
                    ctx.fillStyle = "#5d3a1d";
                    if( params.distance ) {
                        text = params.distance +"km";
                    }
                    else {
                        text = "-";
                    }
                    ctx.fillText(text, woodPannelX + 45, nextIconY + 5);
                    nextIconY += 20;
                    
                    //Dénivellé
                    denPos = new Image();
                    denPos.src = imagePath + "endurance/mountain.png";
                    denPos.onload = function() {            
                        ctx.drawImage( denPos, 0, 0, 30, 30, woodPannelX + 20, nextIconY, 20, 20);
                        ctx.textBaseline = "top";
                        ctx.font = "10pt Arial"; 
                        ctx.fillStyle = "#5d3a1d";
                        if( params.denPos ) {
                            text = params.denPos + " D+";
                        }
                        else {
                            text = "-";
                        }
                        ctx.fillText(text, woodPannelX + 45, nextIconY + 5);
                        woodPanelIsOK = true;
                    };
                };
            };
        };
        ctx.restore();
    };
    
    this.drawMilepost = function(km) {
        ctx.save();
        milepostImg = new Image();

        milepostImg.src = imagePath + "milepost.png";
        //console.log(milepostImg);
        var milepostX = c.width - 70;
        var milepostY = c.height - 100;
        ctx.drawImage(milepostImg, 0, 0, 59, 100, milepostX, milepostY, 59, 100);
        ctx.fillStyle = "black";
        ctx.translate(milepostX+ 5, milepostY + 50);
        ctx.font = "10pt Arial"; 
        ctx.rotate(0.1);
        //ctx.textAlign = "center";
        ctx.fillText(km + "km", 0, 0);
        ctx.restore();
    }
    
    this.drawBoard = function(params) {
        var boardX = c.width - 240;
        var boardY = c.height - 100;
        
        ctx.save();
        //ctx.rotate(0.1);
        var boardWidth = 70;
        var textX = boardX + 3;
        var boardDurationHeight = 22;
        var durationY = boardY + 2;
        
        if( params.duration ) {
            ctx.font = "15pt Digital"; 
            var textDuration = ctx.measureText(params.duration);
            boardWidth = textDuration.width + 6;
            
            //Dessin du panneau
            ctx.fillStyle = "#000000";
            ctx.fillRect(boardX, boardY, boardWidth, boardDurationHeight); 
            
            //dessin du pied du panneau
            ctx.fillRect(boardX + (boardWidth /2 - 2.5), boardY, 5, 200); 
            
            ctx.fillStyle = "red";
        
            ctx.textBaseline = "top";
            ctx.fillText(params.duration, textX, durationY);
        }
        
        if( params.scores ) {
            var boardScoresHeight = params.scores.length > 3 ? 55 : 44;
            
            ctx.fillStyle = "#000000";
            ctx.fillRect(boardX, boardY + boardDurationHeight, boardWidth, boardScoresHeight); 
            ctx.font = "10pt Arial"; 
            ctx.fillStyle = "white";
            ctx.fillText("SCORES", textX + 3, boardDurationHeight + durationY);
            ctx.font = "7pt Arial"; 
            ctx.fillText("Home", textX, boardDurationHeight + durationY + 15);
            ctx.fillText("Visitors", textX + 32, boardDurationHeight + durationY + 15);
            ctx.font = "10pt Digital";
            ctx.fillStyle = "yellow";
            
            var nextScoreMeX = textX;
            var nextScoreMeY = boardDurationHeight + durationY + 25;
            var maxScoreMeX = nextScoreMeX + 25;
            
            var nextScoreOpponentsX = textX + 35;
            var nextScoreOpponentsY = nextScoreMeY;
            var maxScoreOpponentsX = nextScoreOpponentsX + 25;
            var marginScores = 3;
            $.each( params.scores, function(key, score) {
                //score 1
                var textScoreMe = score.me;
                var textScoreMeDimentions = ctx.measureText(textScoreMe);
                ctx.fillText(textScoreMe, nextScoreMeX, nextScoreMeY);
                nextScoreMeX += textScoreMeDimentions.width + marginScores;
                //Si le score va empiéter sur celui des adversaires, on l'écrit à la ligne
                if( nextScoreMeX > maxScoreMeX ) {
                    nextScoreMeX = textX;
                    nextScoreMeY += 13;
                }
                
                //Score 2
                var textScoreOpponent = score.opponent;
                var textScoreOpponentDimentions = ctx.measureText(textScoreOpponent);
                ctx.fillText(textScoreOpponent, nextScoreOpponentsX, nextScoreOpponentsY);
                nextScoreOpponentsX += textScoreOpponentDimentions.width + marginScores;
                if( nextScoreOpponentsX > maxScoreOpponentsX ) {
                    nextScoreOpponentsX = textX + 35;
                    nextScoreOpponentsY += 13;
                }
            });
        }
        
        ctx.restore();
    }
    
    this.draw = function( params ){  
        if( params.persoX ) this.persoX = params.persoX;
        if( params.persoY ) this.persoX = params.persoY;

        
        if ( params.paramsBoard ) {
            //En fonction du type de sport on dessine un panneau different
            switch( params.activityType ) {
                case "session_team_sport":
                    this.drawBoard( params.paramsBoard );
                    break;
                    
                case "session_endurance_on_earth":
                default:
                    this.drawWoodPanel( params.paramsBoard );
                    break;
            }
            
        }
        //if( params.drawMilepost ) this.drawMilepost();
    }
};