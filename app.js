'use strict';

// Shared helpers for the pages that use fetch(). Loaded on every page.
// Page-specific code lives in js/list.js and js/ticket.js.
window.FixIt = (function () {
    // The CSRF token is printed in a <meta> tag by includes/header.php
    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    // Sends a request and always gives back { ok, status, data }.
    // A 401 means the session ended, so we go back to the login page.
    async function request(url, options) {
        let response;
        try {
            response = await fetch(url, options);
        } catch (err) {
            return { ok: false, status: 0, data: { ok: false, error: 'Could not reach the server.' } };
        }

        let data = null;
        try {
            data = await response.json();
        } catch (err) {
            data = null;
        }

        if (response.status === 401) {
            window.location.href = 'login.php';
        }
        if (!data) {
            data = { ok: false, error: 'The server sent an unexpected response.' };
        }
        return { ok: response.ok && data.ok === true, status: response.status, data: data };
    }

    function get(url) {
        return request(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
    }

    // POSTs a JSON body, with the CSRF token in a header
    function post(url, body) {
        return request(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrfToken()
            },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        });
    }

    // "In progress" -> "in-progress", for CSS class names
    function slug(text) {
        return String(text).toLowerCase().replace(/\s+/g, '-');
    }

    return { get: get, post: post, slug: slug };
})();
