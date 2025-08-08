@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div id="app">
        <fishing-spot-detail :spot-id="{{ $spotId }}"></fishing-spot-detail>
    </div>
</div>
@endsection
