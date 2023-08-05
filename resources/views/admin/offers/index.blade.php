@extends('layouts/admin')
@push('datatableheader')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css">
@endpush
@section('content')
<div class="content-header row">
</div>
<div class="content-body">
    <!-- users list start -->
    <section class="app-user-list">
        <!-- users filter start -->
        <div class="card">

            <div class="d-flex justify-content-between align-items-center mx-50 row pt-0 pb-2">
                <div class="col-md-4 user_role"></div>
                <div class="col-md-4 user_plan"></div>
                <div class="col-md-4 user_status"></div>
            </div>
        </div>
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
        <!-- users filter end -->
        <!-- list section start -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title">{{$title}}</h5>
                @if(auth()->user()->can('add_offer') || $isAdmin)
                <button class="btn btn-primary waves-effect waves-float waves-light" id="add_car">{{__('page.Add_New')}}</button>
                @endif
            </div>

            <div class="card-datatable table-responsive pt-1 pr-1 pl-1">
                <table id="example" class="display user-list-table table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{__('page.Details')}}</th>
                        <th>{{__('page.Start_time')}}</th>
                        <th>{{__('page.End_time')}}</th>
                        <th>{{__('page.Status')}}</th>
                        <th>{{__('label.Code')}}</th>

                        <th>{{__('page.Actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($offers as $offer)
                        <tr>
                            <td>{{$offer->details}}</td>
                            <td>{{$offer->start_time}}</td>
                            <td>{{$offer->end_time}}</td>
                            <td>
                                @if($offer->status==0)
                                    {{'Active'}}
                                @elseif($offer->status==1)
                                    {{'Pending'}}
                                @elseif($offer->status==2)
                                    {{'Closed'}}
                                @elseif($offer->status==3)
                                    {{'Cancelled'}}
                                @endif
                            </td>
                            <td>{{$offer->code}}</td>

                            <td>

                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-toggle="dropdown">
                                        <i data-feather="more-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{url('admin/offers/edit/'.$offer->id)}}">
                                            <i data-feather="edit-2" class="mr-50"></i>
                                            <span>{{__('page.Edit')}}</span>
                                        </a>
                                        <a class="dropdown-item confirm-text" href="{{url('admin/offers/delete/'.$offer->id)}}">
                                            <i data-feather="trash" class="mr-50"></i>
                                            <span>{{__('page.Delete')}}</span>
                                        </a>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Modal to add new user starts-->



            <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
                <div class="modal-dialog">
                    <form class="add-new-user modal-content pt-0" name="add_offer" action="{{url('admin/offers/store')}}" method="post">
                        @csrf
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                        <div class="modal-header mb-1">
                            <h5 class="modal-title" id="exampleModalLabel">{{__('page.New_Offer')}}</h5>
                        </div>
                        <div class="modal-body flex-grow-1">
                            <div class="form-group">
                                <label class="form-label" for="details">{{__('page.Details')}}</label>
                                <input type="text" class="form-control dt-full-name" name="details" id="details"   aria-label="John Doe" aria-describedby="basic-icon-default-fullname2" />
                                <span id="details-error" class="error" style="display:none">This field is required.</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="start_time">{{__('page.Start_time')}}</label>
                                <input type="date" id="start_time" class="form-control dt-uname" placeholder="01-01-2022" aria-label="jdoe1" aria-describedby="basic-icon-default-uname2" name="start_time" />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="end_time">{{__('page.End_time')}}</label>
                                <input type="date" id="end_time" name="end_time" class="form-control " placeholder="01-01-2022" aria-label="john.doe@example.com" aria-describedby="basic-icon-default-email2"  />
                                <small class="form-text text-muted">  </small>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="discount">{{__('label.Discount')}}</label>
                                <input type="number" id="discount" name="discount" class="form-control "   aria-describedby="basic-icon-default-email2"  />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="amount">{{__('label.balance')}}</label>
                                <input type="number" id="amount" name="amount" class="form-control "   aria-describedby="basic-icon-default-email2"  />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="code">{{__('label.Code')}}</label>
                                <input type="text" id="code" name="code" class="form-control"   aria-describedby="basic-icon-default-email2"  />
                                <small class="form-text text-muted">5 digits only  </small>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="num_of_trips">{{__('label.num_of_trips')}}</label>
                                <input type="number" id="num_of_trips" min="0" name="num_of_trips" class="form-control"   aria-describedby="basic-icon-default-email2"  />
                            </div>
                            <div class="form-group">
                                <label class="d-block">{{__('label.is_new_client')}}</label>
                                <div class="custom-control custom-radio my-50">
                                    <input    type="radio"   id="validationRadio3" name="is_new_client"   class="custom-control-input"  value="1"    />
                                    <label class="custom-control-label" for="validationRadio3">{{__('page.Yes')}}</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input   type="radio"  id="validationRadio4"  name="is_new_client" class="custom-control-input" value="0" checked     />
                                    <label class="custom-control-label" for="validationRadio4">{{__('page.No')}}</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="basicSelect">{{__('label.Status')}}</label>
                                <select class="form-control" id="basicSelect" name="status">
                                    <option value="0">Active</option>
                                    <option value="1">Pending</option>
                                    <option value="2">Closed</option>
                                    <option value="3">Cancelled</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="switch-label" for="price_open">{{__('label.all')}}</label><br>
                                <div class="custom-control custom-switch custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" id="customSwitch1" name="all" value="1" />
                                    <label class="custom-control-label" for="customSwitch1"></label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{__('label.choose_users')}}</label>
                                <select class="select2 form-control" multiple name="users[]" id="users">
                                    <optgroup label="{{__('menus.Users')}}">
                                        @foreach($users as $user)
                                            @if( in_array($user->id,$bestUsersIds))
                                                <option value="{{$user->id}}" selected>{{$user->name}}</option>
                                            @else
                                                <option value="{{$user->id}}" >{{$user->name}}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                </select>

                            </div>


                            <button type="submit" class="btn btn-primary mr-1 data-submit">{{__('page.Submit')}}</button>
                            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">{{__('page.Cancel')}}</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal to add new user Ends-->
        </div>
        <!-- list section end -->
    </section>
    <!-- users list ends -->

</div>
@endsection
@push('datatablefooter')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            url = "{{asset('admin/ar.json')}}";
            $('#example').DataTable({
                language: {
                    url: url,
                },
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5',
                    'print'
                ]
            });
        } );
    </script>

    <script>
        $( "#add_car" ).click(function() {
            //alert( "Handler for .click() called." );
            $('#modals-slide-in').modal('toggle');
            $('#modals-slide-in').modal('show');
        });

    //disable choose people
    $('#customSwitch1').change(function () {
    W = $(this).val();
    if($(this).is(":checked")){
        $('#users').prop('disabled', 'disabled');
        $('#validationRadio3').prop('disabled', 'disabled');
        $('#validationRadio4').prop('disabled', 'disabled');
    }else{
        $('#users').prop('disabled', false);
        $('#validationRadio3').prop('disabled', false);
        $('#validationRadio4').prop('disabled', false);
    }
    });
    </script>


@endpush
@push('sweetalert')
{{--    <script src="{{ asset('admin/app-assets/vendors/js/extensions/sweetalert2.all.min.js')}}"></script>--}}
{{--    <script src="{{ asset('admin/app-assets/vendors/js/extensions/polyfill.min.js')}}"></script>--}}
@endpush


@push('select2')
    <!-- BEGIN: Page Vendor JS-->
    <script src="{{ asset('admin/app-assets/vendors/js/forms/select/select2.full.min.js')}}"></script>
    <!-- END: Page Vendor JS-->
    <!-- BEGIN: Page JS-->
    <script src="{{ asset('admin/app-assets/js/scripts/forms/form-select2.min.js')}}"></script>
    <!-- END: Page JS-->
@endpush
