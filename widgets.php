<?php
class kmlgWidget extends WP_widget{
	public function __construct(){
		$widget_ops=array(
			'classname'=>'Kloudymail',
			'description'=>__('Kloudymail subscription form','kloudymail')
		);
		parent::__construct('Kloudymail',__('Kloudymail form','kloudymail'),$widget_ops);
	}

	public function widget($args,$instance){
		global $kmlg_url;
		$url=$kmlg_url;
		$settings=get_option('Kloudymail');

		if (isset($settings['account']) and !empty($settings['recaptcha_site_key']) and !empty($settings['recaptcha_secret_key']) and isset($instance['fields'])){
			$account=$settings['account'];
		    $key=$settings['api_key'];
		    $captchaKey=$settings['recaptcha_site_key'];
		    $list=$instance['list'];
		    $fields=json_decode($instance['fields']);

		    if($account==$instance['account']){
    			wp_localize_script( 'subscribe', str_replace('-','',$this->id), kmlgGetLocalizeArray($key,$list,$fields,$captchaKey) );
			?>
			<form id='form_<?php echo$this->id; ?>' data-instance='<?php echo str_replace('-','',$this->id); ?>' method="POST" action="<?php echo admin_url('admin-ajax.php'); ?>">
				<input name="widget_number" type="hidden" value="<?php echo $this->number; ?>">
				<p id='response_form_<?php echo$this->id; ?>'></p>
			</form>
			<?php
		    }
    	}	    
	}
	

	public function form($instance){
		global $kmlg_url;
		$url=$kmlg_url;
		$title=__('Kloudymail form','kloudymail');

		$settings=get_option('Kloudymail');
		if ($instance){
			$list=esc_attr($instance['list']);
			$notify=esc_attr($instance['notify'])==""?false:true;
			//wp_die(esc_attr($instance['notify']));
		}else{
			$list='';
			$notify=true;
			$instance['listName']='';
		}
		$new=false;
		
		include dirname(__FILE__).'/admin/form.php';
		?>
		<script type="text/javascript">
			jQuery(function($){
				$(document).ready(function(){
		        	$("button[id='<?php echo $this->get_field_id('update'); ?>'").on('click',function($event){
		        		$event.preventDefault();
		        		wpWidgets.save($(this).closest('div.widget'), 0, 1, 0);//come se si schiacciasse su salva
		        	});
				});
			});
        </script>

        <?php
	}

	public function update($new_instance,$old_instance){//aggiornamento impostazioni(lista)
		global $kmlg_url;;

		$instance=$old_instance;
		$instance['list']=strip_tags($new_instance['list']);//setta la lista selezionata
		if (isset($new_instance['notify']))
			$instance['notify']=true;
		else
			$instance['notify']=false;

		//scarica la lista dei campi
        if ($instance['list']!='0'){//se l'hai scelta
        	$settings=get_option('Kloudymail');
			$account=$settings['account'];
        	
        	$instance['fields']=kmlgGetFields($instance['list']);
        	$instance['account']=$account;//per poi controllare se l'account è cambiato

			$lib=new KmApi($settings['api_key'],$kmlg_url);
			$details=$lib->list_details($account,$instance['list']);
        	$instance['listName']=$details['name'];
        }
		return $instance;
	}
}

function kmlgShortcodeHandler($atts){
	global $wpdb,$kmlg_url,$kmlg_shortcode_widgets;

	$a=shortcode_atts(array(
		'id'=>'kloudymail',
	),$atts);
	$a['id']='kloudymail-short-'.$a['id'];

	$url=$kmlg_url;
	$settings=get_option('Kloudymail');

	$ret=$wpdb->get_row("SELECT * FROM $kmlg_shortcode_widgets WHERE id='$atts[id]';");

	$HTMLret="";

	if (isset($settings['account']) and !empty($settings['recaptcha_site_key']) and !empty($settings['recaptcha_secret_key']) and $ret!=null){
	    $key=$settings['api_key'];
	    $captchaKey=$settings['recaptcha_site_key'];

		$list=$ret->list;
		$account=$ret->account;
		$fields=json_decode($ret->fields);

	    if($account==$settings['account']){
	    	wp_localize_script( 'subscribe', str_replace('-','',$a['id']), kmlgGetLocalizeArray($key,$list,$fields,$captchaKey));
		$HTMLret="<form id='form_$a[id]' data-instance='".str_replace('-','',$a['id'])."' method'POST' action='".admin_url('admin-ajax.php')."'>
			<input name='widget_number' type='hidden' value='".substr($a['id'],11)."'>
			<p id='response_form_$a[id]'></p>
			<div class='km_subscribe_text' id='form_$a[id]_subs'></div>
		</form>";
	    }
	}
  
	return $HTMLret;
}

class kmlgWidgetList extends kmlgListTable{
	function get_columns(){
		$columns=array(
			'id'=>'id',
			'name'=>__('Name','kloudymail'),
			'list'=>__('List','kloudymail'),
			'notify'=>__('Notify','kloudymail'),
			'shortcode'=>__('Shortcode','kloudymail'),
			'copy'=>''
		);
		return $columns;
	}

	function prepare_items(){
		$columns=$this->get_columns();
		$hidden = array('id');
	  	$sortable=$this->get_sortable_columns();
	  	$this->_column_headers = array($columns, $hidden, $sortable);
	  	usort($this->items, array(&$this,'usort_reorder'));
	}

	function get_sortable_columns() {
		$sortable = array(
	  		'name'=>array('name',false),
	  		'list'=>array('list',false),
	  		'notify'=>array('notify',false)
	  	);
	  	return $sortable;
	}

	function usort_reorder($a,$b){
		// If no sort, default to name
		  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'name';
		  // If no order, default to asc
		  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
		  // Determine sort order
		  $result = strcmp( $a[$orderby], $b[$orderby] );
		  // Send final sort direction to usort
		  return ( $order === 'asc' ) ? $result : -$result;
	}

	function column_default($item,$column_name){
		return $item[$column_name];
	}

	function column_name($item){
		$actions=array(
			"edit"=>"<a href='admin.php?page=km_widget&id=$item[id]'>".__('Edit','kloudymail')."</a>",
			"delete"=>"<a href='admin.php?page=km_widget&action=delete&id=$item[id]'>".__('Delete','kloudymail')."</a>",
		);

		return $item['name']." ".$this->row_actions($actions);
	}
}

function kmlgGetLocalizeArray($key,$list,$fields,$captchaKey){
	if(get_locale()=="it_IT")
    	$codes=file_get_contents(dirname(__FILE__).'/languages/country_codes_it.html');
    else
    	$codes=file_get_contents(dirname(__FILE__).'/languages/country_codes_en.html');

    $subs_obj_array=array( 
    	'subscribe_text'=>__('Subscribe','kloudymail'),
    	'subscribed'=>__('You have successfully subscribed to our newsletter!','kloudymail'),
    	'subscribed_notify'=>__('You subscribed to our newsletter, please check your email and activate your subscription','kloudymail'),
    	'error'=>__('Error','kloudymail'),
    	'errorExists'=>__('This email address is already registered','kloudymail'),
    	'errorWrong'=>__('Check the fields!','kloudymail'),
    	'errorCpatcha'=>__('Captcha check failed','kloudymail'),
    	'key' => $key,
    	'list'=>$list,
    	'fields'=>$fields,
    	'captchaKey'=>$captchaKey,
    	'countryCodes'=>$codes,
	);

    return $subs_obj_array;
}
?>