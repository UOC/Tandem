<html>
<head>
<script src="js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.8.17.custom.min.js" type="text/javascript"></script>
<script src="js/jquery.phototag.js" type="text/javascript"></script>

<link rel="stylesheet" href="css/jquery-ui-1.8.17.custom.css" type="text/css" media="screen" />
<script type="text/javascript">
$(document).ready(function(){
	$('.photoTag').photoTag({
		requesTagstUrl: 'http://karlmendes.com/static/photo-tag/tests/photo_tags/photo-tags.php',
		deleteTagsUrl: 'http://karlmendes.com/static/photo-tag/tests/photo_tags/delete.php',
		addTagUrl: 'http://karlmendes.com/static/photo-tag/tests/photo_tags/add-tag.php',
		parametersForNewTag: {
			name: {
				parameterKey: 'name',
				isAutocomplete: true,
				autocompleteUrl: 'http://karlmendes.com/static/photo-tag/tests/photo_tags/names.php',
				label: 'Name'
			}
		}
	});
	/*setTimeout(function() {
		$('#photoTag-wrap_150 .photoTag-tag').each(function(i){
			var image = $('#image1');
			var imagePosition = image.position();
			var tagPosition = $(this).position();
			var isInsidePositionX = ((imagePosition.left + image.width()) > tagPosition.left > imagePosition.left);
			var isInsidePositionY = ((imagePosition.top + image.height()) > tagPosition.top > imagePosition.top);
			var isInside = isInsidePositionX && isInsidePositionY;		
			ok(isInside,"Tag " + i + " is inside the image");
			ok($(this).html()!="","Tag" + i + " has some text inside");
		});
	}, 600);*/
});

</script>
</head>
<body>
<img src="http://karlmendes.com/static/photo-tag/tests/photo_tags/monkeys2.jpg" class="photoTag" data-user-id="25" data-image-id="200" data-album-id="200">
</body>
</html>