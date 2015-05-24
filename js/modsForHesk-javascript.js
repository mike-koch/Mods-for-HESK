//-- Activate anything Mods for HESK needs, such as tooltips.
var loadJquery = function()
{
    //-- Activate tooltips
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });

    //-- Activate popovers
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        container: 'body'
    });

    //-- Activate HTML popovers
    $('[data-toggle="htmlpopover"]').popover({
        trigger: 'hover',
        container: 'body',
        html: 'true'
    });

    //-- Activate HTML on-click popovers
    $('[data-toggle="htmlpopover-onclick"]').popover({
        container: 'body',
        html: 'true'
    });

    //-- Activate jQuery's date picker
    $(function() {
        $('.datepicker').datepicker({
            todayBtn: "linked",
            clearBtn: true,
            autoclose: true,
            autoclose: true,
            todayHighlight: true,
            format: "yyyy-mm-dd"
        });
    });

    $('[data-toggle="iconpicker"]').iconpicker({
        iconset: ['fontawesome', 'octicon'],
        selectedClass: "btn-warning",
        labelNoIcon: $('#no-icon').text(),
        searchText: $('#search-icon').text(),
        labelFooter: $('#footer-icon').text()
    });
};

var setIcon = function(icon) {
    $('[data-toggle="iconpicker"]').iconpicker('setIcon', icon);
}

function selectAll(id) {
    $('#' + id + ' option').prop('selected', true);
}

function deselectAll(id) {
    $('#' + id + ' option').prop('selected', false);
}

function toggleRow(id) {
    if ($('#' + id).hasClass('danger'))
    {
        $('#' + id).removeClass('danger');
    } else
    {
        $('#' + id).addClass('danger');
    }
}

function toggleChildrenForm(show) {
    if (show) {
        $('#childrenForm').show();
        $('#addChildText').hide();
    } else {
        $('#childrenForm').hide();
        $('#addChildText').show();
    }
}

function toggleContainers(showIds, hideIds) {
    showIds.forEach(function (entry) {
        $('#' + entry).show();
    });
    hideIds.forEach(function (entry) {
        $('#' + entry).hide();
    });
}

function disableIfEmpty(sourceId, destinationId) {
    if ($('#' + sourceId).val().length > 0) {
        $('#' + destinationId).attr('disabled', false);
    } else {
        if ($('#' + destinationId).is(':checkbox')) {
            $('#' + destinationId).attr('checked', false);
        }
        $('#' + destinationId).attr('disabled', true);
    }
}

function changeText(id, checkedValue, uncheckedValue, object) {
    if (object.checked) {
        $('#'+id).text(checkedValue);
    } else {
        $('#'+id).text(uncheckedValue);
    }
}

function requestUserLocation(yourLocationText, unableToDetermineText) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            setLatLon(latitude, longitude);
            $('#console').hide();
            initializeMapForCustomer(latitude, longitude, yourLocationText);
        }, function(error) {
            $('#map').hide();
            $('#console').text(unableToDetermineText).show();
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    setLatLon('E-1','E-1');
                    break;
                case error.POSITION_UNAVAILABLE:
                    setLatLon('E-2','E-2');
                    break;
                case error.TIMEOUT:
                    setLatLon('E-3','E-3');
                    break;
                case error.UNKNOWN_ERROR:
                    setLatLon('E-4','E-4');
                    break;
            }
        });
    } else {
        setLatLon('E-5','E-5');
    }
}

function setLatLon(lat, lon) {
    $('#latitude').val(lat);
    $('#longitude').val(lon);
}

var marker;
var map;
function resetLatLon(lat, lon) {
    map.setView([lat, lon], 15);
    marker.setLatLng(L.latLng(lat, lon));
}

function closeAndReset(lat, lon) {
    $('#save-group').hide();
    $('#close-button').show();
    $('#friendly-location').show();
    $('#save-for-address').hide();
    resetLatLon(lat, lon);
}

function initializeMapForCustomer(latitude, longitude, yourLocationText) {
    map = L.map('map').setView([latitude, longitude], 15);
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    marker = L.marker([latitude, longitude], {draggable: true})
        .addTo(map)
        .bindPopup(yourLocationText);

    marker.on('dragend', function(event) {
        setLatLon(event.target.getLatLng().lat, event.target.getLatLng().lng);
    });
}

function initializeMapForStaff(latitude, longitude, usersLocationText) {
    map = L.map('map').setView([latitude, longitude], 15);
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    marker = L.marker([latitude, longitude], {draggable: true})
        .addTo(map)
        .bindPopup(usersLocationText);

    marker.on('dragend', function(event) {
        setLatLon(event.target.getLatLng().lat, event.target.getLatLng().lng);
        $('#save-group').show();
        $('#close-button').hide();
        $('#friendly-location').hide();
        $('#save-for-address').show();
    });

    $('#map-modal').on('shown.bs.modal', function(){
        setTimeout(function() {
            map.invalidateSize();
        }, 10);
    });
}

function getFriendlyLocation(latitude, longitude) {
    var URL = 'http://nominatim.openstreetmap.org/reverse?format=json&lat='+ latitude +'&lon='+ longitude +'&zoom=15&addressdetails=1';
    $.getJSON(URL, function(data) {
        $('#friendly-location').text(data.display_name);
    });
}

jQuery(document).ready(loadJquery);
