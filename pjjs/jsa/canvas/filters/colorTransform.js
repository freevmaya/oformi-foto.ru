var colorTransform = new Class({
    Extends: baseFilter,
    apply: function(pixels) {
        function limit(value) {
            if (value < 0) return 0;
            else if (value > 255) return 255;
            return value;
        }
        var d = pixels.data;
        var contrast = 1 + this.options.con / 100;
        var gray = -this.options.gray / 127;
        var bright = this.options.bright;
        var con = this.options.con;
        
        for (var i=0; i<d.length; i+=4) {
            var gs = d[i] * .3 + d[i+1] * .59 + d[i+2] * .11;
            
            d[i] = limit(((d[i] * (1 - gray) + gs * gray) + bright + this.options.r - con) * contrast);
            d[i+1] = limit(((d[i+1] * (1 - gray) + gs * gray) + bright + this.options.g - con) * contrast);
            d[i+2] = limit(((d[i+2] * (1 - gray) + gs * gray) + bright + this.options.b - con) * contrast);
        }
        return this.parent(pixels);
    }
});

colorTransform.toFloat = function(a_colors) {
    return [a_colors.gray / 127, a_colors.r / 127, a_colors.g / 127, a_colors.b / 127]; 
}; 

colorTransform.toInt = function(a_colors) {
    var l_colors = {};
    for (var n in a_colors) l_colors[n] = parseInt(a_colors[n] * 127);
    return {gray:l_colors[0],bright:0,con:0,r:l_colors[1],g:l_colors[2],b:l_colors[3]}; 
};