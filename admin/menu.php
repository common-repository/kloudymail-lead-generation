<?php
	add_action('admin_init','kmlgRegister');
	add_action('admin_menu','kmlgSettingMenu');

	add_action( 'admin_enqueue_scripts', 'kmlgMenuStyle' );

	function kmlgMenuStyle(){
		wp_enqueue_style('km_style',plugins_url( 'km_style.css', __FILE__ ));
	}

	function kmlgRegister(){
		add_settings_section('kmlgGeneral',__('Settings','kloudymail'),'kmlgSetSection','Kloudymail');
		add_settings_section('kmlgRecaptcha',__('Recaptcha','kloudymail'),'kmlgSetSectionRecaptcha','Kloudymail');
		add_settings_field('api_key',__('API key','kloudymail'),'kmlgApiCallback','Kloudymail','kmlgGeneral');
		add_settings_field('account',__('Account','kloudymail'),'kmlgAccountCallback','Kloudymail','kmlgGeneral');
		add_settings_field('recaptcha_site_key',__('Site key','kloudymail'),'kmlgRecaptchaSiteKeyCallback','Kloudymail','kmlgRecaptcha');
		add_settings_field('recaptcha_secret_key',__('Secret key','kloudymail'),'kmlgRecaptchaSecretKeyCallback','Kloudymail','kmlgRecaptcha');

		register_setting('kmlgGeneral','Kloudymail',array(
			'type'=>'string',
			'sanitize_callback'=>'kmlgCheck'
		));
	}

	function kmlgSetSection(){
		echo '';
	}
	
	function kmlgSetSectionRecaptcha(){
		echo "<div class='card'>";
		echo __('Get keys from <a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">here</a>','kloudymail');
		echo __(' And add ','kloudymail');
		echo $_SERVER['SERVER_NAME'];
		echo __(' to the allowed domains','kloudymail');
	}

	function kmlgSettingMenu(){
		$icon='data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIyLjEuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxpdmVsbG9fMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiCgkgdmlld0JveD0iMCAwIDIwIDIwIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAyMCAyMDsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiNBMUEwQTA7fQo8L3N0eWxlPgo8dGl0bGU+VGF2b2xhIGRpc2Vnbm8gMyBjb3BpYSAzPC90aXRsZT4KPHBhdGggY2xhc3M9InN0MCIgZD0iTTE5LjIsOC4xTDExLDAuM2MtMC41LTAuNC0xLjItMC40LTEuNywwTDEuMSw4LjFDMC45LDguNCwwLjgsOC43LDAuOCw5djkuOEMwLjgsMTkuNSwxLjMsMjAsMiwyMGgxNi40CgljMC43LDAsMS4yLTAuNSwxLjItMS4yVjlDMTkuNiw4LjcsMTkuNSw4LjQsMTkuMiw4LjF6IE00LjQsMTkuM2MtMC4zLDAtMC41LTAuMi0wLjUtMC41YzAtMC4xLDAuMS0wLjMsMC4yLTAuNGw1LjgtNQoJYzAuMi0wLjIsMC41LTAuMiwwLjYsMGw1LjcsNWMwLjIsMC4xLDAuMywwLjUsMC4xLDAuN2MtMC4xLDAuMS0wLjIsMC4yLTAuNCwwLjJINC40eiBNMTguOSw5djkuOGMwLDAuMSwwLDAuMSwwLDAuMgoJYzAsMCwwLDAuMSwwLDAuMWMwLDAsMCwwLDAsMC4xbDAsMGMwLDAsMCwwLDAsMGMwLDAtMC4xLDAtMC4xLDAuMWMwLDAtMC4xLDAtMC4xLDBjLTAuMSwwLTAuMSwwLTAuMiwwYy0wLjEsMC0wLjIsMC0wLjMtMC4xbC01LjgtNQoJYy0wLjItMC4yLTAuMi0wLjUsMC0wLjdjMCwwLDAsMCwwLDBMMTcsOS4zYzAtMS0wLjktMS44LTEuOS0xLjdjLTAuMywwLTAuNywwLjEtMC45LDAuM2MtMC43LTIuMi0zLTMuNS01LjItMi44CglDNy41LDUuNSw2LjQsNi44LDYuMSw4LjNjMCwwLDAsMC0wLjEsMGMtMSwwLTEuOSwwLjQtMi41LDEuMWw0LjUsMy45YzAuMiwwLjIsMC4yLDAuNSwwLDAuN2MwLDAsMCwwLDAsMGwtNS44LDUKCWMtMC4xLDAuMS0wLjIsMC4xLTAuMywwLjFjLTAuMSwwLTAuMSwwLTAuMiwwYy0wLjEsMC0wLjEtMC4xLTAuMi0wLjFjMCwwLDAsMCwwLDBjMCwwLTAuMS0wLjEtMC4xLTAuMWMwLTAuMS0wLjEtMC4xLTAuMS0wLjJ2LTEwCgljMC0wLjIsMC4xLTAuNCwwLjMtMC40YzAuMSwwLDAuMiwwLDAuMiwwbDcuOC03LjRjMC4xLTAuMSwwLjEtMC4xLDAuMi0wLjFjMCwwLDAuMSwwLDAuMSwwYzAuMSwwLDAuMiwwLDAuMywwLjFjMCwwLDAsMCwwLjEsMAoJTDE1LjksNmwyLjQsMi4zYzAuMywwLDAuNSwwLjIsMC41LDAuNGMwLDAsMCwwLDAsMC4xVjl6Ii8+Cjwvc3ZnPgo=';
		//add_options_page('Kloudymail','Kloudymail','manage_options','Kloudymail','settingsPage');

		add_menu_page('Kloudymail', 'Kloudymail', 'manage_options', 'km_menu', 'kmlgSettingsPage', $icon);
		add_submenu_page('km_menu', __('Settings','kloudymail'), __('Settings','kloudymail'), 'manage_options', 'km_menu', 'kmlgSettingsPage');
		add_submenu_page('km_menu', __('Export','kloudymail'), __('Export','kloudymail'), 'manage_options', 'km_export', 'kmlgExportPage');
		add_submenu_page('km_menu', __('Widget','kloudymail'), __('Widget','kloudymail'), 'manage_options', 'km_widget', 'kmlgWidgetPage');
	}

	function kmlgCheck($input){
		$updated=true;
		if (!isset($input['account']) && strlen($input['account']) == 36){
			add_settings_error('Kloudymail', 'errAcc', __('Account not selected/Wrong API Key', 'kloudymail'), 'error');
			$settings = (array)get_option('Kloudymail');
			if (isset($settings['account'])){
				$input['account']=$settings['account'];
				$input['api_key']=$settings['api_key'];
			} else {
				$input['account']='';
				$input['api_key']='';
			}
			$updated=false;
			// non salva
		}
		if (empty($input['recaptcha_site_key']) || empty($input['recaptcha_secret_key'])) {
			add_settings_error('Kloudymail', 'errRe', __('Recaptcha keys not inserted', 'kloudymail'), 'error');
			$updated=false;
		}

		if ($updated)
			add_settings_error('Kloudymail', 'update', __('Settings saved', 'kloudymail'), 'updated');
		return $input;
	}

	function kmlgWidgetPage(){
		global $wpdb,$kmlg_shortcode_widgets;
		
		if (isset($_GET['id'])) {
			$widget_id = $_GET['id'];

			if(isset($_GET['action'])){ // eliminazione
				$wpdb->delete($kmlg_shortcode_widgets, array('id' => $widget_id));
				_e('<h2>Widget deleted</h2>','kloudymail');
				echo '<a href="' . get_admin_url(0, 'admin.php?page=km_widget') . '" class="button km_button">' . __('Back','kloudymail') . '</a>';
			} else { // se è una pagina di modifica/aggiunta
				$settings=(array)get_option('Kloudymail');

				if($widget_id=='add'){
					$notify=true;
					$list='';
					$name='';
					$new=true;
				} else {
					$sql = $wpdb->prepare("SELECT * FROM $kmlg_shortcode_widgets WHERE id='%s';", array($widget_id) );
					$ret = $wpdb->get_row($sql);
					if (is_null($ret)){ // se il widget non esiste
						$notify = true;
						$list = '';
						$name = '';
						$new = true;
					} else {
						$notify = $ret->notify;
						$list = $ret->list;
						$name = $ret->name;
						$new = false;
					}
				}
				?><div id="km_edit" class="km_space" style="width: 300px">
				<?php _e('Widget name:','kloudymail'); ?><br>
				<input class="widefat km_general" type="text" maxlength="30" id="widget_name" value="<?php echo$name ?>"><br><br>
				<?php
				include PLUGIN_DIR.'/admin/form.php';
				?>
				<br>
				<button class="button km_button" id="edit" data-ajax-call="<?php echo admin_url('admin-ajax.php');?>"><?php _e('Save','kloudymail') ?></button>
				<a href="<?php echo get_admin_url(0,'admin.php?page=km_widget'); ?>" class="button km_button"><?php _e('Back','kloudymail') ?></a><br><br>
				</div>
				<script type="text/javascript">
					jQuery(function($){
						$("#edit").on('click',function(){
							$("select[id='list_<?php echo $widget_id; ?>']").removeAttr( 'style' );
							$("#widget_name").removeAttr( 'style' );

							var list = $("select[id='list_<?php echo $widget_id; ?>'] option:selected").val();
							var name = $("#widget_name").val();

							if(list==0){
								$("select[id='list_<?php echo $widget_id; ?>']").css('border-color','red');
							}
							if(name.length==0){
								$("#widget_name").css('border-color','red');
							}

							if(list!=0 && name.length>0){
								$.ajax({
									url : $("#edit").data('ajax-call'),
						            type : "POST",
					            	data:{
						            	action: "kmlg_save_widget",
						            	'id': '<?php echo $widget_id; ?>',
						            	'name': name,
						            	'list': list,
						            	'listName': $("select[id='list_<?php echo $widget_id; ?>'] option:selected").text(),
						            	'notify': $("#notify_<?php echo $widget_id; ?>").prop('checked'),
						            },
						            success : function(jqXHR){
						            	window.location.href="<?php echo get_admin_url(0,'admin.php?page=km_widget'); ?>";
						            },
						            error: function(){}
					        	});
							}
						});

						$("button[id='update_<?php echo $widget_id; ?>'").on('click', function($event){
			        		$event.preventDefault();
			        		$.ajax({
								url : $("#edit").data('ajax-call'),
					            type : "POST",
				            	data:{
					            	action: "kmlg_short_update_fields",
					            	'id': '<?php echo $widget_id; ?>',
					            },
					            success : function(jqXHR){
					            	window.location.href="<?php echo get_admin_url(0,'admin.php?page=km_widget'); ?>";
					            },
					            error: function(){}
			        		});
			        	});
					});
				</script>
				<?php
			}
		} else {
			$ret=$wpdb->get_results("SELECT * FROM $kmlg_shortcode_widgets;");
			$id=$wpdb->num_rows+1;
			$list=new kmlgWidgetList();
			
			$items=array();
			foreach ($ret as $w) {
				$edit_link = 'admin.php?page=km_widget&id='.$w->id;
				if($w->notify==true)
					$notify=__('Yes','kloudymail');
				else
					$notify=__('No','kloudymail');
				array_push($items,array(
					'id'=>$w->id,
					'name'=>$w->name,
					'list'=>$w->listName,
					'notify'=>$notify,
					'shortcode'=>"<input class='widefat km_general' id='short_$w->id' value='[kloudymail id=&#39;$w->id&#39; name=&#39;$w->name&#39;]' readonly>",
					'copy'=>"<button class='button km_button' onclick='copy($w->id)'>".__('Copy','kloudymail')."</button>"
				));
			}
			?>
			<div class="kmlg_space">
				<h1><?php _e('Widget list:','kloudymail'); ?></h1>
				<script type="text/javascript">
					function copy(id){
						document.getElementById("short_"+id).select();
						document.execCommand("copy");
					}
				</script>	
			<?php
	  		$list->items = $items;
	  		$list->prepare_items();
	  		$list->display();
	  		?>
			  	<a class="button kmlg_button" href="admin.php?page=km_widget&id=add"><?php _e('Add new','kloudymail'); ?></a>
			</div>
			<?php
		}
	}

	function kmlgSettingsPage(){
		global $kmlg_url;

		$url = $kmlg_url;
		$settings = (array)get_option('Kloudymail');
		if (isset($settings['account']))
			$account = esc_attr($settings['account']);
		else
			$account = '';
		?>
		<div class="wrap kmlg_space">
		<h1> Kloudymail </h1>
		<form method="post" id="kmlg_sett_form" action="options.php" data-ajax-call="<?php echo admin_url('admin-ajax.php');?>">
			<table class="form-table">
		<?php
		settings_errors('Kloudymail');
		settings_fields('kmlgGeneral');
		do_settings_sections('Kloudymail');
		?>
		</div>
			</table>
			<?php submit_button(); ?>
		</form>

		</div>
		<script type="text/javascript">
		jQuery(function($){
			var getAccounts = function () {
				$.ajax({
		            url: $("#kmlg_sett_form").data('ajax-call'),
		            type: "GET",
	            	data:{
		            	action: "kmlg_get_accounts",
		            	api_key: $("#api_key").val()
		            },
		            success: function(jqXHR) {
		            	var json = $.parseJSON(jqXHR.data)['results'];
		            	$("#errMsg").empty();
		            	$("#account").empty()
		            	for(var j=0; j<json.length; j++) {
							// solo account attivi
							if(json[j]['enabled'])
								$("#account").append(
									$('<option>', {
										value: json[j]['code'],
										text: json[j]['name'],
										selected: '<?php echo $account ?>' == json[j]['code']
									})
								);
		            	}
		            	/* getLists(); */
		            },
		            error: function(){
						$("#account").empty();
						$("#errMsg").html("<?php _e('Wrong key','kloudymail') ?>");
		            }
		        });
			}
			
			$(document).ready(function(){
				getAccounts();
				$('#api_key').on('input', getAccounts);
			});
		});
		</script>
		<?php
	}

	function kmlgExportPage(){
		global $wpdb,$kmlg_subs_table_name;

		$ret=$wpdb->get_results("SELECT list,account,listName,count(*) as n FROM $kmlg_subs_table_name group by list;"); 
		if ($wpdb->num_rows>0){
		?>
		<div class="kmlg_space">
		<h1><?php _e('Export','kloudymail') ?></h1><br>
		<p><?php _e('Thanks to this feature, you will able to export in a csv file all the subscribers to your newsletter','kloudymail') ?></p>
		<select id="lists" data-ajax-call="<?php echo admin_url('admin-ajax.php');?>" class="kmlg_general">
			<?php
			foreach ($ret as $list) {
				echo "<option value='$list->list' data-account='$list->account'>$list->listName($list->n)</option>";
			}
			?>
		</select>
		 &nbsp;&nbsp;&nbsp;<a class="kmlg_button button" id="export"><?php _e('Export subscribers','kloudymail') ?></a> 
		</div>

		<script type="text/javascript">
		jQuery(function($){
		 	var getExportUrl = function () {
				$("#export").attr('href', $("#lists").data('ajax-call') + "?action=kmlg_export&selectedAccount=" + $("#lists option:selected").data('account') + "&selectedList=" + $("#lists").val());
			}

			$(document).ready(function(){
				getExportUrl();
				$("#lists").on('change', getExportUrl);
			});
		});
		</script>
		<?php
		} else {
			_e('<h2>There is no subscribers to export.</h2>','kloudymail');
		}
	}

	function kmlgApiCallback(){
		kmlgCreateOption('api_key', 'text');
		echo '<p id="errMsg"></p>';
	}

	function kmlgAccountCallback(){
		kmlgCreateOption('account', 'select');
	}

	function kmlgRecaptchaSiteKeyCallback(){
		kmlgCreateOption('recaptcha_site_key', 'text');
	}
	function kmlgRecaptchaSecretKeyCallback(){
		kmlgCreateOption('recaptcha_secret_key', 'text');

	}

	function kmlgCreateOption($field, $type){
		$settings = (array)get_option('Kloudymail');
		if (isset($settings[$field]))
			$value = esc_attr($settings[$field]);
		else
			$value = '';
		if ($type == 'select')
			echo "<select id='$field' class='kmlg_general' name='Kloudymail[$field]'></select>";
		else
			echo "<input type='$type' class='kmlg_general' id='$field' name='Kloudymail[$field]' value='$value'>";
	}
?>