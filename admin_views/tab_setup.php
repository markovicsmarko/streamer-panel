<?php if (!defined('ADMIN_LOADED')) exit; ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    
    <!-- Left column (1/3): System Settings -->
    <div class="lg:col-span-1 bg-card shadow-xl rounded-xl border border-gray-800 p-6 flex flex-col justify-between">
        <div>
            <h2 class="text-lg font-bold text-white mb-4 flex items-center border-b border-gray-700 pb-2">
                <i class="fas fa-sliders-h mr-2 text-primary"></i> <?php echo __('admin_modules_visibility'); ?>
            </h2>
            
            <form action="admin?tab=setup" method="POST" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="flex items-center">
                    <input type="checkbox" name="show_social" id="sett_social" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" <?php echo $sys_show_social ? 'checked' : ''; ?>>
                    <label for="sett_social" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300 transition-colors"><?php echo __('admin_label_social'); ?></label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="show_twitch" id="sett_twitch" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" <?php echo $sys_show_twitch ? 'checked' : ''; ?>>
                    <label for="sett_twitch" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300 transition-colors"><?php echo __('admin_label_twitch'); ?></label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="show_youtube" id="sett_youtube" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" <?php echo $sys_show_youtube ? 'checked' : ''; ?>>
                    <label for="sett_youtube" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300 transition-colors"><?php echo __('admin_label_youtube'); ?></label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="show_servers" id="sett_servers" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" <?php echo $sys_show_servers ? 'checked' : ''; ?>>
                    <label for="sett_servers" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300 transition-colors"><?php echo __('admin_label_servers'); ?></label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="show_activity" id="sett_activity" class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer" <?php echo $sys_show_activity ? 'checked' : ''; ?>>
                    <label for="sett_activity" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300 transition-colors"><?php echo __('admin_label_activity'); ?></label>
                </div>
                
                <!-- Language Settings -->
                <div class="border-t border-gray-800 my-4 pt-4">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"><?php echo __('admin_language_settings'); ?></label>
                    
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" name="language_mode" id="lang_mode_auto" value="auto" class="w-4 h-4 bg-dark border-gray-700 text-primary focus:ring-primary cursor-pointer" <?php echo ($settings['language_mode'] ?? 'auto') === 'auto' ? 'checked' : ''; ?>>
                            <label for="lang_mode_auto" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300 transition-colors"><?php echo __('admin_lang_mode_auto'); ?></label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="language_mode" id="lang_mode_fixed" value="fixed" class="w-4 h-4 bg-dark border-gray-700 text-primary focus:ring-primary cursor-pointer" <?php echo ($settings['language_mode'] ?? 'auto') === 'fixed' ? 'checked' : ''; ?>>
                            <label for="lang_mode_fixed" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300 transition-colors"><?php echo __('admin_lang_mode_fixed'); ?></label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3" id="fixed_lang_container" style="<?php echo ($settings['language_mode'] ?? 'auto') === 'fixed' ? '' : 'display: none;'; ?>">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_default_language'); ?></label>
                    <select name="fixed_language" class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer">
                        <option value="hu" <?php echo ($settings['fixed_language'] ?? 'hu') === 'hu' ? 'selected' : ''; ?>>Magyar (HU)</option>
                        <option value="en" <?php echo ($settings['fixed_language'] ?? 'hu') === 'en' ? 'selected' : ''; ?>>English (EN)</option>
                    </select>
                </div>
                
                <!-- Version Number Setting -->
                <div class="border-t border-gray-800 my-4 pt-4">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"><?php echo __('admin_version_label'); ?></label>
                    <input type="text" name="site_version" value="<?php echo htmlspecialchars($settings['site_version'] ?? '1.0.0'); ?>" 
                           class="w-full bg-dark border border-gray-700 text-white rounded-md px-3 py-2 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                
                <button type="submit" name="save_settings" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 px-4 rounded-md transition-colors shadow-lg mt-4 text-sm">
                    <?php echo __('admin_save_settings'); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Right column (2/3): Statistics -->
    <div class="lg:col-span-2 bg-card shadow-xl rounded-xl border border-gray-800 p-6 flex flex-col justify-center">
        <div>
            <h2 class="text-lg font-bold text-white mb-4 flex items-center border-b border-gray-700 pb-2">
                <i class="fas fa-chart-line mr-2 text-indigo-400"></i> <?php echo __('admin_stats_title'); ?>
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                <div class="bg-dark/50 border border-gray-800 p-4 rounded-lg text-center shadow-inner">
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1"><?php echo __('admin_stat_total_servers'); ?></span>
                    <span class="text-3xl font-extrabold text-primary"><?php echo count($servers); ?></span>
                </div>
                <div class="bg-dark/50 border border-gray-800 p-4 rounded-lg text-center shadow-inner">
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1"><?php echo __('admin_stat_header_links'); ?></span>
                    <span class="text-3xl font-extrabold text-emerald-400"><?php echo count($links); ?></span>
                </div>
                <div class="bg-dark/50 border border-gray-800 p-4 rounded-lg text-center shadow-inner">
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1"><?php echo __('admin_stat_social_links'); ?></span>
                    <span class="text-3xl font-extrabold text-blue-400"><?php echo count($social_links); ?></span>
                </div>
            </div>

            <div class="mt-6 text-sm text-gray-400 bg-dark/30 border border-gray-800/80 p-3 rounded-lg flex items-center justify-between shadow-sm">
                <span><i class="fas fa-sync-alt text-indigo-400 mr-2"></i> <?php echo __('admin_cron_sync'); ?></span>
                <span class="font-bold text-gray-200"><?php echo htmlspecialchars($last_cron_status); ?></span>
            </div>

            <div class="mt-3 text-sm text-gray-400 bg-dark/30 border border-gray-800/80 p-3 rounded-lg flex items-center justify-between shadow-sm">
                <span><i class="fab fa-twitch <?php echo $twitch_bot_online ? 'text-purple-500' : 'text-gray-500'; ?> mr-2"></i> <?php echo __('admin_twitch_bot_status'); ?></span>
                <span class="font-bold <?php echo $twitch_bot_online ? 'text-emerald-400' : 'text-rose-500'; ?>"><?php echo htmlspecialchars($twitch_bot_status); ?></span>
            </div>
            
            <h2 class="text-lg font-bold text-white mb-4 mt-8 flex items-center border-b border-gray-700 pb-2">
                <i class="fas fa-stethoscope mr-2 text-cyan-400"></i> <?php echo __('admin_diag_logs_title'); ?>
            </h2>

            <div class="space-y-4">
                <!-- Diagnostic Data -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-dark/45 border border-gray-800/85 p-3.5 rounded-lg flex items-center justify-between shadow-sm">
                        <span class="text-xs text-gray-400 font-medium"><?php echo __('admin_diag_twitch_conn'); ?></span>
                        <?php if ($twitch_irc_conn_ok): ?>
                            <span class="text-xs text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded flex items-center"><i class="fas fa-check-circle mr-1"></i> <?php echo __('admin_diag_ok'); ?></span>
                        <?php else: ?>
                            <span class="text-xs text-rose-500 font-bold bg-rose-500/10 border border-rose-500/20 px-2 py-1 rounded flex items-center"><i class="fas fa-times-circle mr-1"></i> <?php echo __('admin_diag_error'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="bg-dark/45 border border-gray-800/85 p-3.5 rounded-lg flex items-center justify-between shadow-sm">
                        <span class="text-xs text-gray-400 font-medium"><?php echo __('admin_diag_bot_process'); ?></span>
                        <?php if ($twitch_bot_active_lock): ?>
                            <span class="text-xs text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded flex items-center"><i class="fas fa-play-circle mr-1"></i> <?php echo __('admin_diag_running'); ?></span>
                        <?php else: ?>
                            <span class="text-xs text-gray-400 font-bold bg-gray-500/10 border border-gray-500/20 px-2 py-1 rounded flex items-center"><i class="fas fa-stop-circle mr-1"></i> <?php echo __('admin_diag_stopped'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($last_cron_results) && is_array($last_cron_results)): ?>
                <div class="mt-4 bg-dark/45 border border-gray-800/85 p-4 rounded-lg shadow-sm">
                    <h3 class="text-xs font-bold text-gray-300 mb-3 border-b border-gray-800 pb-2 uppercase tracking-wide"><?php echo __('admin_cron_last_results'); ?></h3>
                    <div class="space-y-2">
                        <?php foreach ($last_cron_results as $task): 
                            // Dynamic language translation for Hungarian texts saved by cron
                            $display_name = $task['name'];
                            if (strpos($display_name, 'Szerver: ') === 0) {
                                $display_name = __('admin_cron_server_prefix') . substr($display_name, 9);
                            } elseif (strpos($display_name, 'Discord: ') === 0) {
                                $display_name = __('admin_cron_discord_prefix') . substr($display_name, 9);
                            } else {
                                $clean_name = str_replace(
                                    ['á', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', '(', ')', ' '],
                                    ['a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u', '', '', '_'],
                                    strtolower($display_name)
                                );
                                $name_key = 'admin_cron_name_' . $clean_name;
                                $translated_name = __($name_key);
                                if ($translated_name !== $name_key) {
                                    $display_name = $translated_name;
                                }
                            }

                            $display_message = $task['message'];
                            if (preg_match('/^(\d+)\s+játékos$/u', $display_message, $m)) {
                                $display_message = __('admin_cron_msg_players', ['{players}' => $m[1]]);
                            } elseif (preg_match('/^(\d+)\s+online$/u', $display_message, $m)) {
                                $display_message = __('admin_cron_msg_online', ['{online}' => $m[1]]);
                            } else {
                                $clean_msg = str_replace(
                                    ['á', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', '(', ')', ' '],
                                    ['a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u', '', '', '_'],
                                    strtolower($display_message)
                                );
                                $msg_key = 'admin_cron_msg_' . $clean_msg;
                                $translated_msg = __($msg_key);
                                if ($translated_msg !== $msg_key) {
                                    $display_message = $translated_msg;
                                }
                            }

                            $status_color = 'text-gray-400';
                            $icon = 'fa-info-circle';
                            if ($task['status'] === 'success') {
                                $status_color = 'text-emerald-400';
                                $icon = 'fa-check-circle';
                            } elseif ($task['status'] === 'error') {
                                $status_color = 'text-rose-500';
                                $icon = 'fa-times-circle';
                            } elseif ($task['status'] === 'info' || $task['status'] === 'skipped') {
                                $status_color = 'text-blue-400';
                                $icon = 'fa-info-circle';
                            }
                        ?>
                        <div class="flex flex-col py-2 border-b border-gray-800/50 last:border-0">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-300"><?php echo htmlspecialchars($display_name); ?></span>
                                <span class="text-xs <?php echo $status_color; ?> flex items-center">
                                    <i class="fas <?php echo $icon; ?> mr-1.5"></i> <?php echo htmlspecialchars($display_message); ?>
                                </span>
                            </div>
                            <?php 
                            $extra_info = '';
                            if (strpos($task['name'], 'YouTube') !== false) {
                                $success_time = !empty($yt_last_success) ? date('Y-m-d H:i:s', (int)$yt_last_success) : __('admin_never');
                                $error_time = !empty($yt_last_error) ? date('Y-m-d H:i:s', (int)$yt_last_error) : __('admin_never');
                                $extra_info = __('admin_cron_last_success_fail', ['{success}' => $success_time, '{fail}' => $error_time]);
                            } elseif (strpos($task['name'], 'Twitch (Klip)') !== false) {
                                $success_time = !empty($twitch_last_success) ? date('Y-m-d H:i:s', (int)$twitch_last_success) : __('admin_never');
                                $error_time = !empty($twitch_last_error) ? date('Y-m-d H:i:s', (int)$twitch_last_error) : __('admin_never');
                                $extra_info = __('admin_cron_last_success_fail', ['{success}' => $success_time, '{fail}' => $error_time]);
                            }
                            if (!empty($extra_info)):
                            ?>
                            <span class="text-gray-500 text-[10px] mt-0.5"><?php echo $extra_info; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Cron Jobs & Discord Bot Integrations -->
                <div class="mt-6 bg-dark/45 border border-gray-800/85 p-5 rounded-lg shadow-sm">
                    <h3 class="text-sm font-bold text-white mb-3 border-b border-gray-800 pb-2 flex items-center">
                        <i class="fas fa-clock text-indigo-400 mr-2"></i> <?php echo __('admin_cron_info_title'); ?>
                    </h3>
                    <p class="text-gray-400 text-xs leading-relaxed mb-4">
                        <?php echo __('admin_cron_info_desc'); ?>
                    </p>

                    <div class="space-y-3">
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-gray-500 mb-1"><?php echo __('admin_cron_cmd_cron'); ?></span>
                            <code class="block bg-black/50 border border-gray-800 px-3 py-2 rounded-lg text-xs text-indigo-300 font-mono break-all selection:bg-indigo-900">*/5 * * * * php -q <?php echo htmlspecialchars(dirname(__DIR__)); ?>/cron.php</code>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-gray-500 mb-1"><?php echo __('admin_cron_cmd_bot'); ?></span>
                            <code class="block bg-black/50 border border-gray-800 px-3 py-2 rounded-lg text-xs text-indigo-300 font-mono break-all selection:bg-indigo-900">*/5 * * * * php -q <?php echo htmlspecialchars(dirname(__DIR__)); ?>/twitch_bot.php</code>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-gray-800/60 pt-3 flex items-start gap-2 text-xs text-gray-400 leading-normal">
                        <i class="fas fa-info-circle text-blue-500 shrink-0 mt-0.5"></i>
                        <p>
                            <?php echo __('admin_cron_discord_bot_remind'); ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
