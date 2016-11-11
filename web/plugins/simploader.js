var simploader = {
  total: 0,
  count: 0,
  progress: 0,

  callbacks: [ ],

  add: function() {
    this.loading = true;    
    this.count++;
    this.total++;
  },

  image: function(item) {
    if(item instanceof Array) for(var i = 0; i < item.length; i++) this.image(item[i]);
    else {      
      item.addEventListener("load", function() { simploader.ready() });
      item.onerror = this.onItemError;
      this.add();
    }
  },

  audio: function(item) {
    if(item instanceof Array) for(var i = 0; i < item.length; i++) this.audio(item[i]);
    else {
      item.addEventListener("canplay", function() { simploader.ready() });
      this.add();
    }
  },

  font: function(item) {
    if(item instanceof Array) for(var i = 0; i < item.length; i++) this.font(item[i]);
    else {
      var font = new simploader.Font(item);
      font.onload = function() { simploader.ready() };
      this.add();
    }
  },

  ready: function(callback) {
    if(callback) this.callbacks.push(callback);
    else {
      this.count--;
      this.progress = (this.total - this.count) / this.total;
      if(!this.count) {
        for(var i = 0; i < this.callbacks.length; i++) this.callbacks[i]();        
        this.callbacks = [];
        this.total = 0;
        this.loading = false;
      }
    }
  },

  onItemError: function (e) {
    console.log("unable to load some retarded item", this.src);
  }
}

  simploader.Font = function(fontName) {

    this.fontName = fontName;

    var self = this;

    var placeholder = cq(64, 64);
    var timer = setInterval(function() {

      // placeholder.canvas.height = 64;
      // placeholder.canvas.width = 64;
      var previousHeight = placeholder.canvas.height;
      placeholder.clear().font("32px " + fontName).fillText("test", 32, 32).trim();
      
      if((window.mozIndexedDB && placeholder.context.font !== "10px sans-serif") || (!window.mozIndexedDB && placeholder.canvas.height !== previousHeight)) {
        self.onload.call(self);
        clearInterval(timer);
      }
    }, 500);

    this.onload = function() {};
  }
