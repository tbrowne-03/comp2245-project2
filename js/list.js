'use strict';

// Ticket list page: the filter buttons fetch JSON and rebuild the table
// without reloading the page.
(function () {
    const tbody = document.getElementById('ticket-body');
    if (!tbody) {
        return;
    }
    const countEl = document.getElementById('ticket-count');
    const errorEl = document.getElementById('list-error');
    const buttons = document.querySelectorAll('.filters [data-filter]');

    let latestRequest = 0;   // so a slow answer can't overwrite a newer one

    function makeCell(label, child) {
        const td = document.createElement('td');
        td.dataset.label = label;
        if (typeof child === 'string') {
            td.textContent = child;          // textContent, never innerHTML
        } else {
            td.appendChild(child);
        }
        return td;
    }

    function makeBadge(kind, value) {
        const span = document.createElement('span');
        span.className = 'badge ' + kind + '-' + FixIt.slug(value);
        span.textContent = value;
        return span;
    }

    function renderTickets(tickets) {
        tbody.replaceChildren();

        if (tickets.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 7;
            td.className = 'empty';
            td.textContent = 'No tickets to show.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        tickets.forEach(function (t) {
            const tr = document.createElement('tr');

            const link = document.createElement('a');
            link.href = 'ticket.php?id=' + encodeURIComponent(t.id);
            link.textContent = t.title;

            const assigned = makeCell('Assigned to', t.assigned_to_name || 'Nobody');
            if (!t.assigned_to_name) {
                assigned.classList.add('muted');
            }

            tr.appendChild(makeCell('#', String(t.id)));
            tr.appendChild(makeCell('Title', link));
            tr.appendChild(makeCell('Priority', makeBadge('priority', t.priority)));
            tr.appendChild(makeCell('Status', makeBadge('status', t.status)));
            tr.appendChild(makeCell('Created by', t.created_by_name));
            tr.appendChild(assigned);
            tr.appendChild(makeCell('Created', t.created_at));
            tbody.appendChild(tr);
        });
    }

    function setActive(filter) {
        buttons.forEach(function (btn) {
            const active = btn.dataset.filter === filter;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    async function load(filter) {
        const myRequest = ++latestRequest;
        setActive(filter);

        const result = await FixIt.get('api/tickets.php?filter=' + encodeURIComponent(filter));
        if (myRequest !== latestRequest) {
            return;
        }

        if (!result.ok) {
            errorEl.textContent = result.data.error || 'Could not load the tickets.';
            errorEl.hidden = false;
            countEl.textContent = '';
            return;
        }

        errorEl.hidden = true;
        const n = result.data.count;
        countEl.textContent = n + (n === 1 ? ' ticket' : ' tickets');
        renderTickets(result.data.tickets);
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            load(btn.dataset.filter);
        });
    });

    load('all');
})();
