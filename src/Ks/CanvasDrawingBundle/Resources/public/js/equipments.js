var Equipments = function(c, imagesPath){ 
    var ctx = c.getContext('2d');
    
    this.imagesPath = imagesPath;
    
    persoX = 0;  
    persoY = 0;
    
    this.drawArmband = function() {
        ctx.save();
        ctx.beginPath();
        ctx.moveTo( persoX + 0, persoY + 0);
        ctx.lineTo( persoX + 150, persoY + 0);
        ctx.lineTo( persoX + 150, persoY + 260);
        ctx.lineTo( persoX + 0, persoY + 260);
        ctx.closePath();
        ctx.clip();
        ctx.translate(0,0);
        ctx.translate(0,0);
        ctx.scale(1,1);
        ctx.translate(0,0);
        ctx.strokeStyle = 'rgba(0,0,0,0)';
        ctx.lineCap = 'butt';
        ctx.lineJoin = 'miter';
        ctx.miterLimit = 4;
        ctx.save();
        ctx.fillStyle = "#000000";
        ctx.globalAlpha = 1;
        ctx.beginPath();
        ctx.moveTo( persoX + 34.92, persoY + 119.84);
        ctx.bezierCurveTo( persoX + 35.12, persoY + 118.6, persoX + 36.05, persoY + 117.75, persoX + 36.87, persoY + 116.89);
        ctx.bezierCurveTo( persoX + 37.83, persoY + 117.76, persoX + 38.84, persoY + 118.58, persoX + 39.94, persoY + 119.28);
        ctx.bezierCurveTo( persoX + 39.67, persoY + 120.78, persoX + 39.4, persoY + 122.29, persoX + 39.14, persoY + 123.8);
        ctx.bezierCurveTo( persoX + 40.43, persoY + 124.28, persoX + 41.71, persoY + 124.78, persoX + 42.99, persoY + 125.27);
        ctx.bezierCurveTo( persoX + 42.97, persoY + 126.91, persoX + 42.98, persoY + 128.55, persoX + 43, persoY + 130.19);
        ctx.bezierCurveTo( persoX + 41.32, persoY + 129.56, persoX + 39.67, persoY + 128.86, persoX + 38.03, persoY + 128.14);
        ctx.bezierCurveTo( persoX + 37.63, persoY + 130.05, persoX + 37.21, persoY + 131.96, persoX + 36.79, persoY + 133.86);
        ctx.bezierCurveTo( persoX + 35.32, persoY + 133.21, persoX + 33.39, persoY + 132.63, persoX + 33.16, persoY + 130.74);
        ctx.bezierCurveTo( persoX + 32.86, persoY + 127.03, persoX + 34.08, persoY + 123.41, persoX + 34.92, persoY + 119.84);
        ctx.moveTo( persoX + 36.21, persoY + 118.89);
        ctx.bezierCurveTo( persoX + 35.32, persoY + 122.49, persoX + 34.13, persoY + 126.07, persoX + 33.97, persoY + 129.8);
        ctx.bezierCurveTo( persoX + 34.49, persoY + 130.27, persoX + 35.53, persoY + 131.19, persoX + 36.04, persoY + 131.65);
        ctx.bezierCurveTo( persoX + 37.16, persoY + 128.23, persoX + 38.13, persoY + 124.73, persoX + 38.48, persoY + 121.14);
        ctx.bezierCurveTo( persoX + 38.53, persoY + 119.82, persoX + 37.15, persoY + 119.38, persoX + 36.21, persoY + 118.89);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.restore();
        ctx.save();
        ctx.fillStyle = "#577cb4";
        ctx.globalAlpha = 1;
        ctx.beginPath();
        ctx.moveTo( persoX + 36.21, persoY + 118.89);
        ctx.bezierCurveTo( persoX + 37.15, persoY + 119.38, persoX + 38.53, persoY + 119.82, persoX + 38.48, persoY + 121.14);
        ctx.bezierCurveTo( persoX + 38.13, persoY + 124.73, persoX + 37.16, persoY + 128.23, persoX + 36.04, persoY + 131.65);
        ctx.bezierCurveTo( persoX + 35.53, persoY + 131.19, persoX + 34.49, persoY + 130.27, persoX + 33.97, persoY + 129.8);
        ctx.bezierCurveTo( persoX + 34.13, persoY + 126.07, persoX + 35.32, persoY + 122.49, persoX + 36.21, persoY + 118.89);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.restore();
        ctx.restore();
    };
    
    this.drawWatch = function() {
        ctx.save();
        ctx.beginPath();
        ctx.moveTo( persoX + 0, persoY + 0);
        ctx.lineTo( persoX + 150, persoY + 0);
        ctx.lineTo( persoX + 150, persoY + 260);
        ctx.lineTo( persoX + 0, persoY + 260);
        ctx.closePath();
        ctx.clip();
        ctx.translate(0,0);
        ctx.translate(0,0);
        ctx.scale(1,1);
        ctx.translate(0,0);
        ctx.strokeStyle = 'rgba(0,0,0,0)';
        ctx.lineCap = 'butt';
        ctx.lineJoin = 'miter';
        ctx.miterLimit = 4;
        ctx.save();
        ctx.fillStyle = "#363638";
        ctx.globalAlpha = 0.8600000143051147;
        ctx.beginPath();
        ctx.moveTo( persoX + 83.96, persoY + 145.14);
        ctx.bezierCurveTo( persoX + 85.34, persoY + 144.44, persoX + 86.66, persoY + 143.56, persoX + 88.15, persoY + 143.11);
        ctx.bezierCurveTo( persoX + 89.98, persoY + 143.07, persoX + 91.41, persoY + 144.48, persoX + 92.96, persoY + 145.25);
        ctx.bezierCurveTo( persoX + 93.07, persoY + 146.45, persoX + 93.19, persoY + 147.66, persoX + 93.3, persoY + 148.86);
        ctx.bezierCurveTo( persoX + 91.52, persoY + 149.86, persoX + 89.66, persoY + 151.7, persoX + 87.45, persoY + 150.87);
        ctx.bezierCurveTo( persoX + 86.24, persoY + 150.26, persoX + 85.1, persoY + 149.54, persoX + 83.95, persoY + 148.86);
        ctx.bezierCurveTo( persoX + 83.95, persoY + 147.62, persoX + 83.95, persoY + 146.38, persoX + 83.96, persoY + 145.14);
        ctx.moveTo( persoX + 86.36, persoY + 148.49);
        ctx.bezierCurveTo( persoX + 87.35, persoY + 149.93, persoX + 89.2, persoY + 149.58, persoX + 90.7, persoY + 149.65);
        ctx.bezierCurveTo( persoX + 90.78, persoY + 147.96, persoX + 91.71, persoY + 145.41, persoX + 89.45, persoY + 144.74);
        ctx.bezierCurveTo( persoX + 87.35, persoY + 143.42, persoX + 84.8, persoY + 146.59, persoX + 86.36, persoY + 148.49);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.restore();
        ctx.save();
        ctx.fillStyle = "#a2a4a7";
        ctx.globalAlpha = 1;
        ctx.beginPath();
        ctx.moveTo( persoX + 86.36, persoY + 148.49);
        ctx.bezierCurveTo( persoX + 84.8, persoY + 146.59, persoX + 87.35, persoY + 143.42, persoX + 89.45, persoY + 144.74);
        ctx.bezierCurveTo( persoX + 91.71, persoY + 145.41, persoX + 90.78, persoY + 147.96, persoX + 90.7, persoY + 149.65);
        ctx.bezierCurveTo( persoX + 89.2, persoY + 149.58, persoX + 87.35, persoY + 149.93, persoX + 86.36, persoY + 148.49);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.restore();
        ctx.restore();
    };
    
    this.drawRoadBike = function() {
        ctx.save();
        bikeWithoutFrameImg = new Image();
        bikeWithoutFrameImg.src = imagesPath + "bike/road_bike/without_frame.png";
        
        var bikeH = 85;
        var bikeX = 10;
        var bikeY = c.height - bikeH - 5;
        
        bikeWithoutFrameImg.onload = function() {            
            ctx.drawImage(bikeWithoutFrameImg, persoX + bikeX + 0, persoY + bikeY + 0);
        };
        ctx.restore();
        
        ctx.save();
        ctx.beginPath();
        ctx.moveTo( persoX + bikeX + 0, persoY + bikeY + 0);
        ctx.lineTo( persoX + bikeX + 212.5, persoY + bikeY + 0);
        ctx.lineTo( persoX + bikeX + 212.5, persoY + bikeY + 106.25);
        ctx.lineTo( persoX + bikeX + 0, persoY + bikeY + 106.25);
        ctx.closePath();
        ctx.clip();
        ctx.translate(0,0);
        ctx.translate(0,0);
        ctx.scale(1,1);
        ctx.translate(0,0);
        ctx.strokeStyle = 'rgba(0,0,0,0)';
        ctx.lineCap = 'butt';
        ctx.lineJoin = 'miter';
        ctx.miterLimit = 4;
        ctx.save();
        ctx.fillStyle = this.bikeColor;
        ctx.beginPath();
        ctx.moveTo( persoX + bikeX + 90.46, persoY + bikeY + 8.1);
        ctx.bezierCurveTo( persoX + bikeX + 93.14, persoY + bikeY + 8.57, persoX + bikeX + 95.85, persoY + bikeY + 8.43, persoX + bikeX + 98.56, persoY + bikeY + 8.31);
        ctx.bezierCurveTo( persoX + bikeX + 97.62, persoY + bikeY + 10.41, persoX + bikeX + 94.62, persoY + bikeY + 10.17, persoX + bikeX + 93.67, persoY + bikeY + 12.05);
        ctx.bezierCurveTo( persoX + bikeX + 96.84, persoY + bikeY + 22.32, persoX + bikeX + 99.53, persoY + bikeY + 32.75, persoX + bikeX + 103.14, persoY + bikeY + 42.87);
        ctx.bezierCurveTo( persoX + bikeX + 104.94, persoY + bikeY + 48.17, persoX + bikeX + 108.26, persoY + bikeY + 52.72, persoX + bikeX + 110.8, persoY + bikeY + 57.64);
        ctx.bezierCurveTo( persoX + bikeX + 108.18, persoY + bikeY + 55.7, persoX + bikeX + 106.49, persoY + bikeY + 52.85, persoX + bikeX + 104.89, persoY + bikeY + 50.06);
        ctx.bezierCurveTo( persoX + bikeX + 100.92, persoY + bikeY + 42.96, persoX + bikeX + 98.59, persoY + bikeY + 35.12, persoX + bikeX + 96.12, persoY + bikeY + 27.4);
        ctx.bezierCurveTo( persoX + bikeX + 90.73, persoY + bikeY + 32.96, persoX + bikeX + 86.07, persoY + bikeY + 39.16, persoX + bikeX + 80.93, persoY + bikeY + 44.93);
        ctx.bezierCurveTo( persoX + bikeX + 76.94, persoY + bikeY + 49.46, persoX + bikeX + 73.58, persoY + bikeY + 54.59, persoX + bikeX + 68.85, persoY + bikeY + 58.41);
        ctx.bezierCurveTo( persoX + bikeX + 68.64, persoY + bikeY + 56.85, persoX + bikeX + 68.98, persoY + bikeY + 55.3, persoX + bikeX + 70.23, persoY + bikeY + 54.26);
        ctx.bezierCurveTo( persoX + bikeX + 78.14, persoY + bikeY + 45.14, persoX + bikeX + 85.83, persoY + bikeY + 35.83, persoX + bikeX + 93.69, persoY + bikeY + 26.66);
        ctx.bezierCurveTo( persoX + bikeX + 94.95, persoY + bikeY + 25.43, persoX + bikeX + 94.93, persoY + bikeY + 23.57, persoX + bikeX + 95.33, persoY + bikeY + 21.97);
        ctx.bezierCurveTo( persoX + bikeX + 90.71, persoY + bikeY + 20.39, persoX + bikeX + 85.81, persoY + bikeY + 21.52, persoX + bikeX + 81.06, persoY + bikeY + 21.63);
        ctx.bezierCurveTo( persoX + bikeX + 71.63, persoY + bikeY + 22.31, persoX + bikeX + 62.15, persoY + bikeY + 22.02, persoX + bikeX + 52.73, persoY + bikeY + 23.04);
        ctx.bezierCurveTo( persoX + bikeX + 55.67, persoY + bikeY + 33.89, persoX + bikeX + 59.02, persoY + bikeY + 44.61, persoX + bikeX + 62.04, persoY + bikeY + 55.43);
        ctx.bezierCurveTo( persoX + bikeX + 61.45, persoY + bikeY + 55.13, persoX + bikeX + 60.26, persoY + bikeY + 54.55, persoX + bikeX + 59.67, persoY + bikeY + 54.25);
        ctx.bezierCurveTo( persoX + bikeX + 56.66, persoY + bikeY + 44.15, persoX + bikeX + 53.74, persoY + bikeY + 34.02, persoX + bikeX + 50.79, persoY + bikeY + 23.91);
        ctx.bezierCurveTo( persoX + bikeX + 43.16, persoY + bikeY + 34.69, persoX + bikeX + 36.08, persoY + bikeY + 45.85, persoX + bikeX + 28.55, persoY + bikeY + 56.7);
        ctx.bezierCurveTo( persoX + bikeX + 37.1, persoY + bikeY + 58.51, persoX + bikeX + 45.83, persoY + bikeY + 59.44, persoX + bikeX + 54.3, persoY + bikeY + 61.53);
        ctx.bezierCurveTo( persoX + bikeX + 54.5, persoY + bikeY + 61.89, persoX + bikeX + 54.89, persoY + bikeY + 62.59, persoX + bikeX + 55.09, persoY + bikeY + 62.94);
        ctx.bezierCurveTo( persoX + bikeX + 45.17, persoY + bikeY + 62.08, persoX + bikeX + 35.65, persoY + bikeY + 58.82, persoX + bikeX + 25.85, persoY + bikeY + 57.33);
        ctx.bezierCurveTo( persoX + bikeX + 27.34, persoY + bikeY + 55.99, persoX + bikeX + 28.88, persoY + bikeY + 54.66, persoX + bikeX + 29.99, persoY + bikeY + 52.97);
        ctx.bezierCurveTo( persoX + bikeX + 36.22, persoY + bikeY + 43.53, persoX + bikeX + 42.34, persoY + bikeY + 34.02, persoX + bikeX + 48.69, persoY + bikeY + 24.66);
        ctx.bezierCurveTo( persoX + bikeX + 50.09, persoY + bikeY + 22.99, persoX + bikeX + 49.77, persoY + bikeY + 20.79, persoX + bikeX + 49.83, persoY + bikeY + 18.78);
        ctx.bezierCurveTo( persoX + bikeX + 51.97, persoY + bikeY + 19.91, persoX + bikeX + 54.22, persoY + bikeY + 20.98, persoX + bikeX + 56.72, persoY + bikeY + 20.71);
        ctx.bezierCurveTo( persoX + bikeX + 68.94, persoY + bikeY + 19.69, persoX + bikeX + 81.23, persoY + bikeY + 19.96, persoX + bikeX + 93.44, persoY + bikeY + 18.82);
        ctx.bezierCurveTo( persoX + bikeX + 92.99, persoY + bikeY + 15.08, persoX + bikeX + 90.41, persoY + bikeY + 11.91, persoX + bikeX + 90.46, persoY + bikeY + 8.1);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.restore();
        ctx.restore();
    };
    
    this.drawMountainBike = function() {
        ctx.save();
        bikeWithoutFrameImg = new Image();
        bikeWithoutFrameImg.src = imagesPath + "bike/mountain_bike/without_frame.png";
        
        var bikeH = 85;
        var bikeX = 10;
        var bikeY = c.height - bikeH - 5;
        
        bikeWithoutFrameImg.onload = function() {            
            ctx.drawImage(bikeWithoutFrameImg, persoX + bikeX + 0, persoY + bikeY + 0);
        };
        ctx.restore();
        ctx.save();
        ctx.beginPath();
        ctx.moveTo( persoX + bikeX + 0, persoY + bikeY + 0);
        ctx.lineTo( persoX + bikeX + 212.5, persoY + bikeY + 0);
        ctx.lineTo( persoX + bikeX + 212.5, persoY + bikeY + 106.25);
        ctx.lineTo( persoX + bikeX + 0, persoY + bikeY + 106.25);
        ctx.closePath();
        ctx.clip();
        ctx.translate(0,0);
        ctx.translate(0,0);
        ctx.scale(1,1);
        ctx.translate(0,0);
        ctx.strokeStyle = 'rgba(0,0,0,0)';
        ctx.lineCap = 'butt';
        ctx.lineJoin = 'miter';
        ctx.miterLimit = 4;
        ctx.save();
        ctx.fillStyle = this.bikeColor;
        ctx.beginPath();
        ctx.moveTo( persoX + bikeX + 93.51, persoY + bikeY + 15.58);
        ctx.bezierCurveTo( persoX + bikeX + 94.85, persoY + bikeY + 14.53, persoX + bikeX + 95.87, persoY + bikeY + 13.11, persoX + bikeX + 97.16, persoY + bikeY + 11.99);
        ctx.bezierCurveTo( persoX + bikeX + 98.55, persoY + bikeY + 14.86, persoX + bikeX + 99.71, persoY + bikeY + 17.83, persoX + bikeX + 100.76, persoY + bikeY + 20.84);
        ctx.bezierCurveTo( persoX + bikeX + 98.78, persoY + bikeY + 21.73, persoX + bikeX + 96.44, persoY + bikeY + 22.19, persoX + bikeX + 95.16, persoY + bikeY + 24.13);
        ctx.bezierCurveTo( persoX + bikeX + 86.31, persoY + bikeY + 34.71, persoX + bikeX + 77.56, persoY + bikeY + 45.37, persoX + bikeX + 68.72, persoY + bikeY + 55.97);
        ctx.bezierCurveTo( persoX + bikeX + 67.75, persoY + bikeY + 55.36, persoX + bikeX + 66.89, persoY + bikeY + 54.6, persoX + bikeX + 66.15, persoY + bikeY + 53.73);
        ctx.bezierCurveTo( persoX + bikeX + 76.12, persoY + bikeY + 41.7, persoX + bikeX + 86.36, persoY + bikeY + 29.91, persoX + bikeX + 96.29, persoY + bikeY + 17.85);
        ctx.bezierCurveTo( persoX + bikeX + 82.29, persoY + bikeY + 21.37, persoX + bikeX + 68.96, persoY + bikeY + 27.15, persoX + bikeX + 54.94, persoY + bikeY + 30.56);
        ctx.bezierCurveTo( persoX + bikeX + 56.58, persoY + bikeY + 38.17, persoX + bikeX + 59.77, persoY + bikeY + 45.34, persoX + bikeX + 61.8, persoY + bikeY + 52.86);
        ctx.bezierCurveTo( persoX + bikeX + 60.96, persoY + bikeY + 53.15, persoX + bikeX + 60.11, persoY + bikeY + 53.44, persoX + bikeX + 59.26, persoY + bikeY + 53.73);
        ctx.bezierCurveTo( persoX + bikeX + 56.82, persoY + bikeY + 46.77, persoX + bikeX + 54.87, persoY + bikeY + 39.65, persoX + bikeX + 52.65, persoY + bikeY + 32.62);
        ctx.bezierCurveTo( persoX + bikeX + 51.42, persoY + bikeY + 33.36, persoX + bikeX + 50.2, persoY + bikeY + 34.15, persoX + bikeX + 49.2, persoY + bikeY + 35.2);
        ctx.bezierCurveTo( persoX + bikeX + 43.69, persoY + bikeY + 40.7, persoX + bikeX + 38.28, persoY + bikeY + 46.29, persoX + bikeX + 32.76, persoY + bikeY + 51.77);
        ctx.bezierCurveTo( persoX + bikeX + 31.61, persoY + bikeY + 52.95, persoX + bikeX + 30.28, persoY + bikeY + 53.95, persoX + bikeX + 28.71, persoY + bikeY + 54.49);
        ctx.bezierCurveTo( persoX + bikeX + 31.17, persoY + bikeY + 50.46, persoX + bikeX + 34.94, persoY + bikeY + 47.53, persoX + bikeX + 38.25, persoY + bikeY + 44.26);
        ctx.bezierCurveTo( persoX + bikeX + 42.91, persoY + bikeY + 39.81, persoX + bikeX + 47.26, persoY + bikeY + 35.05, persoX + bikeX + 51.95, persoY + bikeY + 30.64);
        ctx.bezierCurveTo( persoX + bikeX + 51.32, persoY + bikeY + 29.21, persoX + bikeX + 50.68, persoY + bikeY + 27.79, persoX + bikeX + 50.1, persoY + bikeY + 26.35);
        ctx.bezierCurveTo( persoX + bikeX + 51.31, persoY + bikeY + 26.05, persoX + bikeX + 52.51, persoY + bikeY + 25.75, persoX + bikeX + 53.71, persoY + bikeY + 25.41);
        ctx.bezierCurveTo( persoX + bikeX + 53.83, persoY + bikeY + 26.07, persoX + bikeX + 54.08, persoY + bikeY + 27.39, persoX + bikeX + 54.2, persoY + bikeY + 28.05);
        ctx.bezierCurveTo( persoX + bikeX + 62.34, persoY + bikeY + 25.77, persoX + bikeX + 70.3, persoY + bikeY + 22.86, persoX + bikeX + 78.39, persoY + bikeY + 20.41);
        ctx.bezierCurveTo( persoX + bikeX + 83.49, persoY + bikeY + 19.01, persoX + bikeX + 88.59, persoY + bikeY + 17.56, persoX + bikeX + 93.51, persoY + bikeY + 15.58);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.restore();
        ctx.save();
        ctx.fillStyle = this.bikeColor;
        ctx.beginPath();
        ctx.moveTo( persoX + bikeX + 25.46, persoY + bikeY + 58.04);
        ctx.bezierCurveTo( persoX + bikeX + 26.75, persoY + bikeY + 56.41, persoX + bikeX + 28.37, persoY + bikeY + 55.03, persoX + bikeX + 30.44, persoY + bikeY + 54.55);
        ctx.bezierCurveTo( persoX + bikeX + 29.28, persoY + bikeY + 55.9, persoX + bikeX + 27.96, persoY + bikeY + 57.1, persoX + bikeX + 26.7, persoY + bikeY + 58.35);
        ctx.bezierCurveTo( persoX + bikeX + 35.98, persoY + bikeY + 59.18, persoX + bikeX + 45.31, persoY + bikeY + 58.62, persoX + bikeX + 54.59, persoY + bikeY + 59.38);
        ctx.bezierCurveTo( persoX + bikeX + 54.75, persoY + bikeY + 59.73, persoX + bikeX + 55.05, persoY + bikeY + 60.43, persoX + bikeX + 55.2, persoY + bikeY + 60.79);
        ctx.bezierCurveTo( persoX + bikeX + 45.85, persoY + bikeY + 61.31, persoX + bikeX + 36.49, persoY + bikeY + 60.33, persoX + bikeX + 27.2, persoY + bikeY + 59.34);
        ctx.bezierCurveTo( persoX + bikeX + 26.76, persoY + bikeY + 59.01, persoX + bikeX + 25.89, persoY + bikeY + 58.36, persoX + bikeX + 25.46, persoY + bikeY + 58.04);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.restore();
        ctx.restore();
    };
    
    this.drawTennisRacquet = function() {
        ctx.save();
        tennisRacquetImg = new Image();
        tennisRacquetImg.src = imagesPath + "tennis/racquet.png";
        tennisRacquetImg.onload = function() {            
            ctx.drawImage(tennisRacquetImg, persoX + 2, persoY + 92);
        };
        ctx.restore();
    };
    
    this.draw = function( params ) {  
        if( params.persoX ) persoX = params.persoX;
        if( params.persoY ) persoX = params.persoY;
        
        if( params.drawArmband ) this.drawArmband();
        if( params.drawWatch ) this.drawWatch();
        
        //Bikes
        if( params.drawRoadBike ) this.drawRoadBike();
        if( params.drawMountainBike ) this.drawMountainBike();
        
        //Tennis
        if( params.drawTennisRacquet ) this.drawTennisRacquet();
    };
    
};