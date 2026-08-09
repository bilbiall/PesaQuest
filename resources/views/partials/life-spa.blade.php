{{-- Life section SPA bootstrap — makes the 4 life tabs (life-tabs.blade.php)
     swap #life-panel's content via fetch instead of a full page reload.
     Include ONCE per life shell page, after #life-panel and the tab bar. --}}
<script>
(function () {
    var panel = document.getElementById('life-panel');
    if (!panel) return;

    var TAB_URLS = {
        board:     '{{ route('life.board') }}',
        career:    '{{ route('life.career') }}',
        timeline:  '{{ route('life.timeline') }}',
        finances:  '{{ route('life.finances') }}',
    };

    function setActiveTab(key) {
        document.querySelectorAll('[data-life-tab]').forEach(function (el) {
            el.classList.toggle('lt-active', el.dataset.lifeTab === key);
        });
    }

    // innerHTML doesn't execute <script> tags — recreate each one so it runs.
    function execScripts(container) {
        container.querySelectorAll('script').forEach(function (old) {
            var s = document.createElement('script');
            for (var i = 0; i < old.attributes.length; i++) {
                s.setAttribute(old.attributes[i].name, old.attributes[i].value);
            }
            s.textContent = old.textContent;
            old.replaceWith(s);
        });
    }

    var loading = false;
    function loadTab(key, url, push) {
        url = url || TAB_URLS[key];
        if (!url || loading) return;
        loading = true;
        setActiveTab(key);
        panel.style.transition = 'opacity .12s';
        panel.style.opacity = '.35';

        fetch(url, { headers: { 'X-Fragment': '1', 'Accept': 'text/html' } })
            .then(function (res) {
                if (!res.ok) throw new Error('bad response');
                return res.text();
            })
            .then(function (html) {
                panel.innerHTML = html;
                execScripts(panel);
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(panel);
                }
                var titleEl = panel.querySelector('[data-page-title]');
                if (titleEl) document.title = titleEl.dataset.pageTitle;
                if (push) history.pushState({ lifeTab: key, url: url }, '', url);
                window.scrollTo({ top: 0, behavior: 'instant' });
            })
            .catch(function () {
                // Fragment fetch failed for any reason — fall back to a real
                // navigation rather than leaving the tab in a broken state.
                window.location.href = url;
            })
            .finally(function () {
                panel.style.opacity = '1';
                loading = false;
            });
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('[data-life-tab]');
        if (!link) return;
        e.preventDefault();
        // Use the link's own href (so query strings like ?stmt_filter=income
        // survive) — the fixed TAB_URLS map is only a fallback.
        loadTab(link.dataset.lifeTab, link.getAttribute('href'), true);
    });

    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.lifeTab) loadTab(e.state.lifeTab, e.state.url, false);
    });

    // The very first load's history entry has no state (browser default) — give
    // it one, so pressing Back from a later tab lands here correctly instead of
    // updating the URL bar while leaving the wrong content on screen.
    var initialTab = document.querySelector('[data-life-tab].lt-active');
    if (initialTab) {
        history.replaceState({ lifeTab: initialTab.dataset.lifeTab, url: window.location.href }, '', window.location.href);
    }
})();
</script>
