var isCanvasSupported = function()
{
  var elem = document.createElement('canvas'); 
  return !!(elem.getContext && elem.getContext('2d'));
}

var clear = function(c){   
    var ctx = c.getContext('2d');
    
    ctx.save();
    ctx.beginPath();  
    ctx.clearRect(0, 0, c.width, c.height);  
    ctx.closePath();  
    ctx.restore();
}  

function resizeCanvas(activityId, c, cBg) {
    var parentWidth = $(c).parent().parent().width();
    var maxW = 400;
    //On bloque la largeur à maxW
    if(typeof c != 'undefined' && c != null) c.width = parentWidth < maxW ? parentWidth : maxW;
    if(typeof cBg != 'undefined' && cBg != null) cBg.width = c.width;
    
    //console.log($(c).parent().parent().width() + "-" + c.width);
    
    eval('reDraw' + activityId + '()');
}

getRandomInt = function(start, end) {
    return Math.floor(Math.random() * (end - start + 1)) + start;
}

function drawBubble(ctx, x, y, w, h, radius)
{
  var r = x + w;
  var b = y + h;
  ctx.save();
  ctx.beginPath();
  ctx.strokeStyle="black";
  ctx.fillStyle="white";
  ctx.lineWidth="2";
  ctx.moveTo(x+radius, y);
  
  ctx.lineTo(x+radius * 2, y);
  ctx.lineTo(r-radius, y);
  ctx.quadraticCurveTo(r, y, r, y+radius);
  ctx.lineTo(r, y+h-radius);
  ctx.quadraticCurveTo(r, b, r-radius, b);
  ctx.lineTo(x+radius *3, b);
  ctx.lineTo(x-radius/2, b+10);
  ctx.lineTo(x+radius, b);
  ctx.quadraticCurveTo(x , b, x, b-radius);
  ctx.lineTo(x, y+radius);
  ctx.quadraticCurveTo(x, y, x+radius, y);
  ctx.globalAlpha = 0.8;
  ctx.fill()
  ctx.stroke();
  ctx.restore();
}

function drawTextBubble(c, x, y, w, text) {
    var ctx = c.getContext('2d');
    //On écrit une première fois le texte pour connaître ses dimentions
    heightText = wrapText(ctx, 'no_fill', text, x + 10, y , c.width - x - 20, 10);
    
    //On construit la bulle en fonction des dimentions du texte
    drawBubble(ctx, x, y, w, heightText, 10)
    
    //On écrit le texte par dessus la bulle
    wrapText(ctx, 'fill', text, x + 10, y , c.width - x - 20, 10);
}

function wrapText(ctx, context, text, x, y, maxWidth, lineHeight) {
    var words = text.split(/ |\n|<br>/);

    var line = '';
    var height = lineHeight * 2;
    
    ctx.save();
    ctx.beginPath();
    ctx.textBaseline = "top";
    ctx.font = "normal normal 10px sans-serif";
    ctx.fillStyle = "black";
    for(var n = 0; n < words.length; n++) {
      var testLine = line + words[n] + ' ';
      var metrics = ctx.measureText(testLine);
      var testWidth = metrics.width;

      if ((testWidth > maxWidth && n > 0) || words[n] == '') {
        if (context == "fill") ctx.fillText(line, x, y);
        line = words[n] != '' ? words[n] + ' ' : '';
        y += lineHeight;
        height += lineHeight;
      }
      else {
        line = testLine;
      }
    }
    if (context == "fill") ctx.fillText(line, x, y);
    
    ctx.closePath();
    ctx.fill();
    ctx.stroke();
    ctx.restore();
    
    return height;
  }
  
function preload_img(url)
{
    var img = new Image();
    img.src=url;
    //simploader.image(img);
    return img;
}

var preload_webFont = function(fontName) {
    //simploader.font(fontName);
};

