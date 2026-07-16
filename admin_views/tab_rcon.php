<?php if (!defined('ADMIN_LOADED')) exit; ?>
<div class="mb-6">
    <h2 class="text-xl font-bold text-white flex items-center tracking-wide"><i class="fas fa-terminal mr-2 text-yellow-500"></i> <?php echo __('admin_rcon_title'); ?></h2>
    <p class="text-xs text-gray-400 mt-1"><?php echo __('admin_rcon_desc'); ?></p>
</div>

<div class="bg-card shadow-xl rounded-xl border border-gray-800 p-6 mb-10">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Left side: Selection -->
        <div class="lg:col-span-1 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_rcon_label_target'); ?></label>
                <select id="rcon_server_select" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer">
                    <option value=""><?php echo __('admin_rcon_select_server'); ?></option>
                    <?php 
                    $has_rcon_servers = false;
                    foreach ($servers as $s): 
                        if (in_array($s['game'], ['cod2', 'cod4', 'mw2', 'mw3', 'cs2', 'dayz']) && !empty($s['rcon_password'])):
                            $has_rcon_servers = true;
                    ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?> (<?php echo strtoupper($s['game']); ?>)</option>
                    <?php 
                        endif;
                    endforeach; 
                    
                    if (!$has_rcon_servers):
                    ?>
                        <option value="" disabled><?php echo __('admin_rcon_no_servers'); ?></option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"><?php echo __('admin_rcon_common_commands'); ?></label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="quickCommand('status')" class="px-2.5 py-1.5 bg-dark border border-gray-700 hover:border-gray-500 rounded text-xs text-gray-300 transition-colors font-mono">status</button>
                    <button type="button" onclick="quickCommand('map_rotate')" class="px-2.5 py-1.5 bg-dark border border-gray-700 hover:border-gray-500 rounded text-xs text-gray-300 transition-colors font-mono">map_rotate</button>
                    <button type="button" onclick="quickCommand('serverinfo')" class="px-2.5 py-1.5 bg-dark border border-gray-700 hover:border-gray-500 rounded text-xs text-gray-300 transition-colors font-mono">serverinfo</button>
                </div>
            </div>
        </div>
        
        <!-- Right side: Console and input field -->
        <div class="lg:col-span-3 flex flex-col space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_rcon_terminal_output'); ?></label>
                <div id="rcon_terminal" class="w-full h-80 bg-black border border-gray-800 rounded-lg p-4 font-mono text-xs text-gray-200 overflow-y-auto leading-relaxed shadow-inner space-y-1">
                    <div class="text-gray-500 italic"><?php echo __('admin_rcon_select_first'); ?></div>
                </div>
            </div>
            
            <form id="rcon_form" onsubmit="submitRcon(event)" class="flex gap-4 items-center">
                <input type="hidden" id="rcon_csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="flex-1">
                    <input type="text" id="rcon_command_input" placeholder="<?php echo htmlspecialchars(__('admin_rcon_command_placeholder')); ?>" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-primary font-mono" disabled>
                </div>
                <button type="submit" id="rcon_send_btn" class="bg-primary hover:bg-blue-600 text-white font-bold py-2.5 px-6 rounded-md transition-colors shadow-lg text-sm flex items-center justify-center min-w-[100px]" disabled>
                    <span><?php echo __('admin_rcon_btn_send'); ?></span>
                </button>
            </form>
        </div>
        
    </div>
</div>

<script>
// RCON AJAX Handler functions
const rconSelect = document.getElementById('rcon_server_select');
const rconInput = document.getElementById('rcon_command_input');
const rconBtn = document.getElementById('rcon_send_btn');
const rconTerminal = document.getElementById('rcon_terminal');

if (rconSelect) {
    rconSelect.addEventListener('change', function() {
        const hasServer = this.value !== "";
        rconInput.disabled = !hasServer;
        rconBtn.disabled = !hasServer;
        if (hasServer) {
            rconTerminal.innerHTML = `<div class="text-gray-500">${escapeHtml("<?php echo addslashes(__('admin_rcon_ready')); ?>")}</div>`;
        } else {
            rconTerminal.innerHTML = `<div class="text-gray-500">${escapeHtml("<?php echo addslashes(__('admin_rcon_select_first')); ?>")}</div>`;
        }
    });
}

function quickCommand(cmd) {
    if (rconSelect.value === "") {
        alert("<?php echo addslashes(__('admin_rcon_alert_select')); ?>");
        return;
    }
    rconInput.value = cmd;
    document.getElementById('rcon_form').dispatchEvent(new Event('submit'));
}

function submitRcon(e) {
    e.preventDefault();
    const serverId = rconSelect.value;
    const command = rconInput.value.trim();
    const csrf = document.getElementById('rcon_csrf').value;
    
    if (serverId === "" || command === "") return;
    
    const time = new Date().toLocaleTimeString();
    rconTerminal.innerHTML += `<div><span class="text-blue-400">[${time}] ></span> <span class="text-white font-bold">${escapeHtml(command)}</span></div>`;
    rconTerminal.scrollTop = rconTerminal.scrollHeight;
    
    rconInput.disabled = true;
    rconBtn.disabled = true;
    const originalText = rconBtn.innerHTML;
    rconBtn.innerHTML = `<span><?php echo addslashes(__('admin_rcon_btn_send')); ?>...</span>`;
    
    const formData = new FormData();
    formData.append('server_id', serverId);
    formData.append('command', command);
    formData.append('csrf_token', csrf);
    
    fetch('rcon_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const timeResponse = data.timestamp || new Date().toLocaleTimeString();
        if (data.status === 'success') {
            rconTerminal.innerHTML += `<div class="mt-1"><span class="text-emerald-500">[${timeResponse}]</span><br>${data.message}</div>`;
        } else {
            rconTerminal.innerHTML += `<div class="mt-1 text-red-500 font-semibold">[${timeResponse}] Hiba: ${escapeHtml(data.message)}</div>`;
        }
        rconTerminal.scrollTop = rconTerminal.scrollHeight;
        
        rconInput.disabled = false;
        rconBtn.disabled = false;
        rconBtn.innerHTML = originalText;
        rconInput.value = "";
        rconInput.focus();
    })
    .catch(err => {
        rconTerminal.innerHTML += `<div class="mt-1 text-red-500 font-semibold">${escapeHtml("<?php echo addslashes(__('admin_rcon_net_error')); ?>")}</div>`;
        rconTerminal.scrollTop = rconTerminal.scrollHeight;
        rconInput.disabled = false;
        rconBtn.disabled = false;
        rconBtn.innerHTML = originalText;
    });
}

function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
