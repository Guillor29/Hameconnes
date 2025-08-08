@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="component-container">
        <fish-species-detail :species-id="{{ $speciesId }}"></fish-species-detail>
    </div>
</div>
@endsection
