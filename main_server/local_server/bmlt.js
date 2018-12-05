var bmltbaseURL;
var recurseServiceBodies;

var bmltClientInit = function (host, recurse) {
    this.bmltbaseURL = host + "/client_interface/jsonp/?switcher=";
    this.recurseServiceBodies = recurse == null ? false : recurse;
};

var getDayOfWeek = function (dayint) {
    return ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"][dayint];
};

var getTodayDayOfWeek = function () {
    return (new Date()).getDay() + 1;
};

var militaryToStandard = function (value) {
    if (value !== null && value !== undefined) { //If value is passed in
        if (value.indexOf('AM') > -1 || value.indexOf('PM') > -1) { //If time is already in standard time then don't format.
            return value;
        } else {
            if (value.length === 8) { //If value is the expected length for military time then process to standard time.
                var valueconv = value.split(':'); // convert to array
                // fetch
                var hours = Number(valueconv[0]);

                // calculate
                var timeValue;
                if (hours > 0 && hours <= 12) { // If hour is less than or equal to 12 then convert to standard 12 hour format
                    timeValue= "" + hours;
                } else if (hours > 12) { //If hour is greater than 12 then convert to standard 12 hour format
                    timeValue= "" + (hours - 12);
                } else if (hours === 0) { //If hour is 0 then set to 12 for standard time 12 AM
                    timeValue= "12";
                }

                timeValue += ":" + valueconv[1];  // get minutes
                timeValue += (hours >= 12) ? " PM" : " AM";  // get AM/PM
                // show
                return timeValue;
            } else { //If value is not the expected length than just return the value as is
                return valueconv;
            }
        }
    }
};

var getMeetingsByCity = function (city, callback) {
    getJSON(bmltbaseURL + "GetSearchResults&meeting_key=location_municipality&meeting_key_value=" + city + "&callback=?", callback);
};

var getMeetingsByServiceBodyId = function (serviceBodyId, callback) {
    getJSON(bmltbaseURL + "GetSearchResults" + getServiceBodyIdQueryString(serviceBodyId) + "&callback=?", callback);
};

var getMeetingsByServiceBodyIdAndWeekdayId = function (serviceBodyId, weekdayId, callback) {
    getJSON(bmltbaseURL + "GetSearchResults" + getServiceBodyIdQueryString(serviceBodyId) + "&weekdays=" + weekdayId + "&callback=?", callback);
};

var getMeetingsByServiceBodyIdAndCity = function (serviceBodyId, city, callback) {
    getJSON(bmltbaseURL + "GetSearchResults" + getServiceBodyIdQueryString(serviceBodyId) + "&meeting_key=location_municipality&meeting_key_value=" + city + "&callback=?", callback);
};

var getFormats = function (callback) {
    getJSON(bmltbaseURL + "GetFormats&callback=?", callback);
};

var getUniqueValuesByServiceBody = function (serviceBodyId, field, callback) {
    getMeetingsByServiceBodyId(serviceBodyId, function (data) {
        var valuesArray = [];
        for (i = 0; i < data.length; i++) {
            valuesArray.push(data[i][field]);
        }

        callback(valuesArray.unique())
    });
};

var getServiceBodyIdQueryString = function (serviceBodyIds) {
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

var getJSON = function (url, callback) {
    var random = Math.floor(Math.random() * 999999);
    var callbackFunctionName = "cb_" + random;
    if (this.recurseServiceBodies) {
        url += "&recursive=1";
    }
    url = url.replace("callback=?", "callback=" + callbackFunctionName);

    window[callbackFunctionName] = function (data) {
        callback(data);
    };

    var scriptItem = document.createElement('script');
    scriptItem.setAttribute('src', url);
    document.body.appendChild(scriptItem);
};

Array.prototype.unique = function () {
    var o = {}, a = [];
    for (var i = 0; i < this.length; i++) {
        o[this[i]] = 1
        for (var e in o) {
            a.push(e)
            return a
        }
    }
};
