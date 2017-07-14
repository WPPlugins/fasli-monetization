<?php
	/*
	 * Plugin Name: Fasli monetization
	 * Description: This plugin allows you to configure Full Page Script, Website Entry Script.
	 * Version: 1.0.1
	 * Author: Fasli
	 * Author URI: http://fas.li
	 * License: GPL2
	 */
	function fasli_plugins_get_options() {
		return array(
			'enabled' => get_option('fasli_plugins_option_enabled'),
			'id' => trim(get_option('fasli_plugins_option_id')) ?: '-1',
			'domain' => trim(get_option('fasli_plugins_option_domain')) ?: 'fas.li',
			'website_entry_enabled' => get_option('fasli_plugins_option_website_entry_enabled'),
			'include_exclude_domains_choose' => get_option('fasli_plugins_option_include_exclude_domains_choose') ?: 'exclude',
			'include_exclude_domains_value' => trim(get_option('fasli_plugins_option_include_exclude_domains_value')),
			'exclude_roles' => get_option('fasli_plugins_option_exclude_roles')
		);
	}
	function fasli_plugins_gen_script() {
		if (get_option('fasli_plugins_option_enabled')) {
			$options = fasli_plugins_get_options();
			global $current_user;
			
			if ($options['exclude_roles']) {
				foreach ($options['exclude_roles'] as $excludeRole) {
					if (in_array($excludeRole, $current_user->roles)) {
						return false;
					}
				}
			}
			
			echo '
				<script type="text/javascript">
					var fas_token = ' . json_encode($options['id']) . ';
					var domain_url = ' . json_encode($options['domain']) . ';
					' . fasli_plugins_gen_include_exclude_domains_script($options) . ' 
					
					' . ($options['website_entry_enabled'] ? 'var fa_uid = ' . json_encode($options['id']) . ';' : '') . ' 
					' . ($options['website_entry_enabled'] ? 'var fa_cap = 5;' : '') . ' 
				    ' . ($options['website_entry_enabled'] ? 'var fa_delay = 5;' : '') . ' 
				    ' . ($options['website_entry_enabled'] ? 'var fa_delay = 3;' : '') . '  
					
				</script>
			';
		
			wp_enqueue_script('fullpage', '/wp-content/plugins/fasli-monetization/script.js');
				if($options['website_entry_enabled']) 
			wp_enqueue_script('entryscript', '/wp-content/plugins/fasli-monetization/fa_in.js');

		} else {
			return false;
		}
	}
	function fasli_plugins_gen_include_exclude_domains_script($options) {
		$script = 'var ';
		if ($options['include_exclude_domains_choose'] == 'include') {
			$script .= 'domains_include = [';
		} else if ($options['include_exclude_domains_choose'] == 'exclude') {
			$script .= 'domains_exclude = [';
		}
		if (trim($options['include_exclude_domains_value'])) {
			$script .= implode(', ', array_map(function($x) {
				return json_encode(trim($x));
			}, explode(',', trim($options['include_exclude_domains_value']))));
		}
		
		$script .= '];';
		return $script;
	}
	function fasli_plugins_create_admin_menu() {
		add_options_page('Fasli website monetization Settings', 'Fasli website monetization Settings', 'administrator', __FILE__, 'fasli_plugins_admin_settings_page', plugins_url('/images/icon.png', __FILE__ ));
		add_action('admin_init', 'fasli_plugins_register_options');
	}
	function fasli_plugins_option_id_validate($value) {
		if (!eregi("^([a-zA-Z0-9])+$", str_replace(" ", "", trim($value)))) {
			add_settings_error('fasli_plugins_option_id', 'fasli_plugins_option_id', 'User ID is required and must be an alphanumeric .', 'error');
			return false;
		} else {
			return $value;
		}
	}
	function fasli_plugins_domain_name_validate($value) {
		return preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/', $value);
	}
	function fasli_plugins_option_include_exclude_domains_value_validate($value) {
		$arr = array_filter(array_map(function($x) { return trim($x); }, explode(',', trim($value))), function($x) { return $x ? true : false; });
		if (count($arr)) {
			array_map(function($x) {
				if (!fasli_plugins_domain_name_validate($x)) {
					add_settings_error('fasli_plugins_option_id', 'fasli_plugins_option_include_exclude_domains_value', $x . ' is not valid domain name.', 'error');
				}
			}, $arr);
		} else {
			add_settings_error('fasli_plugins_option_id', 'fasli_plugins_option_include_exclude_domains_value', 'You must specify at least one domain name to include/exclude.', 'error');
		}
		
		return implode(',', $arr);
	}
	function fasli_plugins_option_custom_domain_validate($value) {
		if (($value = trim($value)) && !fasli_plugins_domain_name_validate($value)) {
			add_settings_error('fasli_plugins_option_id', 'fasli_plugins_option_custom_domain', $value . ' is not valid domain name.', 'error');
			return false;
		}
		
		return $value;
	}
	function fasli_plugins_register_options() {
		register_setting('np-fasli-settings-group', 'fasli_plugins_option_enabled');
		register_setting('np-fasli-settings-group', 'fasli_plugins_option_id', 'fasli_plugins_option_id_validate');
		register_setting('np-fasli-settings-group', 'fasli_plugins_option_domain');
		register_setting('np-fasli-settings-group', 'fasli_plugins_option_website_entry_enabled');
		register_setting('np-fasli-settings-group', 'fasli_plugins_option_include_exclude_domains_choose');
		register_setting('np-fasli-settings-group', 'fasli_plugins_option_include_exclude_domains_value', 'fasli_plugins_option_include_exclude_domains_value_validate');
		register_setting('np-fasli-settings-group', 'fasli_plugins_option_exclude_roles');
	}
	function fasli_plugins_admin_settings_page() {?>
		<div class="wrap">
			<h2>Fasli website monetization</h2>
			
			<form method="post" action="options.php">
		    	<?php settings_fields('np-fasli-settings-group');?>
		    	<table class="form-table">
		    		<tbody>
						<tr valign="top">
							<td scope="row">Integration Enabled</td>
							<td><input type="checkbox" <?php echo get_option('fasli_plugins_option_enabled') ? 'checked="checked"' : '' ?> value="1" name="fasli_plugins_option_enabled" /></td>
						</tr>
						<tr valign="top">
							<td scope="row">Fasli User ID</td>
							<td>
								<input type="text" name="fasli_plugins_option_id" value="<?php echo htmlspecialchars(get_option('fasli_plugins_option_id'), ENT_QUOTES) ?>" />
								<p class="description">
									Simply visit <a href="https://dashboard.fas.li/tools/fullpage" target="_blank">https://dashboard.fas.li/tools/fullpage</a> page.
									There will be fas_token = "XXX" where XXX is your Fasli User ID.
								</p>
							</td>
						</tr>
						
						<tr valign="top">
							<td scope="row">Fasli Domain</td>
							<td>
								<select name="fasli_plugins_option_domain">
									<option value="fas.li" <?php echo get_option('fasli_plugins_option_domain') == 'fas.li' ? 'selected="selected"' : '' ?>>fas.li</option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<td scope="row">Include/Exclude Domains</td>
							<td>
								<div>
									<label>
										<input type="radio" name="fasli_plugins_option_include_exclude_domains_choose" value="include" <?php echo get_option('fasli_plugins_option_include_exclude_domains_choose') == 'include' ? 'checked="checked"' : '' ?> />
										Include
									</label>
									<label>
										<input type="radio" name="fasli_plugins_option_include_exclude_domains_choose" value="exclude" <?php echo !get_option('fasli_plugins_option_include_exclude_domains_choose') || get_option('fasli_plugins_option_include_exclude_domains_choose') == 'exclude' ? 'checked="checked"' : '' ?> />
										Exclude
									</label>
								</div>
								<div>
									<textarea rows="4" style="width: 64%;" name="fasli_plugins_option_include_exclude_domains_value"><?php echo htmlspecialchars(trim(get_option('fasli_plugins_option_include_exclude_domains_value')), ENT_QUOTES) ?></textarea>
									<p class="description">Comma-separated list of domains.</p>
								</div>
							</td>
						</tr>
						
						
						
						<tr valign="top">
							<td scope="row">Website Entry Script Enabled</td>
							<td>
								<input type="checkbox" <?php echo get_option('fasli_plugins_option_website_entry_enabled') ? 'checked="checked"' : '' ?> value="1" name="fasli_plugins_option_website_entry_enabled" />
								<p class="description">Check this option if you wish to earn money when a visitor simply enters your site.</p>
							</td>
						</tr>
						<tr valign="top">
							<td scope="row">Exclude following user roles from displaying ads</td>
							<td>
								<select name="fasli_plugins_option_exclude_roles[]" multiple="multiple">
									<option <?php echo get_option('fasli_plugins_option_exclude_roles') && in_array('subscriber', get_option('fasli_plugins_option_exclude_roles')) ? ' selected="selected" ' : '' ?> value="subscriber">Subscriber</option>
									<option <?php echo get_option('fasli_plugins_option_exclude_roles') && in_array('contributor', get_option('fasli_plugins_option_exclude_roles')) ? ' selected="selected" ' : '' ?> value="contributor">Contributor</option>
									<option <?php echo get_option('fasli_plugins_option_exclude_roles') && in_array('author', get_option('fasli_plugins_option_exclude_roles')) ? ' selected="selected" ' : '' ?> value="author">Author</option>
									<option <?php echo get_option('fasli_plugins_option_exclude_roles') && in_array('editor', get_option('fasli_plugins_option_exclude_roles')) ? ' selected="selected" ' : '' ?> value="editor">Editor</option>
									<option <?php echo get_option('fasli_plugins_option_exclude_roles') && in_array('administrator', get_option('fasli_plugins_option_exclude_roles')) ? ' selected="selected" ' : '' ?> value="administrator">Administrator</option
								</select>
							</td>
						</tr>
					</tbody>
				</table>
		
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Update Settings') ?>" />
				</p>
				
				<p>Please contact our <a href="https://dashboard.fas.li/contact" target="_blank">Support Portal</a> if you have any questions and/or suggestions.</p>		
			</form>
		</div>
<?php }?>
<?php
	add_action('wp_head', 'fasli_plugins_gen_script');
	add_action('admin_menu', 'fasli_plugins_create_admin_menu');
?>
