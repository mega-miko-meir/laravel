<div
    x-show="feedbackOpen"
    x-cloak
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>
    <div
        @click.away="feedbackOpen = false"
        class="bg-white rounded-lg w-full max-w-lg p-6"
    >
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Обратная связь</h2>

            <button
                @click="feedbackOpen = false"
                class="text-gray-400 hover:text-gray-600"
            >
                ✕
            </button>
        </div>

        <form method="POST" action="{{ route('feedback.store') }}" onsubmit="return confirm('Are you sure?');" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Заголовок</label>
                <input
                    type="text"
                    name="title"
                    class="w-full border rounded px-3 py-2"
                    required
                >
            </div>

            <div>
                <label class="text-sm font-medium">Сообщение</label>
                <div x-data="{
                    message: '',
                    screenshot: null,
                    handlePaste(e) {
                        const item = Array.from(e.clipboardData.items).find(i => i.type.indexOf('image') !== -1);
                        if (item) {
                            const reader = new FileReader();
                            reader.onload = (event) => { this.screenshot = event.target.result; };
                            reader.readAsDataURL(item.getAsFile());
                        }
                    }
                }" class="space-y-2">

                    <textarea
                        name="message"
                        x-model="message"
                        @paste="handlePaste"
                        class="w-full border p-2 rounded"
                        placeholder="Вставьте текст или скриншот..."></textarea>

                    <!-- Вот здесь будет видна картинка -->
                    <template x-if="screenshot">
                        <div class="relative w-48">
                            <img :src="screenshot" class="rounded border shadow-sm">
                            <button @click="screenshot = null" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs">×</button>
                            <!-- Скрытое поле, чтобы отправить картинку в базу -->
                            <input type="hidden" name="screenshot" :value="screenshot">
                        </div>
                    </template>
                </div>

            </div>

            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    @click="feedbackOpen = false"
                    class="px-4 py-2 border rounded"
                >
                    Отмена
                </button>

                <button
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Отправить
                </button>
            </div>
        </form>
    </div>
</div>
<!-- =============== END MODAL ================= -->
