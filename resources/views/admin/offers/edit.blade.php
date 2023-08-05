@extends('layouts/admin')

@section('content')

    <div class="content-header row">
        <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="col-12">
                    <h2 class="content-header-title float-left mb-0">{{__('page.Offers')}}</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="content-body"><!-- Basic Inputs start -->
        <section id="basic-input">
            <form action="{{url('admin/offers/update')}}" method="post" >
                @csrf
            <div class="row">
                <div class="col-md-12">
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
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{$title}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-12 mb-1">
                                    <div class="form-group">
                                        <label for="details">{{__('page.Details')}}</label>
                                        <input type="text" class="form-control" name="details" id="details" value="{{$offer->details}}" />
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label for="start_time">{{__('page.Start_time')}}</label>
                                        <input type="date" class="form-control" name="start_time" id="start_time" value="{{$offer->start_time}}" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label for="end_time">{{__('page.End_time')}}</label>
                                        <input type="date" class="form-control" name="end_time" id="end_time" value="{{$offer->end_time}}" required>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label for="discount">{{__('label.Discount')}}</label>
                                        <input type="number" class="form-control" name="discount" id="discount" value="{{$offer->discount}}" />
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label for="balance">{{__('label.balance')}}</label>
                                        <input type="number" class="form-control" name="amount" id="amount" value="{{$offer->amount}}" />
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label for="code">{{__('Label.Code')}}</label>
                                        <input type="number" class="form-control" name="code" id="code" value="{{$offer->code}}" />
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label for="num_of_trips">{{__('label.num_of_trips')}}</label>
                                        <input type="number" id="num_of_trips" min="0" name="num_of_trips" class="form-control" aria-describedby="basic-icon-default-email2" value="{{$offer->num_of_trips}}"  />
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label class="d-block">{{__('label.is_new_client')}}</label>
                                        <div class="custom-control custom-radio my-50">
                                            <input    type="radio"   id="validationRadio3" name="is_new_client"   class="custom-control-input"  value="1" {{($offer->is_new_client==1)?'checked':''}}   />
                                            <label class="custom-control-label" for="validationRadio3">{{__('page.Yes')}}</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input
                                                type="radio"  id="validationRadio4"  name="is_new_client" class="custom-control-input" value="0" {{($offer->is_new_client==0)?'checked':''}}   />
                                            <label class="custom-control-label" for="validationRadio4">{{__('page.No')}}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label for="basicSelect">{{__('label.Status')}}</label>
                                        <select class="form-control" id="basicSelect" name="status">
                                            <option value="0" @if($offer->status==0){{'seleceted'}}  @endif>Active</option>
                                            <option value="1"  @if($offer->status==1){{'seleceted'}} @endif>Pending</option>
                                            <option value="2"  @if($offer->status==2){{'seleceted'}} @endif>Closed</option>
                                            <option value="3"  @if($offer->status==3){{'seleceted'}} @endif>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-6 col-12 mb-1">
                                    <div class="form-group">
                                        <label>{{__('menus.Users')}}</label>
                                        <select class="select2 form-control" multiple name="users[]">
                                            <optgroup label="{{__('menus.Users')}}">
                                                @foreach($users as $user)
                                                    @if( in_array($user->id,$usersOffersIds))
                                                        <option value="{{$user->id}}" selected>{{$user->name}}</option>
                                                    @else
                                                        <option value="{{$user->id}}" >{{$user->name}}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="id" value="{{$offer->id}}" >
                                <div class="col-sm-9 ">
                                    <button type="submit" class="btn btn-primary mr-1 mb-1">{{__('page.Edit')}}</button>
                                    <a type="button" class="btn btn-info mr-1 mb-1 " href="{{url()->previous() }}">{{__('menus.back')}} </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">

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
