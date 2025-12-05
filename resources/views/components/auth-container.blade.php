{{-- <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-4">
    <h1 id="auth-title" class="text-2xl font-semibold mb-6">Login</h1>
    <div id="auth-content" class="w-full max-w-md bg-white shadow-md rounded-lg p-6">
        <x-login />
    </div>
    <button id="auth-toggle" onclick="toggleAuth()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
        Registration
    </button>
</div>

<script>
    const toggleAuth = () => {
        const title = document.getElementById('auth-title');
        const content = document.getElementById('auth-content');
        const button = document.getElementById('auth-toggle');

        if (title.innerText === 'Login') {
            title.innerText = 'Registration';
            content.innerHTML = `<x-registration />`;
            button.innerText = 'Login';
        } else {
            title.innerText = 'Login';
            content.innerHTML = `<x-login />`;
            button.innerText = 'Registration';
        }
    };
</script> --}}


<div id="auth-container" class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-4">
    <h1 id="auth-title" class="text-2xl font-semibold mb-6">Login</h1>
    <div id="auth-content" class="w-full max-w-md bg-white shadow-md rounded-lg p-6">
        <x-login />
    </div>
    <button
        id="auth-toggle"
        onclick="toggleAuth()"
        class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
        Registration
    </button>
</div>

<script>
    const toggleAuth = () => {
        const title = document.getElementById('auth-title');
        const content = document.getElementById('auth-content');
        const button = document.getElementById('auth-toggle');

        if (title.innerText === 'Login') {
            title.innerText = 'Registration';
            content.innerHTML = `<x-registration />`;
            button.innerText = 'Login';
        } else {
            title.innerText = 'Login';
            content.innerHTML = `<x-login />`;
            button.innerText = 'Registration';
        }
    };
</script>
