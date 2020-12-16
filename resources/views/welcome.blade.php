@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="links">
            <ul>
                @foreach ($links as $link) <li>
                    <a href="{{ $link->url }}">{{ $link->title }}</a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    </body>

    </html>

    @endsection
