Report = window.Fefop || {};

var Currency = (function(){
	
	return {
		format: function(number, decimals, dec_point, thousands_sep, symbol) 
		{
			number 			= (number).toString().replace(/[^0-9+\-Ee.]/g, '');
			decimals 		= decimals || 2;
			dec_point 		= dec_point || ".";
			thousands_sep 	= thousands_sep || ",";
			symbol 			= symbol || "$";
			
	        var 
	        	n = !isFinite(+number) ? 0 : +number,
	            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
	            s = '',
	            toFixedFix = function(n, prec) 
	            {
	                var k = Math.pow(10, prec);
	                return '' + Math.round(n * k) / k;
	            };
	         
	        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
	        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	        
	        if (s[0].length > 3) {
	            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_sep);
	        }
	        
	        if ((s[1] || '').length < prec) {
	            s[1] = s[1] || '';
	            s[1] += new Array(prec - s[1].length + 1).join('0');
	        }
	        
	        return symbol + s.join(dec_point);
	    }
	};
	
})();

Report.Fefop = {
		
    init: function()
    {
		$("#slider-amount").slider({
			range: true,
			min: 0,
			max: 1000000,
			step: 50000,
			values: [0, 50000],
			slide: function( event, ui ) 
			{
			    $("#slider-age-amount").text(Currency.format(ui.values[0]) + " - " + Currency.format(ui.values[1]));
			    $("#minimum_amount").val(ui.values[0]);
			    $("#maximum_amount").val(ui.values[1]);
			}
	    });
    },
    
};

$(document).ready(Report.Fefop.init);