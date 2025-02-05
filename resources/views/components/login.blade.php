<div>
    <div class="p-6 bg-gray-100 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Log in</h2>
        <form action="/login" method="POST" class="space-y-4">
            @csrf
            <input name="loginname" type="text" placeholder="Name" class="w-full p-2 border rounded">
            <input name="loginpassword" type="password" placeholder="Password" class="w-full p-2 border rounded">
            <button class="btn-primary bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Submit</button>
        </form>
    </div>
</div>
