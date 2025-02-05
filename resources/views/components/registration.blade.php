<div>
    <div class="p-6 bg-gray-100 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4">Register</h2>
        <form action="/register" method="POST" class="space-y-4">
            @csrf
            <input name="full_name" type="text" placeholder="Full name" class="w-full p-2 border rounded">
            <input name="first_name" type="text" placeholder="First name" class="w-full p-2 border rounded">
            <input name="last_name" type="text" placeholder="Last name" class="w-full p-2 border rounded">
            <input name="position" type="text" placeholder="Position" class="w-full p-2 border rounded">
            <input name="email" type="email" placeholder="Email" class="w-full p-2 border rounded">
            <input name="password" type="password" placeholder="Password" class="w-full p-2 border rounded">
            <button class="btn-primary bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Submit</button>
        </form>
    </div>
</div>
