$(document).ready(function () {
	const USERS_API = '/api/v2/users';
	/**
	 * @return {string}
	 */
	const USER_DATA_API = function(uuid){
		return '/api/v2/' + uuid + '/stats';
	};

	am4core.useTheme(am4themes_animated);
	am4core.useTheme(am4themes_material);
	// am4core.useTheme(am4themes_dark);

	am4core.options.onlyShowOnViewport = false;
	am4core.options.queue = true;

	const chartColors = {
		theme: 'material',
		selectedBackgroundColor: '#3C5077',
		gridColor: '#999999',
		color: '#111111',
		scrollBarBackgroundColor: '#3D5E77',
		labelColor: '#000000',
		backgroundColor: '#2B3E50',
		ratioLineColor: '#196E1F',
		countLineColor: '#214DD1',
		handDrawn: false
	};

    const dataTableBody = $('#data-table-body');

	function getUsers(usersCallback) {
		$.ajax({
			url: USERS_API,
			data: {
				range: 14
			},
			context: document.body,
			method: 'GET'
		}).done(function (data) {
			if (data && data['users'] && data['users'] instanceof Array) {
				usersCallback(data['users']);
			}
		});
	}

	function getUserData(userId, userDataCallBack) {
        $.ajax({
            url: USER_DATA_API(userId),
            data: {},
            context: document.body,
            method: 'GET'
        }).done(function (data) {
            if (data && data['total'] && data['week'] && data['day'] && data['total']['count'] > 0) {
                userDataCallBack(data['total'], data['week'], data['day']);
            }
        });
    }

    function secondsToDurationStr(seconds){
		if(!seconds)
			return "-";

		seconds = Math.trunc(seconds);
		let minutes = ~~(seconds / 60);
		seconds %= seconds;

		if(minutes === 0){
			return seconds + "s"
		}

		let hours = ~~(minutes / 60);
		minutes %= 60;

		if(hours === 0) {
			return minutes + "m " + seconds + "s"
		}

		let days = ~~(hours / 24);
		hours %= 24;

		if(days === 0){
			return hours + "h " + minutes + "m " + seconds + "s"
		}

		return days + "d " + hours + "h " + minutes + "m " + seconds + "s"
	}

    getUsers(function(users){
        for (const userIndex in users) {
            if (users.hasOwnProperty(userIndex)) {
                const userObject = users[userIndex];
                getUserData(userObject['uuid'], function(totalData, weekData, dayData){
                    $('<tr>').append(
                        $('<th scope="row">').text(userObject['username']),
                        $('<td>').text(secondsToDurationStr(totalData['opened'])),
                        $('<td>').text(secondsToDurationStr(totalData['watched'])),
                        $('<td>').text(totalData['count']),
                        $('<td>').text(secondsToDurationStr(weekData['opened'])),
                        $('<td>').text(secondsToDurationStr(weekData['watched'])),
                        $('<td>').text(weekData['count']),
                        $('<td>').text(secondsToDurationStr(dayData['opened'])),
                        $('<td>').text(secondsToDurationStr(dayData['watched'])),
                        $('<td>').text(dayData['count'])
                    ).appendTo(dataTableBody);
                })
            }
        }
    });
});
