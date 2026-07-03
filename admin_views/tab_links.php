<?php if (!defined('ADMIN_LOADED')) exit; ?>
<div class="mb-4">
    <h2 class="text-xl font-bold text-white flex items-center tracking-wide"><i class="fas fa-link mr-2 text-emerald-500"></i> <?php echo __('admin_manage_header_links'); ?></h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    
    <!-- Left column (1/3): New Navigation Link -->
    <div class="lg:col-span-1 bg-card shadow-xl rounded-xl border border-gray-800 p-6">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-700 pb-2"><?php echo __('admin_add_link'); ?></h3>
        
        <?php if ($link_success_msg): ?>
            <div class="mb-4 bg-green-900/50 border border-green-800 text-green-400 px-3 py-2 text-xs rounded">
                <?php echo $link_success_msg; ?>
            </div>
        <?php endif; ?>
        <?php if ($link_error_msg): ?>
            <div class="mb-4 bg-red-900/50 border border-red-800 text-red-400 px-3 py-2 text-xs rounded">
                <?php echo $link_error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="admin" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_link_name'); ?></label>
                <input type="text" name="link_title" placeholder="<?php echo htmlspecialchars(__('admin_placeholder_link_title')); ?>" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary" required>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_link_url'); ?></label>
                <input type="text" name="link_url" placeholder="https://..." class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary" required>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_link_order'); ?> <span class="text-[10px] text-gray-500"><?php echo __('admin_label_link_order_desc'); ?></span></label>
                <input type="number" name="link_order" value="10" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="link_is_visible" id="link_visible" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" checked>
                <label for="link_visible" class="ml-2 text-sm text-gray-400 cursor-pointer select-none"><?php echo __('admin_th_visible'); ?></label>
            </div>
            
            <button type="submit" name="add_link" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 px-4 rounded-md transition-colors shadow-lg text-sm">
                <?php echo __('admin_btn_add_link'); ?>
            </button>
        </form>
    </div>
    
    <!-- Right column (2/3): List and update managed links -->
    <div class="lg:col-span-2 bg-card shadow-xl rounded-xl border border-gray-800 p-6 overflow-hidden">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-700 pb-2"><?php echo __('admin_managed_header_links'); ?></h3>
        
        <form action="admin" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                <?php if (count($links) > 0): ?>
                    <?php foreach ($links as $l): ?>
                        <div class="bg-dark border border-gray-800 rounded-lg p-3 flex flex-col lg:flex-row lg:items-center gap-3 hover:border-gray-700 transition-colors shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] text-gray-500 mb-1 uppercase tracking-wider font-semibold"><?php echo __('admin_th_visible'); ?></span>
                                    <input type="checkbox" name="links[<?php echo $l['id']; ?>][is_visible]" class="w-5 h-5 bg-card border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" <?php echo isset($l['is_visible']) && $l['is_visible'] ? 'checked' : ''; ?>>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-500 mb-1 uppercase tracking-wider font-semibold"><?php echo __('admin_label_order'); ?></span>
                                    <input type="number" name="links[<?php echo $l['id']; ?>][sort_order]" value="<?php echo $l['sort_order']; ?>" class="w-14 bg-card border border-gray-700 text-white rounded px-1.5 py-1 text-center text-sm focus:border-primary focus:outline-none">
                                </div>
                            </div>
                            
                            <div class="flex flex-col flex-1 gap-2 w-full">
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <div class="flex-1">
                                        <input type="text" name="links[<?php echo $l['id']; ?>][title]" value="<?php echo htmlspecialchars($l['title']); ?>" class="w-full bg-card border border-gray-700 text-white rounded px-2.5 py-1 text-sm focus:border-primary focus:outline-none font-bold" placeholder="<?php echo htmlspecialchars(__('admin_label_button_text')); ?>">
                                    </div>
                                    <div class="flex-[2]">
                                        <input type="text" name="links[<?php echo $l['id']; ?>][url]" value="<?php echo htmlspecialchars($l['url']); ?>" class="w-full bg-card border border-gray-700 text-gray-400 rounded px-2.5 py-1 text-sm font-mono focus:border-primary focus:outline-none" placeholder="<?php echo htmlspecialchars(__('admin_label_target_url')); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end lg:border-l lg:border-gray-800 lg:pl-3 mt-1 lg:mt-0">
                                <a href="admin?delete_link=<?php echo $l['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" onclick="return confirm('<?php echo addslashes(__('admin_confirm_delete_link')); ?>');" class="text-red-500 hover:text-white bg-red-900/20 hover:bg-red-600 px-3 py-1.5 rounded font-bold text-xs transition-colors whitespace-nowrap flex items-center shadow-sm">
                                    <?php echo __('admin_btn_delete'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500 bg-dark rounded-lg border border-gray-800 italic"><?php echo __('admin_no_links'); ?></div>
                <?php endif; ?>
            </div>
            
            <?php if (count($links) > 0): ?>
                <div class="mt-4 flex justify-end">
                    <button type="submit" name="update_links" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-1.5 px-4 rounded text-sm transition-colors shadow-lg">
                        <?php echo __('admin_btn_save_changes'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ================= SECTION 3: SOCIAL LINKS ================= -->
<div class="border-t border-gray-800 my-8"></div>
<div class="mb-4">
    <h2 class="text-xl font-bold text-white flex items-center tracking-wide"><i class="fas fa-share-nodes mr-2 text-blue-500"></i> <?php echo __('admin_manage_social_links'); ?></h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    
    <!-- Left column (1/3): New Social Link -->
    <div class="lg:col-span-1 bg-card shadow-xl rounded-xl border border-gray-800 p-6">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-700 pb-2"><?php echo __('admin_add_social_link'); ?></h3>
        
        <form action="admin" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_link_name'); ?></label>
                <input type="text" name="social_title" placeholder="<?php echo htmlspecialchars(__('admin_placeholder_link_title')); ?>" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary" required>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_link_url'); ?></label>
                <input type="text" name="social_url" placeholder="https://..." class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary" required>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_icon'); ?></label>
                <select name="social_icon" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary cursor-pointer">
                    <option value="fab fa-twitch">Twitch</option>
                    <option value="fab fa-youtube">YouTube</option>
                    <option value="fab fa-discord">Discord</option>
                    <option value="fab fa-instagram">Instagram</option>
                    <option value="fab fa-tiktok">TikTok</option>
                    <option value="fab fa-steam">Steam</option>
                    <option value="fas fa-hand-holding-dollar"><?php echo __('admin_option_donate'); ?></option>
                    <option value="fas fa-envelope">Email</option>
                    <option value="fas fa-link"><?php echo __('admin_option_general_link'); ?></option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_order'); ?></label>
                <input type="number" name="social_order" value="10" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="social_is_visible" id="social_visible" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" checked>
                <label for="social_visible" class="ml-2 text-sm text-gray-400 cursor-pointer select-none"><?php echo __('admin_th_visible'); ?></label>
            </div>
            
            <button type="submit" name="add_social_link" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-md transition-colors shadow-lg text-sm">
                <?php echo __('admin_btn_add_social_link'); ?>
            </button>
        </form>
    </div>
    
    <!-- Right column (2/3): List and update managed social links -->
    <div class="lg:col-span-2 bg-card shadow-xl rounded-xl border border-gray-800 p-6 overflow-hidden">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-700 pb-2"><?php echo __('admin_managed_social_links'); ?></h3>
        
        <form action="admin" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                <?php if (count($social_links) > 0): ?>
                    <?php foreach ($social_links as $l): ?>
                        <div class="bg-dark border border-gray-800 rounded-lg p-3 flex flex-col lg:flex-row lg:items-center gap-3 hover:border-gray-700 transition-colors shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] text-gray-500 mb-1 uppercase tracking-wider font-semibold"><?php echo __('admin_th_visible'); ?></span>
                                        <input type="checkbox" name="social_links[<?php echo $l['id']; ?>][is_visible]" class="w-5 h-5 bg-card border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" <?php echo isset($l['is_visible']) && $l['is_visible'] ? 'checked' : ''; ?>>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-gray-500 mb-1 uppercase tracking-wider font-semibold"><?php echo __('admin_label_order'); ?></span>
                                        <input type="number" name="social_links[<?php echo $l['id']; ?>][sort_order]" value="<?php echo $l['sort_order']; ?>" class="w-14 bg-card border border-gray-700 text-white rounded px-1.5 py-1 text-center text-sm focus:border-primary focus:outline-none">
                                    </div>
                                </div>
                                
                                <div class="flex flex-col min-w-[130px] w-full sm:w-auto">
                                    <span class="text-[10px] text-gray-500 mb-1 uppercase tracking-wider font-semibold"><?php echo __('admin_label_icon'); ?></span>
                                    <div class="flex items-center space-x-2 bg-card border border-gray-700 rounded px-2 h-[34px]">
                                        <i class="<?php echo htmlspecialchars($l['icon']); ?> text-md w-5 text-center text-gray-400"></i>
                                        <select name="social_links[<?php echo $l['id']; ?>][icon]" class="w-full bg-transparent border-none text-white text-xs focus:ring-0 px-1 py-1 outline-none cursor-pointer">
                                            <option value="fab fa-twitch" <?php if($l['icon']=='fab fa-twitch') echo 'selected'; ?>>Twitch</option>
                                            <option value="fab fa-youtube" <?php if($l['icon']=='fab fa-youtube') echo 'selected'; ?>>YouTube</option>
                                            <option value="fab fa-discord" <?php if($l['icon']=='fab fa-discord') echo 'selected'; ?>>Discord</option>
                                            <option value="fab fa-instagram" <?php if($l['icon']=='fab fa-instagram') echo 'selected'; ?>>Instagram</option>
                                            <option value="fab fa-tiktok" <?php if($l['icon']=='fab fa-tiktok') echo 'selected'; ?>>TikTok</option>
                                            <option value="fab fa-steam" <?php if($l['icon']=='fab fa-steam') echo 'selected'; ?>>Steam</option>
                                            <option value="fas fa-hand-holding-dollar" <?php if($l['icon']=='fas fa-hand-holding-dollar') echo 'selected'; ?>>Donate</option>
                                            <option value="fas fa-envelope" <?php if($l['icon']=='fas fa-envelope') echo 'selected'; ?>>Email</option>
                                            <option value="fas fa-link" <?php if($l['icon']=='fas fa-link') echo 'selected'; ?>>Link</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col flex-1 w-full gap-2">
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <div class="flex-1">
                                            <input type="text" name="social_links[<?php echo $l['id']; ?>][title]" value="<?php echo htmlspecialchars($l['title']); ?>" class="w-full bg-card border border-gray-700 text-white rounded px-2.5 py-1 text-sm focus:border-primary focus:outline-none font-bold" placeholder="<?php echo htmlspecialchars(__('admin_label_button_name')); ?>">
                                        </div>
                                        <div class="flex-[2]">
                                            <input type="text" name="social_links[<?php echo $l['id']; ?>][url]" value="<?php echo htmlspecialchars($l['url']); ?>" class="w-full bg-card border border-gray-700 text-gray-400 rounded px-2.5 py-1 text-sm font-mono focus:border-primary focus:outline-none" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end lg:border-l lg:border-gray-800 lg:pl-3 mt-1 lg:mt-0">
                                    <a href="admin?delete_social_link=<?php echo $l['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" onclick="return confirm('<?php echo addslashes(__('admin_confirm_delete_social')); ?>');" class="text-red-500 hover:text-white bg-red-900/20 hover:bg-red-600 px-3 py-1.5 rounded font-bold text-xs transition-colors whitespace-nowrap flex items-center shadow-sm">
                                        <?php echo __('admin_btn_delete'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500 bg-dark rounded-lg border border-gray-800 italic"><?php echo __('admin_no_social'); ?></div>
                <?php endif; ?>
            </div>
            
            <?php if (count($social_links) > 0): ?>
                <div class="mt-4 flex justify-end">
                    <button type="submit" name="update_social_links" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-1.5 px-4 rounded text-sm transition-colors shadow-lg">
                        <?php echo __('admin_btn_save_modules'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
