@extends('layouts.app')

@section('body')
    <div class="flex-center position-ref full-height">
        @if (Route::has('login'))
            <div class="top-right links">
                @auth
                    <a href="{{ url('/home') }}">Home</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        @endif

        <div class="content">
            <div class="title m-b-md">
                Toilet Adviser
            </div>
            <div style="font-size: 24px">
                Privacy Policy
            </div>

            <div style="margin: 100px; font-size: 18px">
                Your privacy is important to us. It is Nemanja Gajic's policy to respect your privacy regarding any information we may collect from you across our application.
                We only ask for personal information when we truly need it to provide a service to you. We collect it by fair and lawful means, with your knowledge and consent. We also let you know why we’re collecting it and how it will be used.
                We only retain collected information for as long as necessary to provide you with your requested service. What data we store, we’ll protect within commercially acceptable means to prevent loss and theft, as well as unauthorized access, disclosure, copying, use or modification.
                We don’t share any personally identifying information publicly or with third-parties, except when required to by law.
                Our website may link to external sites that are not operated by us. Please be aware that we have no control over the content and practices of these sites, and cannot accept responsibility or liability for their respective privacy policies.
                You are free to refuse our request for your personal information, with the understanding that we may be unable to provide you with some of your desired services.
                Your continued use of our website will be regarded as acceptance of our practices around privacy and personal information. If you have any questions about how we handle user data and personal information, feel free to contact us on email Nemanja.gajicru96@gmail.com.
            </div>
        </div>
    </div>
@endsection