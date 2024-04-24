@extends('brokers::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('brokers.name') !!}</p>
@endsection
