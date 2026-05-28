(function () {
    const baseUrl = window.ICTTS_BASE_URL || '/ICTTS/public';

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    async function fillSelect(select, rows, placeholder) {
        select.innerHTML = '';
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = placeholder;
        select.appendChild(empty);
        rows.forEach((row) => {
            const option = document.createElement('option');
            option.value = row.id;
            option.textContent = row.name;
            select.appendChild(option);
        });
        select.disabled = rows.length === 0;
    }

    const regionSelect = document.getElementById('regionSelect');
    const officeSelect = document.getElementById('officeSelect');
    if (regionSelect && officeSelect) {
        regionSelect.addEventListener('change', async () => {
            officeSelect.disabled = true;
            const response = await fetch(`${baseUrl}/api/offices?region_id=${encodeURIComponent(regionSelect.value)}`);
            const data = await response.json();
            fillSelect(officeSelect, data.offices || [], 'Select branch/office');
        });
    }

    const categorySelect = document.getElementById('categorySelect');
    const serviceSelect = document.getElementById('serviceSelect');
    if (categorySelect && serviceSelect) {
        categorySelect.addEventListener('change', async () => {
            serviceSelect.disabled = true;
            const response = await fetch(`${baseUrl}/api/services?category_id=${encodeURIComponent(categorySelect.value)}`);
            const data = await response.json();
            fillSelect(serviceSelect, data.services || [], 'Select specific request');
        });
    }
})();
