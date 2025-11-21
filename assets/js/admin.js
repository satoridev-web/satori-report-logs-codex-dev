(function () {
        var tabLinks = document.querySelectorAll('.srl-editor-tabs .nav-tab');
        var tabPanels = document.querySelectorAll('.srl-tab-panel');
        var tabField = document.querySelector('input[name="tab"]');

        if (!tabLinks.length) {
                return;
        }

        var setActiveTab = function (tab) {
                if (!tab) {
                        return;
                }

                tabLinks.forEach(function (link) {
                        link.classList.toggle('nav-tab-active', link.dataset.tab === tab);
                });

                tabPanels.forEach(function (panel) {
                        panel.classList.toggle('is-active', panel.dataset.tab === tab);
                });

                if (tabField) {
                        tabField.value = tab;
                }

                var url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
        };

        tabLinks.forEach(function (link) {
                link.addEventListener('click', function (event) {
                        event.preventDefault();
                        setActiveTab(this.dataset.tab);
                });
        });

        var initialTab = new URL(window.location.href).searchParams.get('tab') || (tabField ? tabField.value : '');
        setActiveTab(initialTab || (tabLinks[0] ? tabLinks[0].dataset.tab : ''));
})();
