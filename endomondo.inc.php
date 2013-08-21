<?php
class Endomondo {
	private $config = null;
	private $ua_string = null;
	public $sports_map = array(
		2 => 'Cycling, sport',
		1 => 'Cycling, transport',
		14 => 'Fitness walking',
		15 => 'Golfing',
		16 => 'Hiking',
		21 => 'Indoor cycling',
		9 => 'Kayaking',
		10 => 'Kite surfing',
		3 => 'Mountain biking',
		17 => 'Orienteering',
		19 => 'Riding',
		5 => 'Roller skiing',
		11 => 'Rowing',
		0 => 'Running',
		12 => 'Sailing',
		4 => 'Skating',
		6 => 'Skiing, cross country',
		7 => 'Skiing, downhill',
		8 => 'Snowboarding',
		20 => 'Swimming',
		18 => 'Walking',
		13 => 'Windsurfing',
		22 => 'Other',
		23 => 'Aerobics',
		24 => 'Badminton',
		25 => 'Baseball',
		26 => 'Basketball',
		27 => 'Boxing',
		28 => 'Climbing stairs',
		29 => 'Cricket',
		30 => 'Elliptical training',
		31 => 'Dancing',
		32 => 'Fencing',
		33 => 'Football, American',
		34 => 'Football, rugby',
		35 => 'Football, soccer',
		49 => 'Gymnastics',
		36 => 'Handball',
		37 => 'Hockey',
		48 => 'Martial arts',
		38 => 'Pilates',
		39 => 'Polo',
		40 => 'Scuba diving',
		41 => 'Squash',
		42 => 'Table tennis',
		43 => 'Tennis',
		44 => 'Volleyball, beach',
		45 => 'Volleyball, indoor',
		46 => 'Weight training',
		47 => 'Yoga',
		50 => 'Step counter',
		87 => 'Circuit Training',
		88 => 'Treadmill running',
		89 => 'Skateboarding',
		90 => 'Surfing',
		91 => 'Snowshoeing',
		92 => 'Wheelchair',
		93 => 'Climbing',
		94 => 'Treadmill walking'
	);
	function __construct($config) {
		$this->config=$config;
		$this->ua_string="Dalvik/1.6.0 (Linux; U; ". $config['os'] ." ". $config['os_version'] ."; ". $config['model'] ." Build/GRI40)";
		$this->getAuthToken();
	}
	private function makeRequest($url, $method=null, $params=null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->ua_string);
		if($params!=null) {
			if($method=='POST') {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			} else {
				if(!strpos($url, '?')) $url.='?';
				else $url.="&";
				$url.=http_build_query($params);
			}
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		if(!$response = curl_exec($ch)) {
			echo "Something went wrong. CURL Error: ".curl_error($ch);
			curl_close($ch);
			return false;
		} else {
			curl_close($ch);
			switch ($params['compression']) {
				case 'gzip':
					return gzinflate(substr($response, 10));
					break;
				case 'deflate':
					return gzinflate(substr($response, 2));
					break;
				default:
					return $response;
					break;
			}		
		}
	}
	private function requestAuthToken() {
		$params = array(
			'email' =>		$this->config['email'],
			'password' =>		$this->config['password'],
			'country' =>		$this->config['country'],
			'deviceId' =>		$this->config['device_id'],
			'os' =>			$this->config['os'],
			'appVersion' =>	$this->config['app_version'],
			'appVariant' =>	$this->config['app_variant'],
			'osVersion' =>		$this->config['os_version'],
			'model' =>		$this->config['model']
		);
		$url=$this->config['endomondo_host']."/mobile/auth?v=2.4&action=PAIR";
		$response=$this->makeRequest($url,"GET",$params);
		$lines=explode("\n", $response);
		if($lines[0]=="OK") foreach ($lines as $line) {
			if(substr($line, 0, 9)=="authToken") return substr($line, 10);
		}
		else {
			echo "Failed to obtain authToken from endomondo.";
			return false;
		}
	}
	private function getAuthToken() {
		if($this->config['auth_token']!=null) return $this->config['auth_token'];
		return $this->config['auth_token'] = $this->requestAuthToken();
	}
	function getActivities($limit=15) {
		$url=$this->config['endomondo_host']."/mobile/api/feed";
		$params = array(
			'authToken' =>	$this->getAuthToken(),
			'maxResults' =>	$limit,
			'language' =>		'pl',
			'show' =>		'tagged_users,pictures'
		);
		return json_decode($this->makeRequest($url,"GET",$params))->data;
	}
	function getFriendsSummary() {
		$url=$this->config['endomondo_host']."/mobile/friends";
		$params = array(
			'authToken' =>	$this->getAuthToken(),
			'language' =>		'pl'
		);
	}
	function getMyWorkouts($limit=20) {
		$url=$this->config['endomondo_host']."/mobile/api/workouts";
		$params = array(
			'authToken' =>	$this->getAuthToken(),
			'fields' =>		'device,simple,basic,lcp_count',
			'maxResults' =>	$limit,
			'gzip' =>		'true',
			'compression' =>	'gzip'
		);
		return json_decode($this->makeRequest($url,"GET",$params))->data;
	}
	function getWorkoutDetails($id) {
		$url=$this->config['endomondo_host']."/mobile/api/workout/get";
		$params = array(
			'authToken' =>	$this->getAuthToken(),
			'fields' =>		'device,simple,basic,motivation,interval,points,lcp_count,tagged_users,pictures',
			'workoutId' =>		$id,
			'gzip' =>		'true',
			'compression' =>	'gzip'
		);
		//$params['fields']="device,simple,basic,motivation,interval,hr_zones,weather,polyline_encoded_small,points,lcp_count,tagged_users,pictures,feed";
		return json_decode($this->makeRequest($url,"GET",$params));
	}
}
?>