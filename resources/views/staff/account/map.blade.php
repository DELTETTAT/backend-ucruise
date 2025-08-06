@extends('layouts.vertical', ['title' => 'company map'])
<meta name="csrf-token" content="{{ csrf_token() }}" />
@section('css')
<style>
    #map {
        height: 600px;
    }

    .address-overlay {
        position: absolute;
        top: 10px;
        left: 10px;
        background-color: white;
        padding: 10px;
        border: 1px solid #ccc;
        z-index: 1000;
    }
    #confirmation-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .confirmation-box {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        text-align: center;
    }

    #pin {
        position: absolute;
        width: 40px;
        height: 40px;
        background-image: url('{{ asset("images/p2.png") }}');
        /* You can replace 'pin.png' with your pin image */
        background-size: contain;
        background-repeat: no-repeat;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        /* Ensure pin is above the map */
        cursor: pointer;
        /* Add cursor pointer for draggable effect */
    }
    
</style>
@endsection

@section('content')
<!-- Start Content-->
<div class="container-fluid">
    <div id="map"></div>
    <div id="pin"></div>
    <div id="pin-coordinates"></div>
    <div id="addressOverlay" class="address-overlay"></div>
    
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    function initMap() {
        <?php if (!empty($company_details->latitude) && !empty($company_details->longitude)) : ?>
            var mapCenter = {
                lat: <?php echo $company_details->latitude ?>,
                lng: <?php echo $company_details->longitude ?>
            };
            // Set center to company location
        <?php else : ?>
            var mapCenter = {
                lat: 30.7312,
                lng: 76.7182
            }; // Center of India
        <?php endif; ?>

        var mapStyles = [{
                "elementType": "geometry",
                "stylers": [{
                    "color": "#f5f5f5"
                }]
            },
            {
                "elementType": "labels.icon",
                "stylers": [{
                    "visibility": "off"
                }]
            },
            {
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#616161"
                }]
            },
            {
                "elementType": "labels.text.stroke",
                "stylers": [{
                    "color": "#f5f5f5"
                }]
            },
            {
                "featureType": "administrative.land_parcel",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#bdbdbd"
                }]
            },
            {
                "featureType": "poi",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#eeeeee"
                }]
            },
            {
                "featureType": "poi",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#757575"
                }]
            },
            {
                "featureType": "poi.park",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#e5e5e5"
                }]
            },
            {
                "featureType": "poi.park",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#9e9e9e"
                }]
            },
            {
                "featureType": "road",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#ffffff"
                }]
            },
            {
                "featureType": "road.arterial",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#757575"
                }]
            },
            {
                "featureType": "road.highway",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#dadada"
                }]
            },
            {
                "featureType": "road.highway",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#616161"
                }]
            },
            {
                "featureType": "road.local",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#9e9e9e"
                }]
            },
            {
                "featureType": "transit.line",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#e5e5e5"
                }]
            },
            {
                "featureType": "transit.station",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#eeeeee"
                }]
            },
            {
                "featureType": "water",
                "elementType": "geometry",
                "stylers": [{
                    "color": "#c9c9c9"
                }]
            },
            {
                "featureType": "water",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "color": "#9e9e9e"
                }]
            }
        ];



        var map = new google.maps.Map(document.getElementById('map'), {
            center: mapCenter,
            zoom: 14,
            styles: mapStyles
        });

        var marker;


        var pin = document.getElementById('pin');
        var infoWindow = new google.maps.InfoWindow();
        var addressOverlay = document.getElementById('addressOverlay');
        var pinCoordinates = document.getElementById('pin-coordinates');

        var initialPinLocation = map.getCenter();

        google.maps.event.addListener(map, 'dragend', function() {
            updatePinPosition(map.getCenter());
        });
       

        function updatePinPosition(location) {

            pinCoordinates.textContent = 'Latitude: ' + location.lat().toFixed(6) + ', Longitude: ' + location.lng().toFixed(6);

            // Get the address for the new location and display it
            geocodeLatLng(location);
        }

        function geocodeLatLng(location) {
            var geocoder = new google.maps.Geocoder();

            geocoder.geocode({
                'location': location
            }, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        var completeAddress = results[0].formatted_address;
                 
                        if (completeAddress) {
                            var confirmAddress=confirm('Are you sure to save address?\n\n'+ completeAddress);
                            if(confirmAddress){
                            saveLocationAndAddress(location, completeAddress);
                            }
                        }
                        console.log('Complete Address:', completeAddress);

                    } else {
                        console.error('No results found');
                    }
                } else {
                    console.error('Geocoder failed due to: ' + status);
                }
            });
        }



        function saveLocationAndAddress(location, address) {
            // AJAX request to save the new location and address on the server
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('update.location') }}",
                type: "post",
                data: {
                    latitude: location.lat,
                    longitude: location.lng,
                    address: address
                },
                success: function(response) {
                    console.log('Company location and address saved successfully:', response);

                    alert('Address updated successfully!');

                    infoWindow.close();
                    addressOverlay.style.display = 'none';
                },
                error: function(error) {
                    console.error('Error saving company location and address:', error);
                }
            });
        }
    }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAeRd6wrgj0Tu8Dwf3h5Hh4cZVnWk9E41c&callback=initMap"></script>
@endsection