<?php

/**
 * Get field key for field name.
 * Will return first matched acf field key for a give field name.
 * 
 * ACF somehow requires a field key, where a sane developer would prefer a human readable field name.
 * http://www.advancedcustomfields.com/resources/update_field/#field_key-vs%20field_name
 * 
 * This function will return the field_key of a certain field.
 * 
 * @param $field_name String ACF Field name
 * @param $post_id int The post id to check.
 * @return 
 */
function acf_get_field_key($field_name, $post_id) {
	global $wpdb;
	$acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_parent,post_name FROM $wpdb->posts WHERE post_excerpt=%s AND post_type=%s" , $field_name , 'acf-field' ) );
	// get all fields with that name.
	switch ( count( $acf_fields ) ) {
		case 0: // no such field
			return false;
		case 1: // just one result. 
			return $acf_fields[0]->post_name;
	}
	// result is ambiguous
	// get IDs of all field groups for this post
	$field_groups_ids = array();
	$field_groups = acf_get_field_groups( array(
		'post_id' => $post_id,
	) );
	foreach ( $field_groups as $field_group )
		$field_groups_ids[] = $field_group['ID'];
	
	// Check if field is part of one of the field groups
	// Return the first one.
	foreach ( $acf_fields as $acf_field ) {
		if ( in_array($acf_field->post_parent,$field_groups_ids) )
			return $acf_field->post_name;
	}
	return false;
}
class EMI_Event extends EMI{
	/* Field Names */
	protected $event_id;
	protected $db_event_id;
	protected $db_event_slug;
	protected $db_event_owner;
	protected $db_event_name="";
	protected $db_post_content="";
	protected $db_event_start_time="08:00:00";
	protected $db_event_end_time="18:00:00";
	protected $db_event_all_day=0;
	protected $db_event_start_date;
	protected $db_event_end_date;
	protected $db_event_rsvp;
	protected $db_event_rsvp_date;
	protected $db_event_rsvp_time = "00:00:00";
	protected $db_event_spaces;
	protected $db_location_id;
	protected $db_recurrence_id;
	protected $db_event_status;
	protected $db_event_date_created;
	protected $db_event_date_modified;
	protected $db_blog_id=0;
	protected $db_group_id=0;
	protected $start;
	protected $end;
	protected $db_post_category;
	protected $meta_est;
	protected $meta_rus;
	/**
	 * Populated with the non-hidden event post custom fields (i.e. not starting with _)
	 * @protected array
	 */
	protected $event_attributes = array();

	/* Recurring Specific Values */
	protected $db_recurrence;
	protected $db_recurrence_interval;
	protected $db_recurrence_freq;
	protected $db_recurrence_byday;
	protected $db_recurrence_days = 0;
	protected $db_recurrence_byweekno;

	/* anonymous submission information */
	protected $event_owner_anonymous;
	protected $event_owner_name;
	protected $event_owner_email;

	/* Post protectediables - copied out of post object for easy IDE reference */
	protected $Post;
	protected $post_status='publish';

	function __construct($datas,$location_id) {
		parent::__construct();
		$this->db_event_name=__("Untitled","emi");
		$this->hydrate($datas);
		$this->format_date();
		$this->event_attributes='a:1:{s:25:"custom_style_field_select";s:0:"";}';
		$this->db_event_owner=$this->get_current_user_id();
		$this->start = strtotime($this->db_event_start_date." ".$this->db_event_start_time);
		$this->end = strtotime($this->db_event_end_date." ".$this->db_event_end_time);
		$this->db_location_id=$location_id;
	}

	private function hydrate($datas){
		foreach ($datas as $key=>$value){
			$this->$key=$value;
		}
	}

	private function format_date(){
		$this->db_event_start_date = date(
			'Y-m-d',
			strtotime(
				str_replace("/", "-", $this->db_event_start_date)
			)
		);
		$this->db_event_end_date = date(
			'Y-m-d',
			strtotime(
				str_replace("/", "-", $this->db_event_end_date)
			)
		);
	}

	function get_attribute($attribute){
		return $this->$attribute;
	}

	function get_post_attribute($attribute){
		return $this->Post->$attribute;
	}

	function save(){
		global $wpdb;
		//create the post
		$slug = sanitize_title_with_dashes($this->db_event_name);
		$post_array=array(
			"post_type"=>EM_POST_TYPE_EVENT,
			"post_title"=>$this->db_event_name,
			"post_name"=>$slug,
			"post_content"=>$this->db_post_content,
			"post_status"=>$this->post_status
		);
		$this->Post = $this->createPost($post_array);
		if (empty($this->Post)){return 2;}
		$post_id = $this->Post->ID;
		if ($this->db_post_category) {
			$category = get_term_by('name', $this->db_post_category, 'event-categories');
			if ($category !== false) {
				wp_set_post_terms($post_id, [(int)$category->term_id], 'event-categories');
			}
		}
		$this->db_event_slug=$this->Post->post_name;
		// first we get an event array READY TO BE INSERTED
		$event_array=$this->get_event_array($this->Post);
		if (empty($event_array)){return 3;}
		// insert in em events table
		$em_insert=$wpdb->insert(EM_EVENTS_TABLE, $event_array);
		if (!(bool)$em_insert){return 4;}
		// add posts metas
		$this->add_post_metas($this->Post);
		// add ACF metas
		$ACFMetas = $this->add_ACF_metas($post_id);
		if(!$ACFMetas) {
			return 6;
		}
		// update status of the post to publish
		$post_status = 'publish';
		$unique_slug = wp_unique_post_slug($this->Post->post_name, $post_id, $post_status, 'event', 0);
		$publish_post=$wpdb->update( $wpdb->posts, array( 'post_status' => $post_status, 'post_name' => $unique_slug), array( 'ID' => $this->Post->ID ) );
		(bool)($publish_post);
		if (!$publish_post){return 5;}
		return 1;
	}

