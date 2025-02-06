@extends('layout')

@section('content')
    {{-- <x-act :employee="$employee" :tablet="$tablet"/> --}}
    <x-act :employee="$employee" :tablet="$tablet" :hasPdf="$hasPdf" :pdfAssignment="$pdfAssignment"/>


    <button style="display: none !important; position: fixed; top: 10px; right: 10px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; font-size: 16px;"
    onclick="window.print();">
        Печать
    </button>


@endsection
