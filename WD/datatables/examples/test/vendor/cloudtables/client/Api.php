<?php
/*! CloudTables API client
 * ©SpryMedia Ltd - MIT licensed
 */

namespace Cloudtables;

class Api {
	private $_domain = 'cloudtables.io';
	private $_duration = null;
	private $_key;
	private $_roles = [];
	private $_secure = true;
	private $_subdomain;
	private $_clientId = null;
	private $_clientName = null;
	private $_accessToken = null;

	/**
	 * Create a CloudTables API class instance
	 * @param string $subdomain Application sub-domain
	 * @param string $key API key
	 * @param array $options = [
	 *   'domain' => 'cloudtables.io', // Domain that the API should interface with (prefixed with the application id)
	 *   'duration' => 3600, // Token expire duration
	 *   'roles' => [], // Array of roles (union'ed with the roles for the API key)
	 *   'role' => null, // Single role (takes priority over `roles` if both used)
	 *   'secure' => true, // Use https or http
	 *   'clientId' => null, // Your unique identifier for the user
	 *   'clientName' => null, // Name / label to give the use in the CloudTables configuration UI
	 * ]
	 */
	function __construct($subdomain, $key, $options = null) {
		$this->_key = $key;
		$this->_subdomain = $subdomain;

		if ($options && isset($options['domain'])) {
			$this->_domain = $options['domain'];
		}

		if ($options && isset($options['duration'])) {
			$this->_duration = $options['duration'];
		}

		if ($options && isset($options['roles'])) {
			$this->_roles = $options['roles'];
		}

		if ($options && isset($options['role'])) {
			$this->_roles = array($options['role']);
		}

		if ($options && isset($options['secure'])) {
			$this->_secure = $options['secure'];
		}

		if ($options && isset($options['clientId'])) {
			$this->_clientId = $options['clientId'];
		}

		if ($options && isset($options['clientName'])) {
			$this->_clientName = $options['clientName'];
		}
	}


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Public methods
	 */

	/**
	 * Get an Access Token for CloudTables
	 * @param boolean $echoError Indicate if errors should be shown (true by default)
	 * @return string|false `false` is return if unable to get a token, otherwise
	 *   an access token is given.
	 */
	public function token($echoError = true) {
		// Caching for token reuse
		if ($this->_accessToken !== null) {
			return $this->_accessToken;
		}

		$url = $this->_url('/api/1/access');
		$data = array();

		if ($this->_duration) {
			$data['duration'] = $this->_duration;
		}

		if ($this->_clientId) {
			$data['clientId'] = $this->_clientId;
		}

		if ($this->_clientName) {
			$data['clientName'] = $this->_clientName;
		}

		$json = $this->_curl($url, 'post', $data);

		if ($json === false) {
			return false;
		}
		else if (isset($json['errors'])) {
			if ($echoError) {
				print_r($json['errors']);
			}
		}
		else {
			$this->_accessToken = $json['token'];

			return $json['token'];
		}
	}

	/**
	 * Get the data and columns for a given dataset
	 * @return array|false See API documentation for details please. False is returned if
	 *   an error occurs.
	 */
	public function data($dataset) {
		$url = $this->_url('/api/1/dataset/'. $dataset .'/data');
		return $this->_curl($url, 'get');
	}

	/**
	 * Get summary information about the available datasets
	 * @return array|false See API documentation for details please. False is returned if
	 *   an error occurs.
	 */
	public function datasets() {
		$url = $this->_url('/api/1/datasets');
		$json = $this->_curl($url, 'get');

		return isset($json['datasets'])
			? $json['datasets']
			: false;
	}

	/**
	 * Get a script tag for a dataset
	 * @param string $arg1 Access token (from `->token()`) or Dataset id
	 * @param string $arg2 Dataset id if a token is given for $arg1, the styling framework otherwise
	 * @param string $arg3 Styling framework to use for the table
	 * @return string `<script>` tag to use
	 */
	public function scriptTag($arg1, $arg2=null, $arg3=null) {
		if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $arg1) === 1) {
			$token = $this->token();
			$datasetId = $arg1;
			$style = $arg2 !== null ? $arg2 : 'd';
		}
		else {
			$token = $arg1;
			$datasetId = $arg2;
			$style = $arg3 !== null ? $arg3 : 'd';
		}

		$protocol = $this->_secure ? 'https' : 'http';

		return "<script src='{$protocol}://{$this->_subdomain}.{$this->_domain}/loader/{$datasetId}/table/{$style}' data-token='{$token}'></script>";
	}


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Private methods
	 */

	/**
	 * Perform a GET or POST request to get information. Assumes JSON will be returned
	 * @param string $url URL to query
	 * @param string $method 'get' or 'post'
	 * @param string $data Data to set to the server
	 * @return array|false Resolves JSON array or `false` on error
	 * @private
	 */
	private function _curl($url, $method, $data=array()) {
		// Add common parameters
		$data['key'] = $this->_key;

		if (count($this->_roles)) {
			$data['roles'] = $this->_roles;
		}

		if ($method === 'get') {
			$url = $url . '?' . http_build_query($data);
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		if ($method === 'post') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		}

		$result = curl_exec($ch);
		curl_close($ch);

		if ($result === false) {
			$errno = curl_errno($ch);

			if ($echoError) {
				echo 'cURL encountered an error: '.$errno."\n";

				// https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/7/html/selinux_users_and_administrators_guide/sect-managing_confined_services-the_apache_http_server-booleans
				if ($errno === 7) {
					echo "If you have SELinux enabled you need to allow httpd to access the network - e.g. `setsebool -P httpd_can_network_connect on`\n";
				}
			}

			return false;
		}
		
		return json_decode($result, true);
	}
	
	/**
	 * Get a url
	 * @param string $path The path to be prefixed by protocol, subdomain and domain.
	 * @return string The full url
	 * @private
	 */
	private function _url($path) {
		$url = ($this->_secure ? 'https://' : 'http://');
		$url .= $this->_subdomain . '.' . $this->_domain . $path;

		return $url;
	}
}
