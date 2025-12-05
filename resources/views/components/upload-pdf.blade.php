<div>

    <h2>Загрузить подписанный АПП</h2>

    @if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form action="/upload-pdf" method="POST" enctype="multipart/form-data">
        @csrf
        <label>Сотрудник ID:</label>
        <input type="number" name="employee_id" required>
        <br>
        <label>Планшет ID:</label>
        <input type="number" name="tablet_id" required>
        <br>
        <label>Выберите PDF:</label>
        <input type="file" name="pdf_file" accept="application/pdf" required>
        <br><br>
        <button type="submit">Загрузить</button>
    </form>
</div>
