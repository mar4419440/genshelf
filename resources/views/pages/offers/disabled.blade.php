@extends('layouts.app')

@section('content')
<div class="page-hdr">
    <h2>{{ __('Special Offers') }}</h2>
</div>

<div class="card empty-state">
    {{ __('Disabled') }}
</div>
@endsection
