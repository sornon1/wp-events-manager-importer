<?php class EMI_Manager{

	private $assocLocationId;

	public function getAssocLocationId () {
		return $this->assocLocationId;
	}

	private function xlsxtimeToDate($xlsxtime) {
		$tz = new DateTimeZone(get_option( 'timezone_string' ));
		$timeOffset = $tz->getOffset(new DateTime);
		return date_i18n('d/m/Y H:i:s', (($xlsxtime - 25569) * 86400) - $timeOffset);
	}

	private function getDefault() {
		$locationDetail = array();

		$locationDetail['state_name'] = '...';
		$locationDetail['country'] = '';
		$locationDetail['lat'] = '...';
		$locationDetail['lng'] = '...';

		return $locationDetail;
	}

	private function getGeocoding($r) {
		$host = "http://maps.googleapis.com/";
		$geocoding = json_decode(file_get_contents($host.'maps/api/geocode/json?sensor=false&address='.urlencode($r)));
		if( (isset ($geocoding->status)) && ($geocoding->status == 'OK') ) {
			$locationDetail = array();
			$geometry = $geocoding->results[0]->geometry;
			$address_components = $geocoding->results[0]->address_components;
			$address = array();
			foreach ($address_components as $components) {
				$address[$components->types[0]] = array ('short_name' => $components->short_name, 'long_name' =>  $components->long_name);
			}

			$address['lat'] = (String) $geometry->location->lat;
			$address['lng'] = (String) $geometry->location->lng;

			$locationDetail['address'] = (($address['route']['long_name'] != 'false') && ($address['route']['long_name'] != '')) ?
				$address['route']['long_name'] : '...';
			$locationDetail['city'] = (($address['postal_town']['long_name'] != 'false') && ($address['postal_town']['long_name'] != '')) ?
				$address['postal_town']['long_name'] : '...';
			$locationDetail['state_name'] = (($address['administrative_area_level_1']['long_name'] != 'false') && ($address['administrative_area_level_1']['long_name'] != '')) ?
				$address['administrative_area_level_1']['long_name'] : '...';
			$locationDetail['country'] = (($address['country']['short_name'] != 'false') && ($address['country']['short_name'] != '')) ?
				$address['country']['short_name'] : '...';
			$locationDetail['postcode'] = (($address['postal_code']['short_name'] != 'false') && ($address['postal_code']['short_name'] != '')) ?
				$address['postal_code']['short_name'] : '...';
			$locationDetail['lat'] = (($address['lat'] != 'false') && ($address['lat'] != '')) ?
				$address['lat'] : '...';
			$locationDetail['lng'] = (($address['lng'] != 'false') && ($address['lng'] != '')) ?
				$address['lng'] : '...';
			return $locationDetail;
		} else {
			return $this->getDefault();
		}
	}

	public function getLocationArray ($xlsx, $geocoding = false) {
		$this->assocLocationId = array( '' => 0 );
		$em_locations = array();
		global $wpdb;
		$maxId = $wpdb->get_var( $wpdb->prepare(
				'SELECT location_id FROM ' .EM_LOCATIONS_TABLE. ' WHERE %s IS NOT NULL ORDER BY location_id DESC LIMIT 0,1',
				'location_id'
			));
		$id = $maxId;
		foreach($xlsx->rows() as $k=> $r) {
			if ($id == $maxId) { $id++; continue; }
			$locationDetail = ($geocoding ? $this->getGeocoding($r[1]) : array());
			$exploded = explode("-", $r[1]);
			array_push($em_locations, array(
				'location_id' => $id,
				'location_status' => 'publish',
				'location_name' => isset($exploded[0]) ? $exploded[0] : null,
				'location_address' => isset($locationDetail['address']) ? $locationDetail['address'] : (isset($exploded[1]) ? $exploded[1] : '...'),
				'location_town' => isset($locationDetail['city']) ? $locationDetail['city'] : (isset($exploded[2]) ? $exploded[2] : '...') ,
				'location_postcode' => isset($locationDetail['postcode']) ? $locationDetail['postcode'] : (isset($exploded[3]) ? $exploded[3] : '...'),
				'location_region' => isset($locationDetail['state_name']) ? $locationDetail['state_name'] : '...',
				'location_country' => isset($locationDetail['country']) ? $locationDetail['country'] : '...',
				'location_latitude' => isset($locationDetail['lat']) ? $locationDetail['lat'] : '...',
				'location_longitude' => isset($locationDetail['lng']) ? $locationDetail['lng'] : '...',
				'post_content' => null,

			));
			$this->assocLocationId[($r[1])] = $id;
			$id++;
		}
		return $em_locations;
	}

	public function getEventArray ($xlsx) {
		$em_events = array();
		global $wpdb;
		$maxId = $wpdb->get_var( $wpdb->prepare(
				'SELECT event_id FROM ' .EM_EVENTS_TABLE. ' WHERE %s IS NOT NULL ORDER BY event_id DESC LIMIT 0,1',
				'event_id'
			));
		$id = $maxId;
		foreach($xlsx->rows() as $k=> $r) {
			if ($id == $maxId) {
				$id++;
			} else {
				$startTimeExploded = explode(' ', $this->xlsxtimeToDate($r[2]));
				$endTimeExploded = explode(' ', $this->xlsxtimeToDate($r[3]));
				array_push($em_events, array(
					'event_id' => $id,
					'event_owner' => 1,
					'event_name' => isset($r[0]) ? $r[0] : '...',
					'event_start_time' => isset($startTimeExploded[1]) ? $startTimeExploded[1] : '08:00:00',
					'event_end_time' => isset($endTimeExploded[1]) ? $endTimeExploded[1] : '18:00:00',
					'event_all_day' => isset($endTimeExploded[0]) ? 0 : 1,
					'event_start_date' => isset($startTimeExploded[0]) ? $startTimeExploded[0] : '01/01/1970',
					'event_end_date' => isset($endTimeExploded[0]) ? $endTimeExploded[0] : $startTimeExploded[0],
					'post_content' => isset($r[4]) ? $r[4] : '...',
					'post_status' => 'publish',
					'event_rsvp' => 0,
					'event_rsvp_date' => null,
					'event_rsvp_time' => "00:00:00",
					'event_spaces' => 0,
					'event_category_id' => null,
					'event_attributes' => /*array()*/ null,
					'recurrence' => 0,
					'recurrence_interval' => null,
					'recurrence_freq' => null,
					'recurrence_byday' => null,
					'recurrence_byweekno' => null,
				));
				$id++;
			}
		}
		return $em_events;
	}

	/**
	 * Parses a CSV file and translates between field data and Events Manager Importer expected result.
	 *
	 * @param string $filepath Path to the CSV file to be parsed.
	 * @param array $params Options for parsing.
	 * @return array An array of two elements: "events", which is an array containing the events data, and "location", similarly.
	 */
	// TODO: Parameterize mapping from fields in CSV file to elements in return arrays.
	function parseCsv ($filepath, $params = array('null_string' => '\N', 'null_value' => null)) {
		$ret = array(); // Array to be returned.
		// Initialize data arrays.
		$location = array();
		$events = array();

		$f = fopen($filepath, 'r');
		$r = array();
		while (($data = fgetcsv($f)) !== false)
		{
		    $r[] = $data;
		}

		$i_e = 0;
		foreach ($r as $v) {

			$evs = array();
			$evs['event_id']            = $i_e + 1;
			$evs['event_owner']         = 1; // Always the admin?
			$evs['event_name']          = $v[1];
			$evs['event_start_time']    = date('H:i:s', strtotime($v[3]));
			$evs['event_end_time']      = date('H:i:s', strtotime($v[4]));
			$evs['event_start_date']    = date('d/m/Y', strtotime($v[3]));
			$evs['event_end_date']      = date('d/m/Y', strtotime($v[4]));
			$evs['event_all_day']       = ($params['null_string'] === $v[4]) ? 1 : 0; // No end time means "all day".

			$evs['post_content']        = stripslashes($v[6]);
			$evs['event_rsvp']          = 0;
			$evs['event_rsp_date']      = null;
			$evs['event_spaces']        = null;
			$evs['event_category_id']   = $v[7];
			$evs['event_attributes']    = null;
			$evs['recurrence']          = 0;
			$evs['recurrence_interval'] = null;
			$evs['recurrence_freq']     = null;
			$evs['recurrence_byday']    = null;
			$evs['recurrence_byweekno'] = null;


			// ACF meta fields for event - ESTONIAN
			$evs['meta_est'] = [];
			$evs['meta_est']['asukoht'] = $v[8];
			$evs['meta_est']['sihtgrupp'] = $v[9];
			$evs['meta_est']['korraldaja'] = $v[10];
			$evs['meta_est']['korraldaja_email'] = $v[11];
			$evs['meta_est']['urituse_info'] = $v[12];
			$evs['meta_est']['osalemise_info'] = $v[13];
			$evs['meta_est']['valine_link'] = $v[5];


			// ACF meta fields for event - RUSSIAN
			$evs['meta_rus'] = [];
			$rus_title = $v[14];
			$evs['meta_rus']['venekeelne_info'] = strlen(trim($rus_title)) > 0;
			$evs['meta_rus']['title_ru'] = $rus_title;
			$evs['meta_rus']['asukoht_ru'] = $v[15];
			$evs['meta_rus']['sihtgrupp_ru'] = $v[16];
			$evs['meta_rus']['korraldaja_ru'] = $v[17];
			$evs['meta_rus']['korraldaja_email'] = $v[18];
			$evs['meta_rus']['urituse_info_ru'] = $v[19];
			$evs['meta_rus']['osalemise_info_ru'] = $v[20];

			$events[] = $evs;
			$i_e++;
		}

		$ret['location'] = $location;
		$ret['events'] = $events;
		return $ret;
	}
}
