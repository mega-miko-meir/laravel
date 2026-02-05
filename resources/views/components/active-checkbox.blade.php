@props(['label' => 'Только активные сотрудники'])

<div style="display: flex; align-items: center; gap: 10px;">
    <label for="ticker" style="cursor: pointer; font-size: 16px;">
        {{$label}}
    </label>

    <input type="checkbox" id="ticker" style="display: none;">

    <label id="ticker-ui"
        for="ticker"
        style="width: 40px; height: 20px; background: #ccc;
               border-radius: 10px; display: flex; align-items: center;
               padding: 2px; cursor: pointer; position: relative;">
        <span id="ticker-dot"
            style="width: 16px; height: 16px; background: white;
                   border-radius: 50%; position: absolute;
                   left: 2px; transition: 0.3s;">
        </span>
    </label>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const checkbox = document.getElementById('ticker');
    const ui = document.getElementById('ticker-ui');
    const dot = document.getElementById('ticker-dot');

    // 1️⃣ читаем localStorage (по умолчанию 1)
    let activeOnly = localStorage.getItem('active_only');
    activeOnly = activeOnly === null ? 1 : Number(activeOnly);

    // 2️⃣ применяем состояние
    checkbox.checked = activeOnly === 1;
    updateUI(activeOnly === 1);

    // 3️⃣ при изменении
    checkbox.addEventListener('change', function () {
        const value = checkbox.checked ? 1 : 0;

        localStorage.setItem('active_only', value);
        updateUI(checkbox.checked);

        const url = new URL(window.location.href);
        url.searchParams.set('active_only', value);
        window.location.href = url.href;
    });

    function updateUI(enabled) {
        ui.style.background = enabled ? '#4CAF50' : '#ccc';
        dot.style.left = enabled ? '22px' : '2px';
    }
});
</script>
