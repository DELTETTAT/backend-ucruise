@extends('layouts.vertical', ['title' => 'Reschedule Requests'])

@section('css')
<style>
    #pickUpMap {
        height: 500px;
        width: 100%;
    }

    #dropMap {
        height: 500px;
        width: 100%;
    }

    .active {
        background-color: #E3F2DB !important;
        color: #000;
        height: 30px;
        border-left: 2px solid #7671E0;
        padding: 5px;
    }
</style>
@endsection

@section('content')
<?php error_reporting(0); ?>
<!-- Start Content-->
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-3 nav_list">
            <div class="card">
                <div class="card-body">
                    <h3>Similar Routes</h3>

                    @if(isset($filteredSchedules))

                    @foreach($filteredSchedules as $index => $filteredSchedule)

                    <div class="mb-3">
                        <span class="breadcrumb-item {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                            {{$filteredSchedule->city}}
                        </span>

                        @if(isset($filteredSchedule->vacant_seats))
                        <span class="d-flex align-items-center">
                            <i class="mdi mdi-car-seat"></i> {{$filteredSchedule->vacant_seats}} vacant seats
                        </span>
                        @endif
                        @if($filteredSchedule->shift_type_id==1)
                        <span class="badge bg-soft-success text-success">
                            Pick
                        </span>
                        @elseif($filteredSchedule->shift_type_id==2)
                        <span class="badge bg-soft-danger text-danger">
                            Pick and Drop
                        </span>

                        @else
                        <span class="badge bg-soft-primary text-primary">
                            Drop
                        </span>
                        @endif

                    </div>

                    @endforeach

                    @endif
                </div> <!-- end card body -->
                <!-- end card -->
            </div>
            @if(isset($filteredSchedules) && count($filteredSchedules) > 0)
            @if(isset($old_schedules) && count($old_schedules) > 1)
            <div class="card-footer">
                <button class="btn btn-success" onclick="confirmSaveRoute()">Save</button>
            </div>
            @else
            <div class="card-footer">
                <button class="btn btn-success" onclick="saveRoute()">Save</button>
            </div>
            @endif
            @endif<!-- end card -->
        </div><!-- end col-->

        <div class="col-9">
            @if(isset($filteredSchedules) && count($filteredSchedules) > 0)

            <div id="pickUpMap"></div>
            <div id="dropMap"></div>

            <button id="toggleButton" onclick="toggleMaps()" style="display: none;">Toggle map</button>
            @endif
        </div>
        <!-- end row-->
    </div> <!-- container -->
    @endsection

    @section('script')
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAeRd6wrgj0Tu8Dwf3h5Hh4cZVnWk9E41c&callback=initMap"></script>
    <script>
        let pickUpMap, dropMap;
        let directionsServicePickUp, directionsServiceDrop;
        let directionsRendererPickUp, directionsRendererDrop;

        document.addEventListener("DOMContentLoaded", function() {
            const lis = document.querySelectorAll('.breadcrumb-item');

            lis.forEach(li => {
                li.addEventListener('click', function() {
                    lis.forEach(otherLi => otherLi.classList.remove('active'));
                    this.classList.add('active');
                    const index = this.getAttribute('data-index');
                    handleLiClick(index);
                    selectedSchedule = getScheduleByIndex(index);
                });
            });

            lis[0].click();

        });

        function getScheduleByIndex(index) {

            return <?php echo json_encode($filteredSchedules); ?>[index];
        }

        function confirmSaveRoute() {
            // Display a confirmation dialog
             
                // Display a confirmation dialog
                if (confirm("Employee has multiple shifts. Do you want to remove the employee from all shifts and assign them to the new shift?")) {
                    // If the user confirms, execute the saveRoute function
                    saveRoute();
                } else {
                    // If the user cancels, do nothing
                    console.log('Saving route canceled by the user.');
                }
         
        }

        function saveRoute() {
            if (selectedSchedule) {

                var scheduleId = selectedSchedule.id;
                var rescheduleId = <?php echo json_encode($reschedule->id); ?>;
                var redirectUrl = `http://127.0.0.1:8000/users/acceptReschedule/${scheduleId}/${rescheduleId}`;

                window.location.href = redirectUrl;
                console.log('Selected Schedule ID:', scheduleId);
                console.log('Selected rescheduleId ID:', rescheduleId);

            } else {
                console.error('No schedule selected.');
            }
        }

        function toggleMaps() {
            const pickUpMapElement = document.getElementById("pickUpMap");
            const dropMapElement = document.getElementById("dropMap");

            if (pickUpMapElement.style.display === 'block') {
                pickUpMapElement.style.display = 'none';
                dropMapElement.style.display = 'block';

            } else {
                pickUpMapElement.style.display = 'block';
                dropMapElement.style.display = 'none';


            }
        }
        async function initMap() {
            const {
                Map
            } = await google.maps.importLibrary("maps");
            const mapStyles = [{
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

            pickUpMap = new google.maps.Map(document.getElementById("pickUpMap"), {
                center: {
                    lat: <?php echo $company_details['latitude'] ?>,
                    lng: <?php echo $company_details['longitude'] ?>
                },
                zoom: 13,

                styles: mapStyles,
            });

            dropMap = new google.maps.Map(document.getElementById("dropMap"), {
                center: {
                    lat: <?php echo $company_details['latitude'] ?>,
                    lng: <?php echo $company_details['longitude'] ?>
                },
                zoom: 13,
                styles: mapStyles,
            });

            directionsServicePickUp = new google.maps.DirectionsService();
            directionsServiceDrop = new google.maps.DirectionsService();

            directionsRendererPickUp = new google.maps.DirectionsRenderer({
                map: pickUpMap,
            });

            directionsRendererDrop = new google.maps.DirectionsRenderer({
                map: dropMap,
            });

            <?php if (count($filteredSchedules) > 0) : ?>
                var carers = <?php echo json_encode($filteredSchedules[0]->carers); ?>;
                const rescheduleLatLng = new google.maps.LatLng(<?php echo $reschedule->latitude ?>, <?php echo $reschedule->longitude ?>);

                if (carers.length > 0) {
                    const shiftTypeId = <?php echo $filteredSchedules[0]->shift_type_id ?>;

                    if (shiftTypeId === 1) {
                        createRoute(pickUpMap, directionsServicePickUp, directionsRendererPickUp, carers, rescheduleLatLng, shiftTypeId);
                        document.getElementById("pickUpMap").style.display = 'block';
                        document.getElementById("dropMap").style.display = 'none';
                        document.getElementById("toggleButton").style.display = 'none';

                    } else if (shiftTypeId === 3) {
                        createRoute(dropMap, directionsServiceDrop, directionsRendererDrop, carers, rescheduleLatLng, shiftTypeId);
                        document.getElementById("pickUpMap").style.display = 'none';
                        document.getElementById("dropMap").style.display = 'block';
                        document.getElementById("toggleButton").style.display = 'none';
                    } else if (shiftTypeId === 2) {

                        createRoute(pickUpMap, directionsServicePickUp, directionsRendererPickUp, carers, rescheduleLatLng, shiftTypeId);
                        createRoute(dropMap, directionsServiceDrop, directionsRendererDrop, carers, rescheduleLatLng, shiftTypeId);
                        document.getElementById("pickUpMap").style.display = 'block';
                        document.getElementById("dropMap").style.display = 'none';

                        document.getElementById("toggleButton").style.display = 'block';

                    }
                }
            <?php endif; ?>
        }

        function handleLiClick(index) {
            var schedule = <?php echo json_encode($filteredSchedules); ?>[index];
            var carers = schedule.carers;
            var rescheduleLatLng = new google.maps.LatLng(<?php echo $reschedule->latitude ?>, <?php echo $reschedule->longitude ?>);

            var pickUpMapElement = document.getElementById("pickUpMap");
            var dropMapElement = document.getElementById("dropMap");

            if (schedule.shift_type_id === 1) {
                createRoute(pickUpMap, directionsServicePickUp, directionsRendererPickUp, carers, rescheduleLatLng, schedule.shift_type_id);
                pickUpMapElement.style.display = 'block';
                dropMapElement.style.display = 'none';
                document.getElementById("toggleButton").style.display = 'none';
            } else if (schedule.shift_type_id === 3) {
                createRoute(dropMap, directionsServiceDrop, directionsRendererDrop, carers, rescheduleLatLng, schedule.shift_type_id);
                pickUpMapElement.style.display = 'none';
                dropMapElement.style.display = 'block';
                document.getElementById("toggleButton").style.display = 'none';
            } else if (schedule.shift_type_id === 2) {
                // For shift_type_id 2, consider 'pick' and 'drop' carers separately
                var pickCarers = carers.filter(carer => carer.shift_type === 'pick');
                var dropCarers = carers.filter(carer => carer.shift_type === 'drop');

                createRoute(pickUpMap, directionsServicePickUp, directionsRendererPickUp, pickCarers, rescheduleLatLng, schedule.shift_type_id);
                createRoute(dropMap, directionsServiceDrop, directionsRendererDrop, dropCarers, rescheduleLatLng, schedule.shift_type_id);


                document.getElementById("pickUpMap").style.display = 'block';
                document.getElementById("dropMap").style.display = 'none';

                // Add a button to toggle between pickup and drop maps
                document.getElementById("toggleButton").style.display = 'block';


            }
        }

        function createRoute(map, directionsService, directionsRenderer, carers, rescheduleLatLng, shiftTypeId) {
            const waypoints = carers.map(carer => ({
                location: new google.maps.LatLng(carer.user.latitude, carer.user.longitude),
                stopover: true,
            }));
            waypoints.push({
                location: rescheduleLatLng,
                stopover: true,
            });

            const companyLatLng = new google.maps.LatLng(
                <?php echo $company_details['latitude'] ?>,
                <?php echo $company_details['longitude'] ?>
            );

            let originLatLng, destinationLatLng;

            if (shiftTypeId === 3) {
                originLatLng = companyLatLng;
                destinationLatLng = waypoints[0].location;
            } else if (shiftTypeId === 1) {
                originLatLng = waypoints[0].location;
                destinationLatLng = companyLatLng;
            } else if (shiftTypeId === 2) {
                // For shift_type_id 2, update the logic for pickup and drop
                if (map === pickUpMap) {
                    destinationLatLng = companyLatLng;
                    originLatLng = waypoints[0].location;
                } else if (map === dropMap) {
                    originLatLng = companyLatLng;
                    destinationLatLng = waypoints[0].location;
                }
            }

            const request = {
                origin: originLatLng,
                destination: destinationLatLng,
                waypoints: waypoints.slice(1),
                travelMode: google.maps.TravelMode.DRIVING,
                optimizeWaypoints: true,
            };

            directionsService.route(request, function(result, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    directionsRenderer.setDirections(result);

                    // Set polyline color to dark grey
                    directionsRenderer.setOptions({
                        polylineOptions: {
                            strokeColor: '#404040', // Dark grey color
                            strokeWeight: 4, // Adjust the weight as needed
                        },
                    });
                    const rescheduleWaypointMarker = new google.maps.Marker({
                        position: rescheduleLatLng,
                        map: map,

                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png', // Blue marker image URL
                            //scaledSize: new google.maps.Size(20, 20),
                            //anchor: new google.maps.Point(5, 5),
                        },
                    });
                }
            });
        }
    </script>



    @endsection