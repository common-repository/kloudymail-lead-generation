<?php
	// Is a shortcode widget
	if (isset($widget_id) && (is_numeric($widget_id) || $widget_id=='add')) {
		$list_id = "list_$widget_id";
		$list_name = $widget_id;
		$notify_id = "notify_$widget_id";
		$notify_name = $widget_id;
		$update_id = "update_$widget_id";
	} else {//is a normal widget
		$list_id = $this->get_field_id('list');
		$list_name = $this->get_field_name('list');
		$notify_id = $this->get_field_id('notify');
		$notify_name = $this->get_field_name('notify');
		$update_id = $this->get_field_id('update');
	}
?>
<?php _e('Subscribe in list','kloudymail') ?>:<select class="widefat km_general" id="<?php echo $list_id; ?>" name="<?php echo $list_name; ?>"></select>
<label><br>
<input class="widefat km_general" id="<?php echo $notify_id; ?>" name="<?php echo $notify_name; ?>" type="checkbox" <?php if($notify) echo "checked" ?> /><?php _e('Notify the user','kloudymail') ?></label><br><br>
<?php
if(!$new){ 
	echo "<button class='button km_button' id='$update_id'>".__('Update Form fields','kloudymail')."</button>";
}
?>
<br>
<script type="text/javascript">
jQuery(function($){
	$(document).ready(function(){
		var select=$("select[id=<?php echo $list_id; ?>]");
		var account='<?php echo $settings['account']; ?>';
		var key='<?php echo $settings['api_key']; ?>';
		$.ajax({
            url : '<?php echo admin_url('admin-ajax.php');?>',
            type : "GET",
            data:{
            	action: "kmlg_get_lists"
            },
            success : function(jqXHR){
            	var json = $.parseJSON(jqXHR.data);

            	select.empty().append('<option value=0>------</option>');
            	for(var j=0; j<json.length; j++){
            		select.append(
						$('<option>', {
							value: json[j]['code'],
							text: json[j]['name'],
							selected: '<?php echo $list; ?>' == json[j]['code']
						})
					);
            	}
            },
            error: function(){
				$("input[id=<?php echo $notify_id; ?>]").attr('disabled',true);
				$("button[id=<?php echo $update_id; ?>]").attr('disabled',true);
            	select.empty();
            	select.append('<option></option>');
            	select.attr('disabled', true);
            	select.parent().append('<br><b><?php _e('Unable to get lists','kloudymail') ?></b><br>');

            	// var selectedList=<?php echo$list; ?>;
            	// if (selectedList=='')
            }
    	});
	});
});
</script>