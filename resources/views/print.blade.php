@extends('layout')

@section('content')
    <x-act :employee="$employee" :tablet="$tablet"/>


    <button onclick="window.print()" style="position: fixed; top: 10px; right: 10px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; font-size: 16px;">
        Печать
    </button>

@endsection
