<?php
// Connect and load header
require_once 'db.php';
require_once 'header.php';

// Load system settings for specifications
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $settings = [];
}
?>

<main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-10 text-center">
        <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-primary to-blue-300 mb-4 tracking-wider uppercase drop-shadow-sm"><?php echo __('setup_title'); ?></h1>
        <p class="text-gray-400 max-w-2xl mx-auto"><?php echo __('setup_desc'); ?></p>
    </div>

    <!-- Setup categories grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- 1. PC Internal Components -->
        <div class="bg-card shadow-2xl rounded-2xl border border-gray-800 overflow-hidden transform transition-all hover:border-gray-700">
            <div class="bg-dark border-b border-gray-800 px-6 py-4 flex items-center">
                <div class="bg-blue-900/50 p-2 rounded-lg mr-4">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white tracking-wide"><?php echo __('setup_pc_internal'); ?></h2>
            </div>
            
            <div class="p-6">
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <span class="text-primary font-bold w-32 shrink-0"><?php echo __('spec_cpu'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_cpu'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-primary font-bold w-32 shrink-0"><?php echo __('spec_gpu'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_gpu'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-primary font-bold w-32 shrink-0"><?php echo __('spec_ram'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_ram'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-primary font-bold w-32 shrink-0"><?php echo __('spec_mobo'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_mobo'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-primary font-bold w-32 shrink-0"><?php echo __('spec_psu'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_psu'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-primary font-bold w-32 shrink-0"><?php echo __('spec_case'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_case'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-primary font-bold w-32 shrink-0"><?php echo __('spec_storage'); ?></span>
                        <div class="flex flex-col space-y-1">
                            <span class="text-gray-300 bg-gray-800/50 px-2 py-1 rounded text-sm"><?php echo htmlspecialchars($settings['setup_storage1'] ?? ''); ?></span>
                            <span class="text-gray-300 bg-gray-800/50 px-2 py-1 rounded text-sm"><?php echo htmlspecialchars($settings['setup_storage2'] ?? ''); ?></span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 2. Peripherals -->
        <div class="bg-card shadow-2xl rounded-2xl border border-gray-800 overflow-hidden transform transition-all hover:border-gray-700">
            <div class="bg-dark border-b border-gray-800 px-6 py-4 flex items-center">
                <div class="bg-purple-900/50 p-2 rounded-lg mr-4">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-white tracking-wide"><?php echo __('setup_peripherals'); ?></h2>
            </div>
            
            <div class="p-6">
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <span class="text-purple-400 font-bold w-32 shrink-0"><?php echo __('spec_monitor1'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_monitor1'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-purple-400 font-bold w-32 shrink-0"><?php echo __('spec_monitor2'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_monitor2'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-purple-400 font-bold w-32 shrink-0"><?php echo __('spec_keyboard'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_keyboard'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-purple-400 font-bold w-32 shrink-0"><?php echo __('spec_mouse'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_mouse'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-purple-400 font-bold w-32 shrink-0"><?php echo __('spec_mousepad'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_mousepad'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-purple-400 font-bold w-32 shrink-0"><?php echo __('spec_headset'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_headset'] ?? ''); ?></span>
                    </li>
                    <li class="flex items-start border-t border-gray-800/50 pt-4">
                        <span class="text-purple-400 font-bold w-32 shrink-0"><?php echo __('spec_mic'); ?></span>
                        <span class="text-gray-300"><?php echo htmlspecialchars($settings['setup_mic'] ?? ''); ?></span>
                    </li>
                </ul>
            </div>
        </div>

    </div>

</main>

<?php require_once 'footer.php'; ?>
