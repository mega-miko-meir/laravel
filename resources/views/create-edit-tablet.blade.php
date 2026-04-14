@extends('layout')

@section('content')
<x-back-button />
    <div class="min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-3xl rounded-3xl border border-slate-200 bg-white/95 p-8 shadow-2xl shadow-slate-200/40 backdrop-blur-sm">
            @php $isEdit = isset($tablet); @endphp
            <div class="mb-8 rounded-3xl bg-slate-50 p-6 shadow-sm ring-1 ring-slate-200">
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $isEdit ? 'Редактировать планшет' : 'Добавить планшет' }}
                </h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ $isEdit ? 'Измените данные планшета и сохраните изменения.' : 'Заполните форму, чтобы добавить новый планшет в систему.' }}
                </p>
            </div>

            <x-tablet-form
                :tablet="$tablet ?? null"
                action="{{ $isEdit ? route('tablet.update', $tablet->id) : route('tablet.store') }}"
                method="{{ $isEdit ? 'PUT' : 'POST' }}"
            />
        </div>
    </div>
@endsection
