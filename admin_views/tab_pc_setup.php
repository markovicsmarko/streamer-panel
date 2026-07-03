<?php if (!defined('ADMIN_LOADED')) exit; ?>
<!-- Edit PC Setup settings card -->
<div class="bg-card shadow-xl rounded-xl border border-gray-800 p-6 mb-10">
    <h2 class="text-lg font-bold text-white mb-4 flex items-center border-b border-gray-700 pb-2">
        <i class="fas fa-desktop mr-2 text-primary"></i> <?php echo __('admin_setup_specs_title'); ?>
    </h2>
    <form action="admin?tab=pc_setup" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- CPU -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_cpu'); ?></label>
                <input type="text" name="setup_cpu" value="<?php echo htmlspecialchars($settings['setup_cpu'] ?? 'AMD Ryzen 9 990X3D'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- GPU -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_gpu'); ?></label>
                <input type="text" name="setup_gpu" value="<?php echo htmlspecialchars($settings['setup_gpu'] ?? 'EVGA RTX 3060 12 GB'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- RAM -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_ram'); ?></label>
                <input type="text" name="setup_ram" value="<?php echo htmlspecialchars($settings['setup_ram'] ?? 'Kingston FURY Renegade 32GB (2x16GB) DDR5 6400MHz'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Motherboard -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_mobo'); ?></label>
                <input type="text" name="setup_mobo" value="<?php echo htmlspecialchars($settings['setup_mobo'] ?? 'ASUS ROG STRIX X870-F GAMING'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- PSU -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_psu'); ?></label>
                <input type="text" name="setup_psu" value="<?php echo htmlspecialchars($settings['setup_psu'] ?? 'Fractal Design 760W ION+ Platinum'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Case -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_case'); ?></label>
                <input type="text" name="setup_case" value="<?php echo htmlspecialchars($settings['setup_case'] ?? 'Sharkoon TG4 RGB'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Storage 1 -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_storage1'); ?></label>
                <input type="text" name="setup_storage1" value="<?php echo htmlspecialchars($settings['setup_storage1'] ?? 'Kingston A2000 1TB (2200/2000) M.2 SSD'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Storage 2 -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_storage2'); ?></label>
                <input type="text" name="setup_storage2" value="<?php echo htmlspecialchars($settings['setup_storage2'] ?? '1000 GB SSHD (FireCuda)'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Monitor 1 -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_monitor1'); ?></label>
                <input type="text" name="setup_monitor1" value="<?php echo htmlspecialchars($settings['setup_monitor1'] ?? 'ASUS VP249QGR, IPS, 23.8", 1ms, 144Hz, Full HD, FreeSync'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Monitor 2 -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_monitor2'); ?></label>
                <input type="text" name="setup_monitor2" value="<?php echo htmlspecialchars($settings['setup_monitor2'] ?? ''); ?>" placeholder="<?php echo htmlspecialchars(__('admin_setup_monitor2_placeholder')); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Keyboard -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_keyboard'); ?></label>
                <input type="text" name="setup_keyboard" value="<?php echo htmlspecialchars($settings['setup_keyboard'] ?? 'Razer Huntsman v2 Tenkeyless'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Mouse -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_mouse'); ?></label>
                <input type="text" name="setup_mouse" value="<?php echo htmlspecialchars($settings['setup_mouse'] ?? 'Razer Viper Mini'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Mousepad -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_mousepad'); ?></label>
                <input type="text" name="setup_mousepad" value="<?php echo htmlspecialchars($settings['setup_mousepad'] ?? 'Dream Machine XL'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Headset -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_headset'); ?></label>
                <input type="text" name="setup_headset" value="<?php echo htmlspecialchars($settings['setup_headset'] ?? 'HyperX Cloud III'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            <!-- Microphone -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_setup_mic'); ?></label>
                <input type="text" name="setup_mic" value="<?php echo htmlspecialchars($settings['setup_mic'] ?? 'Yenkee YMC 1030'); ?>" 
                       class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
        </div>
        
        <div class="mt-6 flex justify-end">
            <button type="submit" name="save_setup_specs" class="bg-primary hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-md transition-colors shadow-lg text-sm">
                <i class="fas fa-save mr-2"></i> <?php echo __('admin_setup_specs_btn_save'); ?>
            </button>
        </div>
    </form>
</div>