	function get_current_user_id(){
		global $current_user;
      	get_currentuserinfo();
      	return $current_user->ID;
	}

	function get_event_array($Post){

		$event_array=array();
		foreach(get_object_vars($this) as $key => $value){
			$oldkey=$key;
			$key=preg_replace('#^db_(.+)#',"$1", $key);
			if ($key!=$oldkey){
				$event_array[$key]=$value;
			}
		}
		if (empty($Post)){return $event_array;}
		$event_array["post_id"]=$Post->ID;
		$event_array["event_status"]=$this->get_event_status($Post->status);
		$event_array["event_attributes"]=json_encode($this->event_attributes);
		unset($event_array["post_category"]);
		unset($event_array["event_id"]);
		return $event_array;
	}

	function get_event_status($status){
		switch($status){
			case "publish":
				return 1;
			break;
			case "pending":
				return 0;
			break;
			default :
				return 1;
			break;
		}
	}

	function add_post_metas($Post){
		$event_array=$this->get_event_array($Post);
		$event_array["event_id"]=$this->get_event_id($Post->ID);
		foreach ($event_array as $key => $value) {
			update_post_meta($this->Post->ID, "_".$key, $value);
		}
		update_post_meta($Post->ID, '_start_ts', str_pad($this->start, 10, 0, STR_PAD_LEFT));
		update_post_meta($Post->ID, '_end_ts', str_pad($this->end, 10, 0, STR_PAD_LEFT));
	}

	function add_ACF_metas($post_id) {
		if (!class_exists('acf')) {
			return false;
		}

		$est_keys = ['asukoht', 'sihtgrupp', 'korraldaja', 'korraldaja_email', 'urituse_info', 'osalemise_info', 'valine_link'];
		$meta_est = [];
		foreach ($this->meta_est as $index => $value) {
			$key = $est_keys[$index];
			$meta_est[$key] = $value;
		}

		foreach ($meta_est as $key => $value) {
			update_field(acf_get_field_key($key, $post_id), $value, $post_id);
		}

		$rus_keys = ['venekeelne_info', 'title_ru', 'asukoht_ru', 'sihtgrupp_ru', 'korraldaja_ru', 'korraldaja_email', 'urituse_info_ru', 'osalemise_info_ru'];
		$meta_rus = [];
		foreach ($this->meta_rus as $index => $value) {
			$key = $rus_keys[$index];
			$meta_rus[$key] = $value;
		}

		$has_russian_meta = (bool)$meta_rus['venekeelne_info'];
		if ($has_russian_meta) {
			unset($meta_rus['venekeelne_info']);
			update_field(acf_get_field_key('venekeelne_info', $post_id), $has_russian_meta, $post_id);
			foreach ($meta_rus as $key => $value) {
				update_field(acf_get_field_key($key, $post_id), $value, $post_id);
			}
		}
		return true;
	}

	function get_event_id($post_id){
		global $wpdb;
		$query=$wpdb->prepare('SELECT event_id FROM '.EM_EVENTS_TABLE.' WHERE post_id = %d', $post_id);
		return $wpdb->get_var($query);
	}

	function exist(){
		global $wpdb;
		$query =$wpdb->prepare(
			'SELECT event_id FROM '.EM_EVENTS_TABLE.' WHERE event_name = %s AND event_start_date = %s AND event_start_time = %s',
			$this->db_event_name,
			$this->db_event_start_date,
			$this->db_event_start_time
		);
		return $wpdb->get_var($query);
	}


	function get_similis(){
		global $wpdb;
		$query =$wpdb->prepare('SELECT * FROM '.EM_EVENTS_TABLE.' WHERE event_name = %s', $this->db_event_name);
		$similis= $wpdb->get_results($query);
		$similis_array=array();
		foreach($similis as $simili){
			$simili_array=array();
			foreach ($simili as $key=>$value){
				$simili_array["db_".$key]=$value;
			}
			$simili_array["event_id"]=$simili_array["db_event_id"];
			$Simili=new EMI_Event($simili_array,0);
			array_push($similis_array, $Simili);
		}
		return($similis_array);
	}

	function delete(){
		global $wpdb;
		if (!empty($this->event_id)){
			$query = $wpdb->prepare('DELETE FROM '.EM_EVENTS_TABLE.' WHERE event_id = %d', $this->event_id);
			$wpdb->query($query);
		}
		if (!empty($this->db_post_id)){
			wp_delete_post($this->db_post_id);
		}

	}

	function get_error($code){
		switch($code) :
			case 1 :
				return __("The event was saved successfully","emi");
			break;
			case 2 :
				return __("Cannot create the article link with this event","emi");
			break;
			case 3:
				return __("Error during event table import","emi");
			break;
			case 4 :
				return __("Error while saving to the database","emi");
			break;
			case 5 :
				return __("Error while updating article status","emi");
			break;
			case 6 :
				return __("ACF plugin is not enabled - can't import custom fields","emi");
			break;
		endswitch;
	}
}
