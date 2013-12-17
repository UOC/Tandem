var j=jQuery.noConflict();

j(document).ready(function(){
	j(".tab_content").hide();
	j("ul.tabs li:first").addClass("active").show();
	j(".tab_content:first").show();

	j("ul.tabs li").click(function(ev) {
		ev.preventDefault();
		j("ul.tabs li").removeClass("active");
		j(this).addClass("active");
		j(".tab_content").hide();

		var activeTab = j(this).find("a").attr("href");
		j(activeTab).fadeIn("fast");
	});

	j(".colorbox").colorbox({
		transition:"fade",
		speed: 300,
		opacity:0.7
	});

});