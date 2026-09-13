@extends('layouts.landing')
@section('content')
<livewire:navigation />

<div class="app-page">
    <div class="app-page-card">
        <h1 class="app-page-title">Contact Us</h1>
        <p class="app-page-lead">Got a technical issue? Want to send feedback about us? Become a partner? Let us know.</p>
        <form action="{{ route('contact.send') }}" method="post" class="app-page-form">
            @csrf

            <label for="email">Your email</label>
            <input type="email" id="email" name="email" placeholder="e.g. name@mercibuddy.com" required>

            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Let us know how we can help you" required>

            <label for="message">Your message</label>
            <textarea id="message" name="message" rows="6" placeholder="Leave a comment..."></textarea>

            <button type="submit">Send Message</button>
        </form>
    </div>
</div>

<x-session-message />
<livewire:footer-section />
@endsection
