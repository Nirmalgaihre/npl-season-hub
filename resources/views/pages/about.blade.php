@extends('layouts.app')

@section('title', 'About Us | NPL Season Hub')
@section('meta_description', 'Learn about NPL Season Hub, a website for Nepal Premier League cricket news, teams, fixtures, results, and updates.')

@section('content')
<div class="container py-5">
    <div class="mx-auto" style="max-width: 900px;">
        <p class="text-uppercase fw-semibold text-danger mb-2">Who we are</p>
        <h1 class="display-5 fw-bold mb-4">About NPL Season Hub</h1>

        <p class="lead">
            Your destination for following Nepal Premier League cricket.
        </p>

        <p>
            NPL Season Hub is an independent website created to help cricket
            fans follow the Nepal Premier League (NPL) in one convenient place.
            Our goal is to make relevant tournament information easier to find
            and understand.
        </p>

        <h2 class="h4 mt-4">What you can find here</h2>
        <ul>
            <li>Match fixtures and schedules.</li>
            <li>Team and tournament information.</li>
            <li>Match results and standings, where available.</li>
            <li>Cricket news, articles, and tournament updates.</li>
        </ul>

        <h2 class="h4 mt-4">Our commitment</h2>
        <p>
            We aim to publish useful, readable, and accurate information.
            Information can change, so visitors should check official tournament
            sources for final confirmation of schedules, results, and announcements.
        </p>

        <p>
            NPL Season Hub is an independent informational website and is not
            affiliated with or endorsed by the official tournament organisers
            unless explicitly stated.
        </p>

        <h2 class="h4 mt-4">Get in touch</h2>
        <p>
            Found an error or have a suggestion? Please
            <a href="{{ route('contact') }}">contact us</a>.
        </p>
    </div>
</div>
@endsection
