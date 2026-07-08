(function () {
    const baseUrl = window.ICTTS_BASE_URL || '/ICTTS/public';
    const rowsPerPage = 10;

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    function setupMenu() {
        const toggle = document.querySelector('[data-menu-toggle]');
        const closeTargets = document.querySelectorAll('[data-menu-close], .sidebar .nav-link');
        if (!toggle) {
            return;
        }

        function setOpen(open) {
            document.body.classList.toggle('nav-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', () => {
            setOpen(!document.body.classList.contains('nav-open'));
        });

        closeTargets.forEach((target) => {
            target.addEventListener('click', () => setOpen(false));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    }

    function hasActiveFilter(form) {
        return Array.from(form.elements).some((field) => {
            if (!field.name || field.type === 'hidden' || field.type === 'submit' || field.tagName === 'BUTTON') {
                return false;
            }
            return String(field.value || '').trim() !== '';
        });
    }

    function setupFilterPanels() {
        document.querySelectorAll('form.filter-bar').forEach((form, index) => {
            if (form.closest('.filter-panel')) {
                return;
            }

            const panel = document.createElement('section');
            panel.className = 'filter-panel';

            const header = document.createElement('div');
            header.className = 'filter-panel-header';

            const titleWrap = document.createElement('div');
            const title = document.createElement('div');
            title.className = 'filter-panel-title';
            title.textContent = 'Filters';
            const summary = document.createElement('div');
            summary.className = 'filter-panel-summary';
            summary.textContent = hasActiveFilter(form) ? 'Filters are applied' : 'Refine the visible records';
            titleWrap.append(title, summary);

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline-secondary btn-sm filter-toggle';
            button.setAttribute('aria-expanded', hasActiveFilter(form) ? 'true' : 'false');
            button.setAttribute('aria-controls', `filterPanel${index}`);
            button.textContent = hasActiveFilter(form) ? 'Hide' : 'Show';

            const body = document.createElement('div');
            body.id = `filterPanel${index}`;
            body.hidden = !hasActiveFilter(form);

            form.parentNode.insertBefore(panel, form);
            panel.append(header, body);
            header.append(titleWrap, button);
            body.appendChild(form);

            button.addEventListener('click', () => {
                const isOpen = !body.hidden;
                body.hidden = isOpen;
                button.textContent = isOpen ? 'Show' : 'Hide';
                button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });
        });
    }

    function setupTablePagination() {
        document.querySelectorAll('table.table').forEach((table, index) => {
            if (table.dataset.paginated === 'true' || table.closest('.notification-menu')) {
                return;
            }

            const tbody = table.tBodies[0];
            if (!tbody) {
                return;
            }

            const rows = Array.from(tbody.rows);
            const dataRows = rows.filter((row) => !row.querySelector('td[colspan]'));
            if (dataRows.length <= rowsPerPage) {
                return;
            }

            table.dataset.paginated = 'true';
            let page = 1;
            const totalPages = Math.ceil(dataRows.length / rowsPerPage);

            const pager = document.createElement('div');
            pager.className = 'table-pagination';

            const info = document.createElement('div');
            info.className = 'table-pagination-info';

            const group = document.createElement('div');
            group.className = 'btn-group btn-group-sm';
            group.setAttribute('role', 'group');
            group.setAttribute('aria-label', 'Table pagination');

            const previous = document.createElement('button');
            previous.type = 'button';
            previous.className = 'btn btn-outline-secondary';
            previous.textContent = 'Previous';

            const pageLabel = document.createElement('button');
            pageLabel.type = 'button';
            pageLabel.className = 'btn btn-outline-secondary';
            pageLabel.disabled = true;

            const next = document.createElement('button');
            next.type = 'button';
            next.className = 'btn btn-outline-secondary';
            next.textContent = 'Next';

            group.append(previous, pageLabel, next);
            pager.append(info, group);

            const container = table.closest('.table-responsive') || table;
            container.parentNode.insertBefore(pager, container.nextSibling);

            function render() {
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                dataRows.forEach((row, rowIndex) => {
                    row.hidden = rowIndex < start || rowIndex >= end;
                });
                previous.disabled = page === 1;
                next.disabled = page === totalPages;
                pageLabel.textContent = `${page} / ${totalPages}`;
                info.textContent = `Showing ${start + 1}-${Math.min(end, dataRows.length)} of ${dataRows.length}`;
            }

            previous.addEventListener('click', () => {
                if (page > 1) {
                    page -= 1;
                    render();
                }
            });

            next.addEventListener('click', () => {
                if (page < totalPages) {
                    page += 1;
                    render();
                }
            });

            render();
        });
    }

    function setupPasswordToggles() {
        document.querySelectorAll('input[type="password"]').forEach((input, index) => {
            if (input.closest('.input-group')) {
                return;
            }

            const group = document.createElement('div');
            group.className = 'input-group';
            input.parentNode.insertBefore(group, input);
            group.appendChild(input);

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline-secondary password-toggle';
            button.setAttribute('aria-label', 'Show password');
            button.setAttribute('aria-pressed', 'false');
            button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            group.appendChild(button);

            if (!input.id) {
                input.id = `passwordField${index}`;
            }

            button.addEventListener('click', () => {
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
                button.setAttribute('aria-pressed', visible ? 'false' : 'true');
            });
        });
    }

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

    setupMenu();
    setupFilterPanels();
    setupTablePagination();
    setupPasswordToggles();
})();
