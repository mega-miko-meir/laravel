@if(session('success'))
    <div id="flash-message" class="fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50">
        <h1 class="text-lg font-semibold">{{ session('success') }}</h1>
    </div>
@endif

@if(session('error'))
    <div id="flash-message" class="fixed top-5 right-5 bg-red-500 text-white px-6 py-3 rounded shadow-lg z-50">
        <h1 class="text-lg font-semibold">{{ session('error') }}</h1>
    </div>
@endif

<script>
    // Скрываем сообщение через 5 секунд с плавным исчезновением
    setTimeout(() => {
        const flashMessage = document.getElementById('flash-message');
        if (flashMessage) {
            flashMessage.style.transition = 'opacity 0.5s';
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 500);
        }
    }, 5000);
</script>
