@extends('layout')

@section('content')

<body>
    <div class="container mx-auto mt-20 max-w-sm">
        <h1 class="text-1xl mb-4">Alpine demo</h1>
        {{-- x-data alpine component--}}
        <div x-data="{
        open: false,
        name: 'Brad',
        search: '',
        checked: false
        }">
            {{-- x-on and x-bind--}}
            <button x-on:click="open = !open"
            x-bind:class="open ? 'bg-blue-800' : 'bg-slate-700' "
            class=" text-white px-4 py-2 rounded">
                Toggle
            </button>
            {{-- x-show --}}
            <div x-show="open" x-transition style="display: none">
                <p class="bg-gray-200 p-4 my-6">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Ad maxime quaerat, quisquam fugit esse minima iste totam possimus tenetur aliquid.
                </p>
            </div>
            {{-- x-text --}}
            <div class="my-4">
                The value of name is <span x-text="name" class="font-bold"></span>
            </div>
            {{-- x-model --}}
            {{-- first example --}}
            <div>
                <input
                type="text"
                x-model="search"
                placeholder="Search.."
                class="border p-2 w-full mb-2 mt-6"
                >
                Searching for <span x-text="search" class="font-bold"></span>
            </div>
            {{-- second example --}}
            <div">
                <input type="checkbox" x-model="checked">
                <p x-text="checked ? 'Turned on' : 'turned off' "></p>
            </div>

            {{-- x-for --}}
            <div x-data="{ posts: ['Apple', 'Orange', 'Lemon', 'Banana'] }">
                <h3 class="font-bold mt-6 mb-3 text-2xl">Posts</h3>
                <template x-for="post in posts">
                    <div x-text="post"></div>
                </template>
                <button x-on:click="posts.push('New post')" class="bg-blue-800 text-white mt-4 rounded-md px-4 py-2">Add post</button>
            </div>

        </div>

    </div>

</body>


<script src="{{ asset('js/search.js') }}"></script>
@endsection
