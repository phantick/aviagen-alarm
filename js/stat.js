App.stat= function() {
    var $c, $view, initialized, $container, dates, 
        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            $view = $(doT.template($("#statViewTmpl").text())());
            $container = $view.find(".container");

            $c.append($view);

            $(".dtp").datepicker({
            	inline: true, 
            	dateFormat: "yyyy-mm-dd",
            	onRenderCell: function (date, cellType) {
			        if (cellType == 'day') {
			            var isDisabled = !dates||dates.indexOf(date.formatDate(Date.DATE_ISO))<0;

		            	return {
		    	            disabled: isDisabled
			            }
		        	}
			    }, 
			    onSelect: function(formattedDate, date, inst) {
	                $.ajax({url: "reports/Result "+formattedDate+".htm", async: false, cache : false, success: function(data) {
	                  data = data.replace("<html>", "").replace("</html>", "").replace("<body>", "").replace("</body>", "").replace("<head>", "").replace("</head>", "").replace(/\@page\s+\{.*\}/gmi, "").replace(/body\s+\{.*\}/gmi, "");
	                  $container.find(".html").html(data);
	                  $container.find("a.htm").attr("href", "reports/Result "+formattedDate+".htm");
	                  $container.find("a.xls").attr("href", "reports/Result "+formattedDate+".xlsx");
	                  $container.find(".buttons").show();
	                }});

			    }
			});
            $container.find(".buttons").hide();
            $("#btnUpdate").click(function() {
            	App.disable(true);
            	API.xlsUpdate(function(rc) {
	            	App.enable(true);
					API.xlsDates(function(d) { 
						dates = d.dates;
						$('.dtp').datepicker().data('datepicker').show();
					});
            	});
            });
            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);
			API.xlsDates(function(d) { 
				dates = d.dates;
				$('.dtp').datepicker().data('datepicker').show();
			});
            $view.show();
        },
        hide = function($cont) {
            if (!initialized) init($cont);
            $view.hide();
        };

    return {
        show: show,
        hide: hide
    }
}();

plugins.stat= true;