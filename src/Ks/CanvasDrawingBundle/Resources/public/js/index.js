
var sky = null;
var perso = null;
var accessories = null;




var init = function() {
    //Déclencehement du timer pour mettre à jour le souffle
    //var breathInterval = setInterval("perso.updateBreath()", 1000 / 60);
    
    //Initialisation des nuages
    sky.initClouds(10);
    
    
    resizeCanvas();
}

$(document).ready(function() {  
    c = document.getElementById('canvas');     
    c.height = 210;  

    ctx = c.getContext('2d');

    var imagesPath = "/keepinsport/web/bundles/kscanvasdrawing/images/";
    
    //Initialisation des éléments
    sky = new Sky(c, imagesPath);
    perso = new Perso(c);
    accessories = new Accessory(c, imagesPath);
    
    //Initialisation des colorpicker
    $("#skinTonSelector").spectrum({
        showPaletteOnly: true,
        showPalette:true,
        color: perso.skinColor,
        palette: [
            ['#FFDCB1', '#FCB275', '#E4B98E', '#E2B98F', '#E3A173'],
            ['#D99164', '#C8443', '#C77A58', '#A53900', '#880400'],
            ['#710200', '#440000', '#FFE0C4', '#EECFB4', '#DEAB7F'],
            ['#E0B184', '#DFA675', '#BE723C', '#A01900', '#5B0000'],
            ['#000000', '#EDE4C8', '#EFD6BD', '#EABD9D', '#E3C2AE'],
            ['#DFB997', '#D0926E', '#BD9778', '#BB6D4A', '#940A00'],
            ['#E1ADA4', '#A58869', '#7B0000', '#720000', '#380000']
        ],
        change: function( color ) {
            clear(c); 
            perso.skinColor = color.toHexString()
            reDraw(c);
        }
    });
    $("#eyesColorSelector").spectrum({
        showPaletteOnly: true,
        showPalette:true,
        color: perso.eyesColor,
        palette: [
            ['black', 'brown', 'green', 'blue']
        ],
        change: function( color ) {
            clear(c); 
            perso.eyesColor = color.toHexString()
            reDraw(c);
        }
    });
    $("#hairColorSelector").spectrum({
        showPaletteOnly: true,
        showPalette:true,
        color: perso.hairColor,
        palette: [
            ["#000000", "#FCD505", "#E5E4E0"],
            ['#584A22', '#A28262', '#BF9E12', '#996619', '#7E533C'],
            ['#693824', '#B55B1B', '#E47122', '#AF3432', '#732727'],
        ],
        change: function( color ) {
            clear(c); 
            perso.hairColor = color.toHexString()
            reDraw(c);
        }
    });
    $("#shirtColorSelector").spectrum({
        color: perso.shirtColor,
        change: function( color ) {
            clear(c); 
            perso.shirtColor = color.toHexString()
            reDraw(c);
        }
    });
    $("#shortColorSelector").spectrum({
        color: perso.shortColor,
        change: function( color ) {
            clear(c); 
            perso.shortColor = color.toHexString()
            reDraw(c);
        }
    });
    $("#shoesColor1Selector").spectrum({
        color: perso.shoesPrimaryColor,
        change: function( color ) {
            clear(c); 
            perso.shoesPrimaryColor = color.toHexString()
            reDraw(c);
        }
    });
    $("#shoesColor2Selector").spectrum({
        color: perso.shoesSecondaryColor,
        change: function( color ) {
            clear(c); 
            perso.shoesSecondaryColor = color.toHexString()
            reDraw(c);
        }
    });
    
    $("#faceSelector").change(function() {
        perso.face = $( this ).val();
    });
    
    //Si la feneêtre est redimentionné, on adapte le canvas
    window.addEventListener('resize', resizeCanvas, false);
    
    init();
    
    //On repaint toutes les 20 milisecondes
    setInterval('reDraw(c)',20);
});

