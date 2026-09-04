<script>
    (function () {
        let installPrompt = null;
        const serviceWorkerUrl = @json(app_nav_url('service-worker.js'));

        if ('serviceWorker' in navigator && window.isSecureContext) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register(serviceWorkerUrl).catch(function () {});
            });
        }

        window.addEventListener('beforeinstallprompt', function (event) {
            event.preventDefault();
            installPrompt = event;
            showInstallButton();
        });

        document.addEventListener('DOMContentLoaded', function () {
            const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

            if (isIos && !isStandalone) {
                showInstallButton();
            }
        });

        window.addEventListener('appinstalled', function () {
            document.getElementById('pwaInstallButton')?.remove();
        });

        function showInstallButton() {
            if (document.getElementById('pwaInstallButton')) {
                return;
            }

            const button = document.createElement('button');
            button.id = 'pwaInstallButton';
            button.type = 'button';
            button.className = 'btn btn-sm pwa-install-button';
            button.innerHTML = '<i class="fas fa-mobile-alt mr-1"></i> Instalar ACI';
            button.addEventListener('click', installApplication);
            document.body.appendChild(button);
        }

        function installApplication() {
            if (installPrompt) {
                installPrompt.prompt();
                installPrompt.userChoice.finally(function () {
                    installPrompt = null;
                    document.getElementById('pwaInstallButton')?.remove();
                });
                return;
            }

            const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
            if (isIos) {
                const message = 'En Safari, toca Compartir y selecciona "Agregar a pantalla de inicio".';
                if (window.Swal) {
                    Swal.fire({ icon: 'info', title: 'Instalar ACI Notas', text: message, confirmButtonColor: '#820005' });
                } else {
                    window.alert(message);
                }
            }
        }
    })();
</script>
