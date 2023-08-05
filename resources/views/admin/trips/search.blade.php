@extends('layouts/admin')

@push('datatableheader')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endpush
@section('content')
    @php
        $status = array('0'=>__('menus.Pending'),
                '1'=>__('menus.Approved'),
                '2'=>__('menus.Arrived_to_customer'),
                '3'=>__('menus.In_the_way'),
                '4'=>__('menus.Arrived_to_destination_location'),
                '5'=>__('menus.Cancelled'),
                '6'=>__('menus.Scheduled_Trip'));
    @endphp
    <div class="content-header row">
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0">{{$title}}</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body"><!-- Basic Inputs start -->
        <section id="basic-input">
            <form method="get" id="search-form" >
                @csrf
            <div class="row">
                <div class="col-md-12">

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{$title}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label class="form-label" for="driver_id">{{__('page.Driver')}}</label>
                                        <select class="select2 form-control form-control-lg" name="driver_id"  id="driver_id">
                                            <option value="0">الكل</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{$driver->id}}">{{$driver->name."-".$driver->phone}}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label class="form-label" for="user_id">{{__('page.User')}}</label>
                                        <select class="select2 form-control form-control-lg" name="user_id" id="user_id">
                                            <option value="0">الكل</option>
                                            @foreach($users as $user)
                                                <option value="{{$user->id}}">{{$user->name."-".$user->phone}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label class="form-label" for="name">{{__('page.Type')}}</label>
                                        <select id="type" name="type" class="form-control">
                                            <option value="0">الكل</option>
                                            <option value="1">{{__('label.normal')}}</option>
                                            <option value="2">{{__('label.multi')}}</option>
                                            <option value="3">{{__('label.scheduled')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label class="form-label" for="date">{{__('page.Date')}}</label>
                                        <input type="date" id="date" class="form-control dt-uname"   aria-label="jdoe1" aria-describedby="basic-icon-default-uname2" name="date" >
                                    </div>
                                </div>

                                <input type="hidden" name="id"  >
                                <div class="col-sm-9 ">
                                    <button type="submit" class="btn btn-primary mr-1 mb-1">{{__('menus.Search')}}</button>
                                    <a type="button" class="btn btn-info mr-1 mb-1 " href="{{url('admin/home') }}">{{__('menus.back')}} </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-datatable table-responsive pt-1 pr-1 pl-1">
                            <table id="example" class="display user-list-table table">
                                <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{__('page.User')}}</th>
                                    <th>{{__('page.Driver')}}</th>
                                    <th>{{__('page.Start_Date')}}</th>
                                    <th>{{__('page.Status')}}</th>
                                    <th>{{__('page.Actions')}}</th>
                                </tr>
                                </thead>
                                <tbody id="tbody">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            </form>
        </section>
        <!-- Basic Inputs end -->

    </div>
@endsection
@push('select2')
    <!-- BEGIN: Page Vendor JS-->
    <script src="{{ asset('admin/app-assets/vendors/js/forms/select/select2.full.min.js')}}"></script>
    <!-- END: Page Vendor JS-->
    <!-- BEGIN: Page JS-->
    <script src="{{ asset('admin/app-assets/js/scripts/forms/form-select2.min.js')}}"></script>
    <!-- END: Page JS-->
@endpush

@push('datatablefooter')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>
    <script>
        let table;
        function  datatable(table) {

                url = "{{asset('admin/ar.json')}}";
                table = $('#example').DataTable({
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

        }
        function  destroytable(){
            $('#example').DataTable().destroy();
        }

            $('#search-form').on('submit',function(e) {
                e.preventDefault();
                destroytable();
                var driver_id = $('#driver_id').val();
                var user_id = $('#user_id').val();
                var type = $('#type').val();
                var date = $('#date').val();
                $.ajax({
                    url:"{{ route('trip.search') }}",
                    type:"GET",
                    data:{'driver_id':driver_id,'user_id':user_id,'type':type,'date':date},
                    success:function (data) {
                        //$('#tbody').empty();
                        //table.clear();
                        //table.fnReloadAjax();
                        //table.draw();

                        $('#tbody').html(data);
                        //table.destroy();
                        datatable();
                        //table.reload();

                    }
                })
                // .always(function() {
                //     table.ajax.reload();
                // });
            });




        // $("#search-form").submit(function(e) {
        //     table.ajax.reload();
        // });

</script>
@endpush
