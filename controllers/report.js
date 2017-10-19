function ReportManager() {
	var self = this;
	var api = '/expeerify/api/records.php';
	var pull;

	var $chart4D = $('.Chart-4D');
	var $chart4DPoint = $chart4D.find('> input:first-of-type');
	var $chart4DQuadrants = $chart4D.find('> .quadrants');

	var profile4D = {
		intuited: 0,
		emotional: 0,
		sensed: 0,
		logical: 0
	};

	var updateProfile4DReport = function (id) {
		var params = id ? '?id=' + id : '?result=0';
		var request = $.ajax({
			type: "GET",
			contentType: "application/json; charset=utf-8",
			url: encodeURI(api + params),
			dataType: 'json'
		});

		_setProfile4D(request);
	}

	var _setProfile4D = function (request) {
		$.when(request).done(function (requestData) {
			var data = requestData.length ? JSON.parse(requestData) : requestData;

			profile4D.intuited = Number(data.response[0].intuited);
			profile4D.emotional = Number(data.response[0].emotional);
			profile4D.sensed = Number(data.response[0].sensed);
			profile4D.logical = Number(data.response[0].logical);

			_updateQuadrants();
			_setPointCenter(_getChartCenter());
		});
	}

	var _updateQuadrants = function () {
		var maxOpacityIntensity = 20;
		var greenOpacityIntensity = (profile4D.intuited - profile4D.sensed + profile4D.logical - profile4D.emotional) / maxOpacityIntensity;
		var blueOpacityIntensity = (profile4D.intuited - profile4D.sensed + profile4D.emotional - profile4D.logical) / maxOpacityIntensity;
		var yellowOpacityIntensity = (profile4D.sensed - profile4D.intuited + profile4D.emotional - profile4D.logical) / maxOpacityIntensity;
		var orangeOpacityIntensity = (profile4D.sensed - profile4D.intuited + profile4D.logical - profile4D.emotional) / maxOpacityIntensity;

		if (profile4D.intuited === profile4D.sensed === profile4D.logical === profile4D.emotional === 0) {
			$chart4DQuadrants.find('dt > [role=presentation]').css('opacity', 0);
		}

		/**
		 * Green.
		 */

		if (profile4D.intuited > profile4D.sensed && profile4D.logical > profile4D.emotional) {
			$chart4DQuadrants.find('dt:not(.quadrant-cultivating) > [role=presentation]').css('opacity', 0);
			$chart4DQuadrants.find('.quadrant-cultivating > [role=presentation]').css('opacity', greenOpacityIntensity);
		}

		/**
		 * Blue.
		 */

		if (profile4D.intuited > profile4D.sensed && profile4D.logical < profile4D.emotional) {
			$chart4DQuadrants.find('dt:not(.quadrant-visioning) > [role=presentation]').css('opacity', 0);
			$chart4DQuadrants.find('.quadrant-visioning > [role=presentation]').css('opacity', blueOpacityIntensity);
		}

		/**
		 * Yellow.
		 */

		if (profile4D.intuited < profile4D.sensed && profile4D.logical < profile4D.emotional) {
			$chart4DQuadrants.find('dt:not(.quadrant-including) > [role=presentation]').css('opacity', 0);
			$chart4DQuadrants.find('.quadrant-including > [role=presentation]').css('opacity', yellowOpacityIntensity);
		}

		/**
		 * Orange.
		 */

		if (profile4D.intuited < profile4D.sensed && profile4D.logical > profile4D.emotional) {
			$chart4DQuadrants.find('dt:not(.quadrant-directing) > [role=presentation]').css('opacity', 0);
			$chart4DQuadrants.find('.quadrant-directing > [role=presentation]').css('opacity', orangeOpacityIntensity);
		}
	}

	var _getChartCenter = function () {
		var w = $chart4DQuadrants.width();
		var h = $chart4DQuadrants.height();
		var p = $chart4DQuadrants.offset();

		return {
			top: h / 2 + p.top,
			left: w / 2 + p.left
		}
	}

	var _setPointCenter = function (point) {
		var w = $chart4DPoint.width();
		var h = $chart4DPoint.height();
		var xOffset = (profile4D.logical - profile4D.emotional) * w;
		var yOffset = (profile4D.sensed - profile4D.intuited) * h;

		$chart4DPoint.offset({
			top: (point.top + yOffset) - h / 2,
			left: (point.left + xOffset) - w / 2
		});
	}

	$(window).on('load', function (e) {
		_setPointCenter(_getChartCenter());
		_updateQuadrants();

		updateProfile4DReport();

		pull = setInterval(function () {
			updateProfile4DReport();
		}, 2000);
	});

	$(window).on('resize', function (e) {
		_setPointCenter(_getChartCenter());
	});
}

$(document).ready(function () {
	window.report = new ReportManager();
});