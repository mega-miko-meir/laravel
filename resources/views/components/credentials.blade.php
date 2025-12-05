@props(['employee'])

<h3 id="credentials" class="mt-10 text-lg font-semibold" style="cursor:pointer;">Учётные данные</h3>
<div id="credentials-block" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded-lg mt-4">
        @foreach ($employee->credentials as $credential)
        <div class="mb-2">
            <p class="text-sm"><span class="font-medium">{{ strtoupper($credential->system) }}:</span></p>
            <p class="text-sm">Логин: <span class="font-mono">{{ $credential->login }}</span></p>
            <p class="text-sm">Пароль: <span class="font-mono text-red-600">{{ $credential->password }}</span></p>
        </div>
        @endforeach
    </div>

    <div class="mt-6 bg-white p-4 rounded-lg">
        <!-- Кнопка добавить логин -->
        <button onclick="toggleCredentialForm()" class="bg-blue-600 text-white px-3 py-1 text-sm rounded hover:bg-blue-700 transition">
            Добавить или изменить логин
        </button>


        <!-- Форма добавления/обновления логина (скрыта по умолчанию) -->
        <form action="{{ route('employees.updateCredentials', $employee->id) }}" method="POST" id="credentialForm" class="mt-4 bg-gray-50 p-4 rounded-lg shadow hidden">
            @csrf
            @method('PUT')

            <label for="system" class="block text-sm font-medium mb-1">Выберите систему:</label>
            <select name="system" id="system" class="w-full p-2 border rounded" onchange="userNameAutomative()">
                <option value="">Select System</option>
                <option value="crm">CRM</option>
                <option value="tablet">Планшет</option>
                <option value="kmp">КМП</option>
            </select>

            <label for="user_name" class="block text-sm font-medium mt-2 mb-1">Имя пользователя:</label>
            <input type="text" name="user_name" id="user_name" class="w-full p-2 border rounded">

            <label for="login" class="block text-sm font-medium mt-2 mb-1">Логин:</label>
            <input type="text" name="login" id="login" class="w-full p-2 border rounded">

            <label for="password" class="block text-sm font-medium mt-2 mb-1">Пароль:</label>
            <input type="text" name="password" id="password" class="w-full p-2 border rounded">

            <label for="add_password" class="block text-sm font-medium mt-2 mb-1">Доп пароль:</label>
            <input type="text" name="add_password" id="add_password" class="w-full p-2 border rounded">

            <div class="flex justify-end mt-4">
                <button type="button" onclick="toggleCredentialForm()" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-100 mr-2">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>
<script>

    document.getElementById('credentials').addEventListener('click', function(){
        let credentialsBlock = document.getElementById('credentials-block');
        credentialsBlock.classList.toggle('hidden');
    })

    function userNameAutomative() {
        let system = document.getElementById('system').value;
        let userNameInput = document.getElementById('user_name');
        let loginInput = document.getElementById('login');

        let firstName = @json($employee->first_name);
        let lastName = @json($employee->last_name);
        let fullName = @json($employee->full_name);
        let email = @json($employee->email);

        let parts = fullName.split(' ');
        let KMPName = parts.length > 2 ? parts.slice(0, 2).join(' ') : fullName;


        if(system === 'crm'){
            userNameInput.value = firstName + ' ' + lastName;
            loginInput.value = email;
        } else if(system === 'kmp'){
            userNameInput.value = KMPName;
        } else {
            userNameInput.value = '';
            loginInput.value = '';
        }


    }

    function toggleCredentialForm() {
        document.getElementById('credentialForm').classList.toggle('hidden');
    }

    function deleteCredential(id) {
        if (confirm("Удалить логин?")) {
            fetch(`/employees/credentials/${id}`, {
                method: "DELETE",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
            }).then(() => location.reload());
        }
    }
</script>
