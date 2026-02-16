@extends('layouts.admin.app')

@section('title',translate('FCM Settings'))

@push('css_or_js')
<script>
    function sendTestMessage() {
        const url = '{{$arrocyWG['apiurl']}}';
        const token = $('input[name="arrocyWG_token"]').val();
        const receiver = $('input[name="test_phone_number"]').val();
        $.ajax({
            url,
            method: 'POST',
            data: {
                _token: '{{csrf_token()}}',
                token: token,
                receiver: receiver,
                msgtext: 'This is a test message from 6amMart.'
            },
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (response) {
                $('#loading').hide();
                if (response.error) {
                    toastr.error('Test message error: ' + (response.message || response.error || 'Unknown error'));
                } else {
                    toastr.success('Test message sent successfully: ' + receiver);
                }
            },
            error: function () {
                $('#loading').hide();
                toastr.error('{{translate('messages.something_went_wrong')}}');
            }
        });
    }
</script>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/firebase.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.firebase_push_notification_setup')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <?php
        $mod_type = 'grocery';
        if(request('module_type')){
            $mod_type = request('module_type');
        }
        ?>
        <div class="card">
            <div class="card-header card-header-shadow pb-0">
                <div class="d-flex flex-wrap justify-content-between w-100 row-gap-1">
                    <ul class="nav nav-tabs nav--tabs border-0 gap-2">
                        <li class="nav-item mr-2 mr-md-4">
                            <a href="{{ route('admin.business-settings.fcm-index') }}" class="nav-link pb-2 px-0 pb-sm-3" data-slide="1">
                                <img src="{{asset('/public/assets/admin/img/notify.png')}}" alt="">
                                <span>{{translate('Push Notification')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.business-settings.fcm-config') }}" class="nav-link pb-2 px-0 pb-sm-3 active" data-slide="2">
                                <img src="{{asset('/public/assets/admin/img/firebase2.png')}}" alt="">
                                <span>{{translate('Firebase Configuration')}}</span>
                            </a>
                        </li>
                    </ul>
                    <div class="py-1">
                        <div class="tab--content">
                            <div class="item show text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#firebase-modal">
                                <strong class="mr-2">{{translate('Where to get this information')}}</strong>
                                <div class="blinkings">
                                    <i class="tio-info-outined"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="firebase">
                        <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.update-fcm'):'javascript:'}}" method="post"
                                enctype="multipart/form-data">
                            @csrf
{{--                            @php($key=\App\Models\BusinessSetting::where('key','push_notification_key')->first())--}}
{{--                            <div class="form-group">--}}
{{--                                <label class="input-label"--}}
{{--                                        for="push_notification_key">{{translate('messages.server_key')}}</label>--}}
{{--                                <textarea id="push_notification_key" name="push_notification_key" class="form-control" placeholder="{{translate('Ex: AAAAaBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789')}}"--}}
{{--                                            >{{env('APP_MODE')!='demo'?$key->value??'':''}}</textarea>--}}
{{--                            </div>--}}
                            @php($serviceFileContent = \App\CentralLogics\Helpers::get_business_settings('push_notification_service_file_content'))
                            <div class="form-group">
                                <label class="input-label">{{translate('service_file_content')}}
                                    <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                                       title="{{ translate('select and copy all the service file content and add here') }}">
                                    </i>
                                </label>
                                <textarea name="push_notification_service_file_content" class="form-control" rows="15"
                                          >{{env('APP_MODE')!='demo'?($serviceFileContent?json_encode($serviceFileContent):''):''}}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="apiKey">{{translate('messages.api_key')}}</label>
                                <div class="d-flex">
                                    <input type="text" id="apiKey" value="{{$fcm_credentials['apiKey']??''}}"
                                        name="apiKey" class="form-control" placeholder="{{ translate('Ex: abcd1234efgh5678ijklmnop90qrstuvwxYZ') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 col-sm-6">
                                    @php($project_id=\App\Models\BusinessSetting::where('key','fcm_project_id')->first())
                                    <div class="form-group">
                                        <label class="input-label" for="projectId">{{translate('FCM Project ID')}}</label>
                                        <div class="d-flex">
                                            <input id="projectId" type="text" value="{{$project_id->value??''}}"
                                                name="projectId" class="form-control" placeholder="{{ translate('Ex: my-awesome-app-12345') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label  class="input-label" for="authDomain">{{translate('messages.auth_domain')}}</label>
                                        <div class="d-flex">
                                            <input id="authDomain" type="text" value="{{$fcm_credentials['authDomain']??''}}"
                                                name="authDomain" class="form-control" placeholder="{{ translate('Ex: my-awesome-app.firebase.com') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="storageBucket">{{translate('messages.storage_bucket')}}</label>
                                        <div class="d-flex">
                                            <input id="storageBucket" type="text" value="{{$fcm_credentials['storageBucket']??''}}"
                                                name="storageBucket" class="form-control" placeholder="{{ translate('Ex: my-awesome-app.apps.com') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="messagingSenderId">{{translate('messages.messaging_sender_id')}}</label>
                                        <div class="d-flex">
                                            <input id="messagingSenderId" type="text" value="{{$fcm_credentials['messagingSenderId'] ?? ''}}"
                                                name="messagingSenderId" class="form-control" placeholder="{{ translate('Ex: 1234567890') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="appId">{{translate('messages.app_id')}}</label>
                                        <div class="d-flex">
                                            <input id="appId" type="text" value="{{$fcm_credentials['appId']??''}}"
                                                name="appId" class="form-control" placeholder="{{ translate('Ex: 9876543210') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="measurementId">{{translate('messages.measurement_id')}}</label>
                                        <div class="d-flex">
                                            <input id="measurementId" type="text" value="{{$fcm_credentials['measurementId']??''}}"
                                                name="measurementId" class="form-control" placeholder="{{ translate('Ex: F-12345678') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label class="toggle-switch d-flex align-items-center mb-3" for="arrocyWG_status">
                                    <input type="checkbox" name="arrocyWG_status" class="toggle-switch-input" value="1" id="arrocyWG_status" {{$arrocyWG ? ($arrocyWG['status']==1 ? 'checked' : '') : ''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                    <span class="toggle-switch-content">
                                        <span class="d-block">ARROCY WHATSAPP GATEWAY</span>
                                    </span>
                                </label>
                            </div>
                            <ol>
                                <h3>How to get Arrocy Whatsapp Gateway Token:</h3>
                                <li>Login/Register at <a href="https://arrocy.com" target="_blank">ARROCY.COM</a></li>
                                <li>Go to menu Instances (copy TOKEN, put in the box below) OR Click ADD NEW to add new instance</li>
                                <li>Open Whatsapp on your phone, Menu >> Linked devices</li>
                                <li>You will see your profile picture if successfully connected</li>
                            </ol>
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1" hidden>arrocyWG API URL:</label>
                                <input name="arrocyWG_apiurl" class="form-control" value="{{$arrocyWG['apiurl']}}" placeholder="https://arrocy.com/api/send" readonly hidden />
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">arrocyWG token:</label>
                                <input name="arrocyWG_token" class="form-control" value="{{$arrocyWG['token']}}" placeholder="01KHHG7HC9K6B3A3OVM3SYAP4Q" />
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">arrocyWG OTP template:</label>
                                <input name="arrocyWG_otp_template" class="form-control" value="{{$arrocyWG['otp_template']}}" placeholder="OTP code: *#OTP#*" />
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="test_phone_number">Test Phone Number:</label>
                                <input type="text" name="test_phone_number" class="form-control col-6 mb-2" placeholder="{{ translate('Enter phone number with country code to send test message') }}" />
                                <button type="button" class="btn btn--warning" onclick="sendTestMessage()">Send Test Message</button>
                            </div>

                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary call-demo">{{translate('messages.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Firebase Modal -->
        <div class="modal fade" id="firebase-modal">
            <div class="modal-dialog status-warning-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    <div class="modal-body pb-5 pt-0">
                        <div class="single-item-slider owl-carousel">
                            <div class="item">
                                <div class="mb-20">
                                    <div class="text-center">
                                        <img src="{{asset('/public/assets/admin/img/firebase/slide-1.png')}}" alt="" class="mb-20">
                                        <h5 class="modal-title">{{translate('Go to Firebase Console')}}</h5>
                                    </div>
                                    <ul>
                                        <li>
                                            {{translate('Open your web browser and go to the Firebase Console')}}
                                            <a href="#" class="text--underline">
                                                {{translate('(https://console.firebase.google.com/)')}}
                                            </a>
                                        </li>
                                        <li>
                                            {{translate("Select the project for which you want to configure FCM from the Firebase Console dashboard.")}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="item">
                                <div class="mb-20">
                                    <div class="text-center">
                                        <img src="{{asset('/public/assets/admin/img/firebase/slide-2.png')}}" alt="" class="mb-20">
                                        <h5 class="modal-title">{{translate('Navigate to Project Settings')}}</h5>
                                    </div>
                                    <ul>
                                        <li>
                                            {{translate('In the left-hand menu, click on the "Settings" gear icon, and then select "Project settings" from the dropdown.')}}
                                        </li>
                                        <li>
                                            {{translate('In the Project settings page, click on the "Cloud Messaging" tab from the top menu.')}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="item">
                                <div class="mb-20">
                                    <div class="text-center">
                                        <img src="{{asset('/public/assets/admin/img/firebase/slide-3.png')}}" alt="" class="mb-20">
                                        <h5 class="modal-title">{{translate('Obtain All The Information Asked!')}}</h5>
                                    </div>
                                    <ul>
                                        <li>
                                            {{translate('In the Firebase Project settings page, click on the "General" tab from the top menu.')}}
                                        </li>
                                        <li>
                                            {{translate('Under the "Your apps" section, click on the "Web" app for which you want to configure FCM.')}}
                                        </li>
                                        <li>
                                            {{translate('Then Obtain API Key, FCM Project ID, Auth Domain, Storage Bucket, Messaging Sender ID.')}}
                                        </li>
                                    </ul>
                                    <p>
                                        {{translate('Note: Please make sure to use the obtained information securely and in accordance with Firebase and FCM documentation, terms of service, and any applicable laws and regulations.')}}
                                    </p>
                                    <div class="btn-wrap">
                                        <button type="submit" class="btn btn--primary w-100" data-dismiss="modal" data-toggle="modal" data-target="#firebase-modal-2">{{translate('Got It')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="slide-counter"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

