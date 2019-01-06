@extends('layouts.app')

@section('content')

    @push('css')
        <link href="{{url('css/style.css')}}" rel="stylesheet">

        @endpush

    @push('js')
        <script src="{{url('js/jquery-1.10.1.min.js')}}"></script>
        <script src="{{url('js/socket.io.js')}}"></script>

        <script>
            var user_id='{{auth()->user()->id}}';
            var username='{{auth()->user()->name}}';
            var myname='{{auth()->user()->name}}';
            var typing='{{url('image/typing.gif')}}';
            var check_tables='{{url("/check_tables")}}';
            var add_tables='{{url("/add_tables")}}';
            var send_message='{{url("/send_message")}}';
            var ur='{{url('/')}}';
            var tok='{{csrf_token()}}';
        </script>
        <script src="{{url('js/script.js')}}"></script>
    @endpush
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Chat Control</div>
                <div class="card-body">
                    <p><select name="status" class="select_status">
                            <option value="online" selected>Online</option>
                            <option value="offline">Offline</option>
                            <option value="bys">Busy</option>
                            <option value="dnd">Do not Starve</option>

                        </select></p>
                    <div id="chat-sidebar">
                        @foreach(App\User::where('id','!=',auth()->user()->id)->get() as $user)
                        <div id="sidebar-user-box" class="{{$user->id}} user" uid="{{$user->id}}" >
                            <img class="user_img" src="{{url('image/user.png')}}" />
                            <span id="slider-username">{{$user->name}} </span>
                            <span class="user_status user_{{$user->id}}">&nbsp;</span>
                        </div>
                        @endforeach

                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
