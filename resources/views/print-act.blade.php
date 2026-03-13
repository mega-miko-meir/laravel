@extends('layout')

@section('content')

    <x-act :employee="$employee" :tablet="$tablet" :hasPdf="$hasPdf" :pdfAssignment="$pdfAssignment"  />


    <button class="print:hidden fixed top-2 right-4 bg-green-500 text-white px-4 py-2 rounded"
    onclick="window.print()">
        Печать
    </button>


@endsection
