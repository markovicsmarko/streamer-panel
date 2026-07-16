<?php if (!defined('ADMIN_LOADED')) exit; ?>
<div class="mb-4">
    <h2 class="text-xl font-bold text-white flex items-center tracking-wide"><i class="fas fa-server mr-2 text-primary"></i> <?php echo __('admin_manage_servers'); ?></h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    
    <!-- Left column (1/3): Add New Server and Edit RCON -->
    <div class="lg:col-span-1 space-y-8">
        <div class="bg-card shadow-xl rounded-xl border border-gray-800 p-6">
            <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-700 pb-2"><?php echo __('admin_add_server'); ?></h3>
        
        <form action="admin?tab=servers" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_game_type'); ?></label>
                <select name="game" id="game_select" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer" required onchange="toggleDiscordFields()">
                    <option value="discord"><?php echo __('option_discord'); ?></option>
                    <option value="cs2"><?php echo __('option_cs2'); ?></option>
                    <option value="dayz"><?php echo __('option_dayz'); ?></option>
                    <option value="cod2"><?php echo __('option_cod2'); ?></option>
                    <option value="cod4"><?php echo __('option_cod4'); ?></option>
                    <option value="mw2"><?php echo __('option_mw2'); ?></option>
                    <option value="mw3"><?php echo __('option_mw3'); ?></option>
                    <option value="minecraft"><?php echo __('option_minecraft'); ?></option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_server_name'); ?></label>
                <input type="text" name="name" placeholder="<?php echo htmlspecialchars(__('admin_placeholder_server_name')); ?>" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" required>
            </div>

            <div id="discord_field" style="display: none;">
                <label class="block text-xs font-semibold text-blue-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_discord_invite'); ?></label>
                <input type="text" name="invite_link" placeholder="https://discord.gg/abcd123" class="w-full bg-dark border border-blue-800 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-blue-500 font-mono">
            </div>
            
            <div id="ip_port_fields">
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_ip'); ?></label>
                    <input type="text" name="ip" id="server_ip" placeholder="<?php echo htmlspecialchars(__('admin_placeholder_ip')); ?>" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary font-mono">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_label_port'); ?></label>
                    <input type="number" name="port" id="server_port" placeholder="27015" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary font-mono">
                </div>
            </div>
            <div id="rcon_field" style="display: none;" class="mt-4">
                <label class="block text-xs font-semibold text-yellow-500 uppercase tracking-wider mb-1"><?php echo __('admin_rcon_label_password_opt'); ?></label>
                <input type="password" name="rcon_password" placeholder="<?php echo htmlspecialchars(__('admin_rcon_placeholder')); ?>" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary font-mono">
            </div>
            
            <div class="flex items-center mt-2">
                <input type="checkbox" name="is_visible" id="server_visible" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" checked>
                <label for="server_visible" class="ml-2 text-sm text-gray-400 cursor-pointer select-none"><?php echo __('admin_label_visible'); ?></label>
            </div>
            
            <button type="submit" name="add_server" class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition-colors shadow-lg mt-4 text-sm">
                <?php echo __('admin_btn_add_server'); ?>
            </button>
        </form>
    </div>

    <?php if ($edit_server && in_array($edit_server['game'], ['cod2', 'cod4', 'mw2', 'mw3', 'cs2', 'dayz'])): ?>
    <!-- Edit RCON Password Card -->
    <div class="bg-card shadow-xl rounded-xl border border-yellow-800/50 p-6">
        <h3 class="text-lg font-bold text-white mb-4 border-b border-yellow-700/50 pb-2 flex items-center justify-between">
            <span><?php echo __('admin_rcon_edit_title'); ?></span>
            <span class="text-xs bg-yellow-900/50 text-yellow-400 border border-yellow-800 px-2 py-0.5 rounded uppercase font-mono"><?php echo htmlspecialchars($edit_server['game']); ?></span>
        </h3>
        <p class="text-xs text-gray-400 mb-4 font-semibold"><?php echo __('admin_rcon_edit_desc', ['{server}' => htmlspecialchars($edit_server['name'])]); ?></p>
        <form action="admin?tab=servers" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="server_id" value="<?php echo $edit_server['id']; ?>">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_rcon_label_password'); ?></label>
                <input type="password" name="rcon_password" value="<?php echo htmlspecialchars($edit_server['rcon_password'] ?? ''); ?>" placeholder="<?php echo htmlspecialchars(__('admin_rcon_edit_placeholder')); ?>" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary font-mono">
            </div>
            <div class="flex gap-4">
                <button type="submit" name="update_rcon" class="flex-1 bg-yellow-600 hover:bg-yellow-500 text-white font-bold py-2 px-4 rounded-md transition-colors shadow-lg text-sm">
                    <?php echo __('admin_rcon_btn_save'); ?>
                </button>
                <a href="admin?tab=servers" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-2 px-4 rounded-md transition-colors text-sm text-center border border-gray-700">
                    <?php echo __('admin_rcon_btn_cancel'); ?>
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
    
    <!-- Right column (2/3): List of Managed Servers -->
    <div class="lg:col-span-2 bg-card shadow-xl rounded-xl border border-gray-800 p-6 overflow-hidden flex flex-col justify-between">
        <div>
            <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-700 pb-2"><?php echo __('admin_managed_servers'); ?> (<?php echo count($servers); ?>)</h3>
            
            <div class="overflow-x-auto max-h-[380px] overflow-y-auto pr-1">
                <table class="min-w-full divide-y divide-gray-800">
                    <thead class="sticky top-0 bg-card z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider w-16"><?php echo __('admin_th_visible'); ?></th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('admin_th_server'); ?></th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('admin_th_connection'); ?></th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('admin_th_action'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        <?php if (count($servers) > 0): ?>
                            <?php foreach ($servers as $server): ?>
                                <tr class="hover:bg-gray-800/30 <?php echo (!isset($server['is_visible']) || $server['is_visible']) ? '' : 'opacity-50'; ?> transition-colors">
                                    <td class="px-4 py-3 text-center">
                                        <a href="admin?toggle_server=<?php echo $server['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="inline-block p-1 rounded hover:bg-gray-700 transition-colors" title="<?php echo htmlspecialchars(__('admin_title_toggle_visibility')); ?>">
                                            <?php if (!isset($server['is_visible']) || $server['is_visible']): ?>
                                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            <?php else: ?>
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($server['name']); ?></div>
                                        <div class="text-xs <?php echo $server['game'] === 'discord' ? 'text-blue-400 font-bold' : 'text-gray-500'; ?> uppercase">
                                            <?php echo htmlspecialchars($server['game']); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300 font-mono">
                                        <?php if ($server['game'] === 'discord'): ?>
                                            <span class="text-xs text-blue-300"><?php echo htmlspecialchars($server['invite_link']); ?></span>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($server['ip'] . ':' . $server['port']); ?>
                                            <?php if (!empty($server['rcon_password'])): ?>
                                                <span class="text-emerald-500 ml-1.5" title="<?php echo htmlspecialchars(__('admin_title_rcon_set')); ?>"><i class="fas fa-key text-[10px]"></i></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <?php if (in_array($server['game'], ['cod2', 'cod4', 'mw2', 'mw3', 'cs2', 'dayz'])): ?>
                                                <a href="admin?tab=servers&edit_rcon=<?php echo $server['id']; ?>" class="text-yellow-500 hover:text-yellow-400 bg-yellow-900/20 px-2 py-1 rounded hover:bg-yellow-900/40 inline-block text-xs font-bold transition-colors" title="<?php echo htmlspecialchars(__('admin_title_rcon_edit')); ?>">
                                                    <i class="fas fa-key text-[10px]"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="admin?tab=servers&delete=<?php echo $server['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" onclick="return confirm('<?php echo addslashes(__('admin_confirm_delete_server')); ?>');" class="text-red-500 hover:text-red-400 bg-red-900/20 px-3 py-1 rounded hover:bg-red-900/40 inline-block text-xs font-bold transition-colors">
                                                <?php echo __('admin_btn_delete'); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic">
                                    <?php echo __('admin_no_servers'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDiscordFields() {
    const select = document.getElementById('game_select');
    if (!select) return;
    const game = select.value;
    const discordField = document.getElementById('discord_field');
    const ipPortFields = document.getElementById('ip_port_fields');
    const rconField = document.getElementById('rcon_field');
    const ipInput = document.getElementById('server_ip');
    const portInput = document.getElementById('server_port');
    
    const rconGames = ['cod2', 'cod4', 'mw2', 'mw3', 'cs2', 'dayz'];
    
    if (game === 'discord') {
        if (discordField) discordField.style.display = 'block';
        if (ipPortFields) ipPortFields.style.display = 'none';
        if (rconField) rconField.style.display = 'none';
        if (ipInput) ipInput.required = false;
        if (portInput) portInput.required = false;
    } else {
        if (discordField) discordField.style.display = 'none';
        if (ipPortFields) ipPortFields.style.display = 'block';
        if (ipInput) ipInput.required = true;
        if (portInput) portInput.required = true;
        
        if (rconGames.includes(game)) {
            if (rconField) rconField.style.display = 'block';
        } else {
            if (rconField) rconField.style.display = 'none';
        }
    }
}
document.addEventListener('DOMContentLoaded', toggleDiscordFields);
</script>
