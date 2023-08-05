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
        </div>
        <!-- users filter end -->
        <!-- list section start -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title">{{__('menus.Users')}}</h5>

            </div>

            <div class="card-datatable table-responsive pt-1 pr-1 pl-1">
                <table id="example" class="display user-list-table table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{__('page.Name')}}</th>
                        <th>{{__('page.Phone')}}</th>
                        <th>{{__('page.gender')}}</th>
                        <th>{{__('page.Address')}}</th>
                        <th>{{__('menus.created_at')}}</th>
                        <th>{{__('page.Actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td><a target="_blank" href="{{url('admin/users/view/'.$user->id)}}">{{$user->name}}</a></td>
                            <td>{{$user->phone}}</td>
                            <td>{{($user->gender!=0)?($user->gender==1)?__('page.male'):__('page.female'):''}}</td>
                            <td>{{$user->address}}</td>
                            <td>{{$user->created_at}}</td>
                            <td>

                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-toggle="dropdown">
                                        <i data-feather="more-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">

                                        @if(auth()->user()->can('edit_user') || $isAdmin)
                                        <a class="dropdown-item" href="{{url('admin/users/edit/'.$user->id)}}">
                                            <i data-feather="edit-2" class="mr-50"></i>
                                            <span>{{__('page.Edit')}}</span>
                                        </a>
                                        @endif
                                        @if(auth()->user()->can('change_password_user') || $isAdmin)
                                        <a class="dropdown-item " href="{{url('admin/users/change_password/'.$user->id)}}">
                                            <i data-feather="eye-off" class="mr-50"></i>
                                            <span>{{__('menus.change_password')}}</span>
                                        </a>
                                        @endif
                                        @if(auth()->user()->can('add_note_user') || $isAdmin)
                                        <a class="dropdown-item add_note" href="#" data-toggle="modal" data-target="#inlineForm" data-id="{{$user->id}}">
                                            <i data-feather='clipboard'></i>
                                            <span>{{__('label.add_note')}}</span>
                                        </a>
                                        @endif
                                        @if(auth()->user()->can('delete_user') || $isAdmin)
                                        <a class="dropdown-item confirm-text" href="{{url('admin/users/delete/'.$user->id)}}">
                                            <i data-feather="trash" class="mr-50"></i>
                                            <span>{{__('page.Delete')}}</span>
                                        </a>
                                        @endif
                                        @if( $isAdmin)
                                        <a class="dropdown-item confirm-text" href="{{url('admin/users/final_delete/'.$user->id)}}">
                                            <i data-feather="x-circle" class="mr-50"></i>
                                            <span>حذف نهائي</span>
                                        </a>
                                        @endif

                                    </div>
                                </div>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
{{--                {{ $users->links() }}--}}
            </div>

            <!-- Modal to add new user starts-->
            <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
                <div class="modal-dialog">
                    <form class="add-new-user modal-content pt-0">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                        <div class="modal-header mb-1">
                            <h5 class="modal-title" id="exampleModalLabel">New User</h5>
                        </div>
                        <div class="modal-body flex-grow-1">
                            <div class="form-group">
                                <label class="form-label" for="basic-icon-default-uname">Username</label>
                                <input type="text" id="basic-icon-default-uname" class="form-control dt-uname" placeholder="Web Developer" aria-label="jdoe1" aria-describedby="basic-icon-default-uname2" name="user-name" />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="basic-icon-default-email">Email</label>
                                <input type="text" id="basic-icon-default-email" class="form-control dt-email" placeholder="john.doe@example.com" aria-label="john.doe@example.com" aria-describedby="basic-icon-default-email2" name="user-email" />
                                <small class="form-text text-muted"> You can use letters, numbers & periods </small>
                            </div>
                            <div class="form-group mb-2">
                                <label class="form-label" for="user-plan">Select Plan</label>
                                <select id="user-plan" class="form-control">
                                    <option value="basic">Basic</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mr-1 data-submit">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal to add new user Ends-->
        </div>

<!--            --><?php //echo $users->render(); ?>

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
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    <script>
        $(document).ready(function() {
            url = "{{asset('admin/ar.json')}}";
            $('#example').DataTable({
                language: {
                    url: url,
                },
                dom: 'Bfrtip',
                buttons: [
                    'pageLength',
                    'copyHtml5',
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    'csvHtml5',
                    'pdfHtml5',
                    'print',
                    'colvis',

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
    </script>
    <script>
        $( "#add_car" ).click(function() {
            //alert( "Handler for .click() called." );
            $('#modals-slide-in').modal('toggle');
            $('#modals-slide-in').modal('show');
        });

    </script>
    <script>
        $(document).ready(function () {
            $('.add_note').on('click', function(e) {

               // var id = $(this).data("id");
                $('#user_id').val($(this).data("id"));

            });
        });
    </script>
@endpush
<div class="form-modal-ex">

    <!-- Modal -->
    <div
        class="modal fade text-left"
        id="inlineForm"
        tabindex="-1"
        role="dialog"
        aria-labelledby="myModalLabel33"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33">{{__('label.add_note')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{url('admin/users/add_note')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <label>{{__('label.note')}}</label>
                        <div class="form-group">
                            <input type="text" placeholder="" class="form-control" name="note" required>
                        </div>
                        <input name="user_id" id="user_id" type="hidden">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{__('label.save')}}</button>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{__('page.Cancel')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


