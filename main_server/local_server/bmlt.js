var baseURL;

var bmltClientInit = function(host) {
    this.baseURL = host + "/client_interface/jsonp/?switcher=GetSearchResults";
}

var getDayOfWeek = function(dayint) {
    return ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"][dayint];
};
var getTodayDayOfWeek = function() {
    return (new Date()).getDay() + 1;
};
var militaryToStandard = function(time) {
    if (time !== null && time !== undefined){ //If time is passed in
        if(time.indexOf('AM') > -1 || time.indexOf('PM') > -1){ //If time is already in standard time then don't format.
            return time;
        }
        else {
            if(time.length == 8){ //If time is the expected length for military time then process to standard time.
				time = time.split(':'); // convert to array
				// fetch
				var hours = Number(time[0]);
				var minutes = Number(time[1]);

				// calculate
				var timeValue;

				if (hours > 0 && hours <= 12)
				{
				timeValue= "" + hours;
				} else if (hours > 12)
				{
					timeValue= "" + (hours - 12);
				}
				else if (hours == 0)
				{
					timeValue= "12";
				}
 
				timeValue += (minutes < 10) ? ":0" + minutes : ":" + minutes;  // get minutes
				timeValue += (hours >= 12) ? " PM" : " AM";  // get AM/PM

				// show
				return timeValue;
				}
            else { //If value is not the expected length than just return the value as is
                return value;
            }
        }
    }
};

var getMeetingsByCity = function(city, callback) {
    getJSON(baseURL + "&meeting_key=location_municipality&meeting_key_value=" + city + "&callback=?", callback);
};

var getMeetingsByServiceBodyId = function(serviceBodyId, callback) {
    getJSON(baseURL + getServiceBodyIdQueryString(serviceBodyId) + "&callback=?", callback);
};

var getMeetingsByServiceBodyIdAndWeekdayId = function(serviceBodyId, weekdayId, callback) {
    getJSON(baseURL + getServiceBodyIdQueryString(serviceBodyId) + "&weekdays=" + weekdayId + "&callback=?", callback);
};

var getMeetingsByServiceBodyIdAndCity = function(serviceBodyId, city, callback) {
    getJSON(baseURL + getServiceBodyIdQueryString(serviceBodyId) + "&meeting_key=location_municipality&meeting_key_value=" + city + "&callback=?", callback);
};

var getServiceBodyIdQueryString = function(serviceBodyIds) {
    var serviceBodyIdString = "";
    if (Array.isArray(serviceBodyIds)) {
        for (var i = 0; i < serviceBodyIds.length; i++) {
            serviceBodyIdString += "&services[]=" + serviceBodyIds[i];
        }
    } else {
        serviceBodyIdString = "&services=" + serviceBodyIds;
    }

    return serviceBodyIdString;
};

var getJSON = function(url, callback) {
    var random = Math.floor(Math.random() * 999999);
    var callbackFunctionName = "cb_" + random
    url = url.replace("callback=?", "callback=" + callbackFunctionName);

    window[callbackFunctionName] = function(data) {
        callback(data);
    };

    var scriptItem = document.createElement('script');
    scriptItem.setAttribute('src', url);
    document.body.appendChild(scriptItem);
}
