<?php if (!defined('ADMIN_LOADED')) exit; ?>
<div class="mb-6">
    <h2 class="text-xl font-bold text-white flex items-center tracking-wide"><i class="fas fa-users mr-2 text-indigo-400"></i> <?php echo __('admin_users_title'); ?></h2>
    <p class="text-xs text-gray-400 mt-1"><?php echo __('admin_users_desc'); ?></p>
</div>

<!-- List of Users -->
<div class="bg-card shadow-xl rounded-xl border border-gray-800 p-6 mb-10 overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-800 text-xs">
        <thead class="bg-dark/60">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('admin_users_th_user'); ?></th>
                <th class="px-4 py-3 text-left font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('admin_users_th_steam_id'); ?></th>
                <th class="px-4 py-3 text-center font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('admin_users_th_role'); ?></th>
                <th class="px-4 py-3 text-center font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('admin_users_th_status'); ?></th>
                <th class="px-4 py-3 text-center font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('admin_users_th_actions'); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50 bg-dark/20">
            <?php if (!empty($all_users)): ?>
                <?php foreach ($all_users as $u): ?>
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <!-- Avatar & Username -->
                        <td class="px-4 py-3 whitespace-nowrap flex items-center gap-3">
                            <img src="<?php echo htmlspecialchars($u['avatar']); ?>" class="w-8 h-8 rounded-full border border-gray-700 shadow-sm" alt="avatar">
                            <span class="font-medium text-white"><a href="https://steamcommunity.com/profiles/<?php echo htmlspecialchars($u['steam_id']); ?>" target="_blank"><?php echo htmlspecialchars($u['username']); ?></a></span>
                        </td>
                        <!-- Steam ID -->
                        <td class="px-4 py-3 whitespace-nowrap font-mono text-gray-400">
                            <?php echo htmlspecialchars($u['steam_id']); ?>
                        </td>
                        <!-- Role badge -->
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <?php if ($u['role'] === 'sadmin'): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-900/50 text-red-300 border border-red-800">Superadmin</span>
                            <?php elseif ($u['role'] === 'admin'): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-900/50 text-green-300 border border-green-800">Admin</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-800 text-gray-400 border border-gray-700">User</span>
                            <?php endif; ?>
                        </td>
                        <!-- Ban Status badge -->
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <?php if ($u['is_banned'] == 1): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-600/20 text-red-400 border border-red-500/30"><?php echo __('admin_users_status_banned'); ?></span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600/20 text-emerald-400 border border-emerald-500/30"><?php echo __('admin_users_status_active'); ?></span>
                            <?php endif; ?>
                        </td>
                        <!-- Actions -->
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <?php if ($u['steam_id'] !== ADMIN_STEAM_ID): ?>
                                <button onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($u)); ?>)" 
                                        class="bg-indigo-900/30 hover:bg-indigo-900/60 text-indigo-300 px-3 py-1 rounded text-xs font-semibold border border-indigo-700/50 transition-colors">
                                    <i class="fas fa-edit mr-1"></i> <?php echo __('admin_users_btn_edit'); ?>
                                </button>
                            <?php else: ?>
                                <span class="text-gray-500 italic text-[11px]"><?php echo __('admin_users_system_admin'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic"><?php echo __('admin_users_error_no_users'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="userEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 hidden transition-opacity">
    <div class="bg-card border border-gray-800 w-full max-w-lg rounded-2xl shadow-2xl p-6 relative">
        <button onclick="closeEditUserModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors text-lg">
            <i class="fas fa-times"></i>
        </button>
        
        <h3 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2"><?php echo __('admin_users_modal_title'); ?></h3>
        
        <form action="admin?tab=users" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="user_steam_id" id="edit_user_steam_id">
            
            <!-- Display Name -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_users_label_name'); ?></label>
                <input type="text" id="edit_user_name" class="w-full bg-dark border border-gray-700 text-gray-400 rounded-md px-3 py-2 text-sm focus:outline-none cursor-not-allowed" readonly>
            </div>
            
            <!-- Role selector -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_users_label_role'); ?></label>
                <select name="role" id="edit_user_role" onchange="togglePermissionsForm()" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer">
                    <option value="user"><?php echo __('admin_users_option_user'); ?></option>
                    <option value="admin"><?php echo __('admin_users_option_admin'); ?></option>
                    <?php if ($_SESSION['role'] === 'sadmin'): ?>
                        <option value="sadmin"><?php echo __('admin_users_option_sadmin'); ?></option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Ban status -->
            <div class="flex items-center p-3 bg-red-950/20 border border-red-900/30 rounded-lg">
                <input type="checkbox" name="is_banned" id="edit_user_banned" class="w-4 h-4 bg-dark border-gray-700 rounded text-red-500 focus:ring-red-500 cursor-pointer">
                <label for="edit_user_banned" class="ml-2 text-sm font-semibold text-red-400 cursor-pointer"><?php echo __('admin_users_label_ban'); ?></label>
            </div>
            
            <!-- Permissions section -->
            <div id="permissions_section" class="border-t border-gray-800 pt-4 space-y-3">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider"><?php echo __('admin_users_label_permissions'); ?></label>
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center">
                        <input type="checkbox" name="perm_servers" id="edit_perm_servers" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                        <label for="edit_perm_servers" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __('admin_users_perm_servers'); ?></label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="perm_links" id="edit_perm_links" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                        <label for="edit_perm_links" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __('admin_users_perm_links'); ?></label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="perm_rcon" id="edit_perm_rcon" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                        <label for="edit_perm_rcon" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __('admin_users_perm_rcon'); ?></label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="perm_users" id="edit_perm_users" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                        <label for="edit_perm_users" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __('admin_users_perm_users'); ?></label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="perm_setup" id="edit_perm_setup" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                        <label for="edit_perm_setup" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __('admin_users_perm_setup'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 border-t border-gray-800 pt-4 mt-6">
                <button type="button" onclick="closeEditUserModal()" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-2 px-4 rounded-md text-sm transition-colors shadow-lg">
                    <?php echo __('admin_users_btn_cancel'); ?>
                </button>
                <button type="submit" name="update_user" class="bg-primary hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-md text-sm transition-colors shadow-lg">
                    <?php echo __('admin_users_btn_save'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUserModal(user) {
    document.getElementById('edit_user_steam_id').value = user.steam_id;
    document.getElementById('edit_user_name').value = user.username;
    document.getElementById('edit_user_role').value = user.role;
    document.getElementById('edit_user_banned').checked = user.is_banned == 1;
    
    document.getElementById('edit_perm_servers').checked = user.perm_servers == 1;
    document.getElementById('edit_perm_links').checked = user.perm_links == 1;
    document.getElementById('edit_perm_rcon').checked = user.perm_rcon == 1;
    document.getElementById('edit_perm_users').checked = user.perm_users == 1;
    document.getElementById('edit_perm_setup').checked = user.perm_setup == 1;
    
    togglePermissionsForm();
    
    const modal = document.getElementById('userEditModal');
    modal.classList.remove('hidden');
}

function closeEditUserModal() {
    const modal = document.getElementById('userEditModal');
    modal.classList.add('hidden');
}

function togglePermissionsForm() {
    const roleSelect = document.getElementById('edit_user_role');
    const permSection = document.getElementById('permissions_section');
    if (roleSelect.value === 'admin') {
        permSection.style.display = 'block';
    } else {
        permSection.style.display = 'none';
    }
}
</script>
