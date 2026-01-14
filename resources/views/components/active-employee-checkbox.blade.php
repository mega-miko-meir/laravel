<div style="display: flex; align-items: center; gap: 10px;">
    <label for="ticker" style="cursor: pointer; font-size: 16px;">Только активные сотрудники</label>
    @php
        $activeOnly = request('active_only', 1); // По умолчанию 1 включено
    @endphp
    <input type="checkbox" id="ticker" name="" value="1" style="display: none;" {{ $activeOnly == 1 ? 'checked' : '' }}>
    <label for="ticker" style="width: 40px; height: 20px; background: {{ $activeOnly == 1 ? '#4CAF50' : '#ccc' }};
           border-radius: 10px; display: flex; align-items: center; padding: 2px; cursor: pointer; position: relative;">
        <span style="width: 16px; height: 16px; background: white; border-radius: 50%; position: absolute; left: {{ $activeOnly == 1 ? '22px' : '2px' }};
               transition: 0.3s;"></span>
    </label>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let checkbox = document.getElementById('ticker');
        checkbox.addEventListener('change', function () {
            let url = new URL(window.location.href);
            url.searchParams.set('active_only', checkbox.checked ? 1 : 0);
            window.location.href = url.href;
        });
    });
</script>
