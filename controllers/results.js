function ResultsManager() {
    var self = this;
    var api = '/expeerify/api/results.php';
    var push;

    var $body = $('body');

    var getResult = function(id) {
	var params = id ? '?id=' + id : '?id=0';
	var request = $.ajax({
	    type : "GET",
	    contentType : "application/json; charset=utf-8",
	    url : encodeURI(api + params),
	    dataType : 'json'
	});

	$body.attr('aria-busy', true);

	_renderResult(request);
    }

    var _renderResult = function(request) {
	var renderer = $.get('renderers/result.html');

	$.when(request, renderer).done(
		function(requestData, rendererData) {
		    var data = requestData[0].length ? JSON
			    .parse(requestData[0]) : requestData[0];

		    var $li = $body.find('li:first-of-type');

		    if ($li.length <= 0)
			return;

		    if (data.response[0].tried_open_without_key == 0) {
			$li.find('p:eq(0)').attr('aria-hidden', false);
			$li.find('i:eq(0)').attr('aria-hidden', false);
			$li.find('b:eq(0)').attr('aria-hidden', true);
		    } else if (data.response[0].tried_open_without_key == 1) {
			$li.find('p:eq(0)').attr('aria-hidden', false);
			$li.find('i:eq(0)').attr('aria-hidden', true);
			$li.find('b:eq(0)').attr('aria-hidden', false);
		    }

		    if (data.response[0].is_light_turned_on == 0) {
			$li.find('p:eq(1)').attr('aria-hidden', false);
			$li.find('i:eq(1)').attr('aria-hidden', false);
			$li.find('b:eq(1)').attr('aria-hidden', true);
		    } else if (data.response[0].is_light_turned_on == 1) {
			$li.find('p:eq(1)').attr('aria-hidden', false);
			$li.find('i:eq(1)').attr('aria-hidden', true);
			$li.find('b:eq(1)').attr('aria-hidden', false);
		    }
		    
		    if (data.response[0].end) {
			$li.find('dd:eq(3)').text(data.response[0].end);
			$li.attr('aria-busy', false);

			return;
		    } else if ($li.attr('aria-busy') == 'false') {
			var tmpls = $.templates({
			    resultTemplate : rendererData[0]
			}), html = $.templates.resultTemplate
				.render(data.response);

			$body.find('ul').prepend(html);

			return;
		    }
		}).fail(
		function(requestData) {
		    var data = requestData.length ? JSON.parse(requestData)
			    : requestData;

		    console.log(data);
		});
    }

    var getResults = function() {
	var request = $.ajax({
	    type : "GET",
	    contentType : "application/json; charset=utf-8",
	    url : encodeURI(api),
	    dataType : 'json'
	});

	$body.attr('aria-busy', true);

	_renderResults(request);
    }

    var _renderResults = function(request) {
	var request = request || $.get(api);
	var renderer = $.get('renderers/result.html');

	$.when(request, renderer)
		.done(
			function(requestData, rendererData) {
			    var data = requestData[0].length ? JSON
				    .parse(requestData[0]) : requestData[0];

			    var tmpls = $.templates({
				resultTemplate : rendererData[0]
			    }), html = $.templates.resultTemplate
				    .render(data.response);

			    $body.find('ul').append(html);

			    $body.attr('aria-busy', false);
			}).fail(
			function(requestData) {
			    var data = requestData.length ? JSON
				    .parse(requestData) : requestData;

			    console.log(data);
			});
    }

    $(window).on('load', function(e) {
	getResults();

	push = setInterval(function() {
	    getResult();
	}, 2000);
    });
}

$(document).ready(function() {
    window.results = new ResultsManager();
});