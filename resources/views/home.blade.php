@extends('layouts.master')

@section('content')
    <section class="hero">
        <h1>Welcome to {{ config('app.name', 'Mini Blog Notes') }}</h1>
        <p class="lead">A lightweight notes & posts manager — simple frontend demo.</p>
        <div class="controls">
            <button id="loadPosts" class="btn">Load Posts</button>
        </div>
        <div id="postsList" class="list"></div>
    </section>
@endsection
