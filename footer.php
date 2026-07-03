<!-- Toast notification container -->
<div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-3"></div>

<script>
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    
    // Base Tailwind classes for the toast (with animation and colors)
    toast.className = `transform transition-all duration-300 translate-y-10 opacity-0 px-4 py-3 rounded shadow-lg text-white font-medium flex items-center gap-2 ${type === 'success' ? 'bg-emerald-600' : 'bg-red-600'}`;
    
    // Add icon
    const icon = type === 'success' 
        ? `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`
        : `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
        
    toast.innerHTML = `${icon} <span>${message}</span>`;
    
    container.appendChild(toast);
    
    // Show animation (a small delay is needed for CSS transition to work)
    setTimeout(() => {
        toast.classList.remove('translate-y-10', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    }, 10);
    
    // Fade out animation then delete after 3 seconds
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-10', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Copy IP helper function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('<?php echo addslashes(__('toast_ip_copied')); ?> ' + text, 'success');
    }).catch(err => {
        showToast('<?php echo addslashes(__('toast_copy_failed')); ?>', 'error');
    });
}

// Connect and copy IP for client launcher links (e.g. iw4x, plutonium)
function connectAndCopy(ipPort, protocol) {
    navigator.clipboard.writeText(ipPort).then(() => {
        showToast('<?php echo addslashes(__('toast_ip_copied')); ?> ' + ipPort + ' <?php echo addslashes(__('toast_launching_game')); ?>', 'success');
        setTimeout(() => {
            window.location.href = protocol + '://connect/' + ipPort;
        }, 150);
    }).catch(err => {
        showToast('<?php echo addslashes(__('toast_copy_failed')); ?>', 'error');
        window.location.href = protocol + '://connect/' + ipPort;
    });
}
</script>

<footer class="bg-dark border-t border-gray-800 py-6 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="text-gray-400 text-sm">
            &copy; 2026-<?php echo date('Y') > 2026 ? date('Y') : '2026'; ?> <?php echo htmlspecialchars(TWITCH_USERNAME); ?>. <?php echo __('footer_rights'); ?>
            <?php 
                $site_version = $settings['site_version'] ?? (isset($pdo) ? get_setting_value($pdo, 'site_version', '') : '');
                if (!empty($site_version)): 
            ?>
                <span class="text-gray-600 text-xs ml-2">v<?php echo htmlspecialchars($site_version); ?></span>
            <?php endif; ?>
        </div>
        <div class="text-gray-500 text-xs">
            <?php echo __('footer_developed'); ?> <a href="https://github.com/markovicsmarko" target="_blank" class="text-primary hover:text-blue-400 transition-colors font-bold">Markó</a>
        </div>
    </div>
</footer>

<?php if (!isset($_COOKIE['cookie_consent'])): ?>
<!-- Cookie Consent banner -->
<div id="cookie-consent-banner" class="fixed bottom-5 left-5 z-40 max-w-sm w-[calc(100%-2.5rem)] sm:w-80 bg-card border border-gray-800 p-5 rounded-2xl shadow-2xl transition-all duration-500 transform translate-y-20 opacity-0">
    <div class="flex items-center gap-2 mb-2 text-white">
        <svg class="w-5 h-5 text-yellow-600" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm1.5 5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm-5 2a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm1.5 6.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm5.5-.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/>
        </svg>
        <span class="font-bold text-sm"><?php echo __('cookie_consent_title'); ?></span>
    </div>
    <p class="text-gray-400 text-xs leading-relaxed mb-4">
        <?php echo __('cookie_consent_text'); ?>
    </p>
    <button onclick="acceptCookies()" class="w-full py-2 bg-primary hover:bg-blue-600 text-white font-bold rounded-lg text-xs transition-colors shadow-lg shadow-blue-500/20">
        <?php echo __('cookie_consent_accept'); ?>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const banner = document.getElementById('cookie-consent-banner');
    if (banner) {
        setTimeout(() => {
            banner.classList.remove('translate-y-20', 'opacity-0');
            banner.classList.add('translate-y-0', 'opacity-100');
        }, 500);
    }
});

function acceptCookies() {
    const banner = document.getElementById('cookie-consent-banner');
    const d = new Date();
    d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000)); // 365 days
    const expires = "expires=" + d.toUTCString();
    document.cookie = "cookie_consent=1; " + expires + "; path=/; SameSite=Lax";
    
    if (banner) {
        banner.classList.remove('translate-y-0', 'opacity-100');
        banner.classList.add('translate-y-20', 'opacity-0');
        setTimeout(() => banner.remove(), 500);
    }
}
</script>
<?php endif; ?>

</body>
</html>
