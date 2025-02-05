@if(session('success'))
    <div id="flash-message" class="alert alert-success">
        <h1 class="text-2xl font-bold text-green-600 mb-6">{{session('success')}} </h1>
    </div>
@endif

@if(session('error'))
    <div id="flash-message" class="alert alert-danger">
        <h1 class="text-2xl font-bold text-red-600 mb-6">{{session('error')}} </h1>
    </div>
@endif

<script>
    // Hide the flash message after 5 seconds
    setTimeout(() => {
        const flashMessage = document.getElementById('flash-message');
        if (flashMessage) {
            flashMessage.style.display = 'none';
        }
    }, 5000);
</script>
