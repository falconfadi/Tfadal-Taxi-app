@extends('layouts/admin')
@section('content')

        <div class="content-body">
            <section class="app-user-view">

                @if(Session::has('alert-success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div class="alert-body">
                            {!! session('alert-success') !!}
                        </div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
            @endif
  <!-- User Card & Plan Starts -->
  <div class="row">
    <!-- User Card starts-->
    <div class="col-xl-12 col-lg-8 col-md-7">
      <div class="card user-card">
        <div class="card-body">
          <div class="row">

            <div class="col-xl-6 col-lg-12 mt-2 mt-xl-0">
              <div class="user-info-wrapper">
                <div class="d-flex flex-wrap">
                  <div class="user-info-title">
                    <i data-feather="user" class="mr-1"></i>
                    <span class="card-text user-info-title font-weight-bold mb-0">{{__('page.Driver')}}</span>
                  </div>
                  <p class="card-text mb-0">{{($trip->driver)?$trip->driver->name:''}}</p>
                </div>
                  <div class="d-flex flex-wrap">
                      <div class="user-info-title">
                          <i data-feather="user" class="mr-1"></i>
                          <span class="card-text user-info-title font-weight-bold mb-0">{{__('page.User')}}</span>
                      </div>
                      <p class="card-text mb-0">{{$trip->user->name}}</p>
                  </div>

                <div class="d-flex flex-wrap my-50">
                  <div class="user-info-title">
                    <i data-feather="check" class="mr-1"></i>
                    <span class="card-text user-info-title font-weight-bold mb-0">{{__('page.Status')}}</span>
                  </div>
                  <p class="card-text mb-0">{{$status[$trip->status]}}</p>
                </div>
                  <div class="d-flex flex-wrap my-50">
                      <div class="user-info-title">
                          <i data-feather="check" class="mr-1"></i>
                          <span class="card-text user-info-title font-weight-bold mb-0">{{__('page.Car_Type')}}</span>
                      </div>
                      <p class="card-text mb-0">{{''}}</p>
                  </div>
                  <div class="d-flex flex-wrap my-50">
                      <div class="user-info-title">
                          <i data-feather="check" class="mr-1"></i>
                          <span class="card-text user-info-title font-weight-bold mb-0">جنس العميل \ جنس الكابتن</span>
                      </div>
                      <p class="card-text mb-0">{{$trip->carType->name}}</p>
                  </div>

                  <div class="d-flex flex-wrap">
                      <div class="user-info-title">
                          <i data-feather='type' class="mr-1"></i>
                          <span class="card-text user-info-title font-weight-bold mb-0">{{__('page.Type')}}</span>
                      </div>
                      <p class="card-text mb-0">
                          @if($trip->is_scheduled==1)
                              {{__('label.scheduled')}}
                          @elseif($trip->is_multiple==1)
                              {{__('label.multi')}}
                          @else
                              {{__('label.normal')}}
                          @endif
                      </p>
                  </div>

              </div>
            </div>

              <div class="col-xl-6 col-lg-12 mt-2 mt-xl-0">
                  <div class="user-info-wrapper">

                      <div class="d-flex flex-wrap">
                          <div class="user-info-title">
                              <i data-feather='dollar-sign'  class="mr-1"></i>
                              <span class="card-text user-info-title font-weight-bold mb-0">{{__('label.expected_price')}}</span>
                          </div>
                          <p class="card-text mb-0">{{$expectedPrice}}</p>
                      </div>

                      <div class="d-flex flex-wrap my-50">
                          <div class="user-info-title">
                              <i data-feather='dollar-sign'  class="mr-1"></i>
                              <span class="card-text user-info-title font-weight-bold mb-0">{{__('label.price')}}</span>
                          </div>
                          <p class="card-text mb-0">{{$price}}</p>
                      </div>
                      <div class="d-flex flex-wrap my-50">
                          <div class="user-info-title">
                              <i data-feather='clock' class="mr-1"></i>
                              <span class="card-text user-info-title font-weight-bold mb-0">{{__('label.time_to_arrive_driver')}}</span>
                          </div>
                          <p class="card-text mb-0">{{$timeToArriveDriver.' د '}}</p>
                      </div>
                      <div class="d-flex flex-wrap my-50">
                          <div class="user-info-title">
                              <i data-feather='clock' class="mr-1"></i>
                              <span class="card-text user-info-title font-weight-bold mb-0">{{__('label.captain_wait_for_user')}}</span>
                          </div>
                          <p class="card-text mb-0"> {{$timeToArriveCustomer.' د '}}</p>
                      </div>
                      <div class="d-flex flex-wrap my-50">
                          <div class="user-info-title">
                              <i data-feather='clock' class="mr-1"></i>
                              <span class="card-text user-info-title font-weight-bold mb-0">{{__('label.trip_duration')}}</span>
                          </div>
                          <p class="card-text mb-0"> {{$timeToArriveCustomer.' د '}}</p>
                      </div>

                  </div>
              </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /User Card Ends-->

    <!-- Plan Card starts-->

    <!-- /Plan CardEnds -->
  </div>
  <!-- User Card & Plan Ends -->


    <div class="row">
        <!-- User Card starts-->
        <div class="col-xl-12 col-lg-8 col-md-7">
            <div class="card user-card">
                <div class="card-body">
                    <div class="row" style="height: 500px">
                        <style>
                            #map2 {
                                height: 100%;
                                width: 100%;
                                margin: 0px;
                                padding: 0px
                            }
                        </style>

                        <script src="https://maps.googleapis.com/maps/api/js?key=<?=$key?>&callback=initAutocomplete"></script>
                        <script>
                            var geocoder;
                            var map;
                            var directionsDisplay;
                            var directionsService = new google.maps.DirectionsService();

                                var locations = <?php echo json_encode($driverCoordinatesArray); ?>;
                            function initialize() {
                                directionsDisplay = new google.maps.DirectionsRenderer();


                                var map = new google.maps.Map(document.getElementById('map2'), {
                                    zoom: 10,
                                    center: new google.maps.LatLng(<?=$centerLat?>, <?=$centerLon?>),
                                    mapTypeId: google.maps.MapTypeId.ROADMAP
                                });
                                directionsDisplay.setMap(map);
                                var infowindow = new google.maps.InfoWindow();

                                var marker, i;
                                var request = {
                                    travelMode: google.maps.TravelMode.DRIVING
                                };
                                for (i = 0; i < locations.length; i++) {
                                    marker = new google.maps.Marker({
                                        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                                    });

                                    google.maps.event.addListener(marker, 'click', (function(marker, i) {
                                        return function() {
                                            infowindow.setContent(locations[i][0]);
                                            infowindow.open(map, marker);
                                        }
                                    })(marker, i));

                                    if (i == 0) request.origin = marker.getPosition();
                                    else if (i == locations.length - 1) request.destination = marker.getPosition();
                                    else {
                                        if (!request.waypoints) request.waypoints = [];
                                        request.waypoints.push({
                                            location: marker.getPosition(),
                                            stopover: true
                                        });
                                    }
                                }
                                directionsService.route(request, function(result, status) {
                                    if (status == google.maps.DirectionsStatus.OK) {
                                        directionsDisplay.setDirections(result);
                                    }
                                });
                            }
                            // google.maps.event.addDomListener(window, "load", initialize);
                            window.addEventListener( "load", initialize);
                            //google.maps.event.addDomListener(window, "load", initialize);
                        </script>

                        <div class="col-xl-8 col-lg-6 mt-2 mt-xl-0">
                         @if($activeMap)   <div id="map2" style="position: static"></div> @endif
                        </div>
                        <div class="col-xl-4 col-lg-6 mt-2 mt-xl-0">
                            <div class="card">
                                <div class="card-header border-bottom"><!--href="{{url('admin/alert_driver/'.$trip->id)}}"-->
                                    <a class="btn btn-adn alert-button"  data-toggle="modal" data-target="#new-folder-modal"  data-value="{{$trip->id}}">تحذير الكابتن</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /User Card Ends-->
    </div>



  <!-- User Timeline & Permissions Starts -->
  <div class="row">
    <!-- information starts -->
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title mb-2">{{__('page.Trip_Timeline')}}</h4>
        </div>
        <div class="card-body">
          <ul class="timeline">
            <li class="timeline-item">
              <span class="timeline-point timeline-point-indicator"></span>
              <div class="timeline-event">
                <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                  <h6>{{__('page.Trip_Started_By_User')}}</h6>
                  <span class="timeline-event-time"></span>
                </div>
                <p>{{$trip->trip_date}}</p>
              </div>
            </li>
              <li class="timeline-item">
                  <span class="timeline-point timeline-point-info timeline-point-indicator"></span>
                  <div class="timeline-event">
                      <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                          <h6>{{__('label.arrive_to_customer_time')}}</h6>
                          <span class="timeline-event-time"></span>
                      </div>
                      <p class="mb-0">{{$trip->arrive_to_customer_time}}</p>
                  </div>
              </li>
            <li class="timeline-item">
              <span class="timeline-point timeline-point-warning timeline-point-indicator"></span>
              <div class="timeline-event">
                <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                  <h6>{{__('page.Trip_Started')}}</h6>
                  <span class="timeline-event-time"></span>
                </div>
                <p>{{$trip->start_date}}</p>
                <div class="media align-items-center">
                </div>
              </div>
            </li>

            <li class="timeline-item">
              <span class="timeline-point timeline-point-success timeline-point-indicator"></span>
              <div class="timeline-event">
                <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                  <h6>{{__('page.Trip_Ended')}}</h6>
                  <span class="timeline-event-time"></span>
                </div>
                <p class="mb-0">{{$trip->end_date}}</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <!-- information Ends -->
  </div>
  <!-- User Timeline & Permissions Ends -->
<div class="row" id="basic-table">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{__('label.trip_rate')}}</h4>
            </div>
            <div class="card-body">
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>{{__('label.trip_rate_by_user')}}</th>
                        <th>التصنيف</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <span class="font-weight-bold">{{($rate)?$rate->comment:''}}</span>
                        </td>
                        <td>
                            @if($rate && $rate->stars !=0)
                                @for($i=0;$i<$rate->stars;$i++)
                                    <i data-feather='star' style="color: black"></i>
                                @endfor
                            @endif
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</section>
        </div>

        <!-- alert driver-->
        <div class="modal fade" id="new-folder-modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><b>إرسال تحذير</b></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{url('admin/alert_driver/')}}" method="post">
                        @csrf
                        <div class="modal-body">
                            <label>النص</label>
                            <input type="text" class="form-control" name="text" placeholder="" required />
                            <input type="hidden" name="trip_id" id="trip_id" >
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary mr-1" >{{__('label.send')}}</button>
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{__('page.Cancel')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection
@push('view-page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/app-assets/css/pages/app-user.min.css')}}">
@endpush
@push('datatablefooter')

    <script>
        //get the trip id to modal
        $( ".alert-button" ).click(function() {
            $('#trip_id').val(this.getAttribute('data-value'));
        });
    </script>
@endpush
