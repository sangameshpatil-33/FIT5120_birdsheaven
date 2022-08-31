<?php
/*
 *
 *
 */

if( !class_exists( 'CPRO_Ontraport_API_Class' ) ) {
	class CPRO_Ontraport_API_Class {
		//class variables
		private $api_url = "https://api.ontraport.com/cdata.php";
		private $api_key;
		private $app_id;
		public $http_error_code = '';

		function __construct( $api_key, $app_id ){
			$this->api_key = $api_key;
			$this->app_id = $app_id;
		}

		/**
		 * Gets contact ID for a user based on email address.
		 *
		 * @since 1.4.1
		 * @param string $email The email ID.
		 * @param array $params parameters to header.
		 * @return array {
		 *      @type bool|int $body_json The Contact ID exists return ID or false.
		 * }
		 */
		public function get_contact_id( $email_address, $params ) {

			$request      = "https://api.ontraport.com/1/object/getByEmail?objectID=0&email=" . urlencode( $email_address );
			$response     = wp_remote_get( $request, $params );
			$body_json    = json_decode( $response['body'], true );

			if ( 200 !== $response['response']['code'] || empty( $body_json['data'] ) ) {
				return false;
			} else {
				return isset( $body_json['data']['id'] ) ? $body_json['data']['id'] : false;
			}
		}

		/**
		 * Add New Contact.
		 *
		 * @since 1.4.1
		 * @param array $params parameters to header.
		 * @return array {
		 *      @type bool|int $body_json The Contact ID exists return ID or false.
		 * }
		 */
		public function add_new_contact( $params ) {

			$url = 'https://api.ontraport.com/1/Contacts/saveorupdate';

			$response = wp_remote_post( $url, $params );

			$body_json    = json_decode( $response['body'], true );

			if( 200 !== $response['response']['code'] ) {
				return array( 'status' => true , 'error' => $response['response']['message'] );
			} else {
				$contact_id = isset( $body_json['data']['id'] ) ? $body_json['data']['id'] : false;
				return array( 'status' => false , 'contact_id' => $contact_id );
			}
		}

		/**
		 * Update Contact.
		 *
		 * @since 1.4.1
		 * @param int $contact_id contact id.
		 * @param array $tags tags to apply.
		 * @param array $params parameters to header.
		 * @return array {
		 *      @type bool|int $body_json The Contact ID exists return ID or false.
		 * }
		 */
		public function apply_tags( $contact_id, $tags, $params ) {
			$post_data = array(
				'objectID' => 0,
				'ids'      => (array)$contact_id,
				'add_names' => $tags,
			);
			$params['method'] = 'PUT';
			$params['body']   = json_encode( $post_data );

			$response = wp_remote_post( 'https://api.ontraport.com/1/objects/tagByName', $params );

			if( 200 !== $response['response']['code'] ) {
				return array( 'status' => true , 'error' => $response['response']['message'] );
			} else {
				return array( 'status' => false );
			}
		}

		/**
		 * Apply tags to contact.
		 *
		 * @since 1.4.1
		 * @param array $params parameters to header.
		 * @return array {
		 *      @type bool|int $body_json The Contact ID exists return ID or false.
		 * }
		 */
		public function update_contact( $params ) {

			$request = 'https://api.ontraport.com/1/objects';

			$response = wp_remote_post( $request, $params );

			if( 200 !== $response['response']['code'] ) {
				return array( 'status' => true , 'error' => $response['response']['message'] );
			} else {
				return array( 'status' => false );
			}
		}

		function getTags() {
			$result['result'] = $this->execute_curl_call( 'pull_tag' );
			$result['status'] = true;
			if( isset( $result['result']->error ) ){
				$this->http_error_code = $result['result']->error;
				$result['status'] = false;
			}
			return $result;
		}

		function getSequences() {
			$result['result'] = $this->execute_curl_call( 'fetch_sequences', 'SEQUENCE' );
			$result['status'] = true;
			if( isset( $result['result']->error ) ){
				$this->http_error_code = $result['result']->error;
				$result['status'] = false;
			}
			return $result;
		}

		function execute_curl_call( $reqType = '', $tag = 'TAG', $postdata = array() ){

			if( $this->api_key == '' && $this->app_id == '' ) {
				$this->http_error_code = 'You must specify token parameter for the method';
			} else {
				$postargs = "appid=". $this->app_id ."&key=". $this->api_key ."&reqType=".$reqType;
				$request = "http://api.ontraport.com/cdata.php";
				$session = curl_init( $this->api_url );
				curl_setopt ($session, CURLOPT_POST, true);
				curl_setopt ($session, CURLOPT_POSTFIELDS, $postargs);
				curl_setopt($session, CURLOPT_HEADER, false);
				curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
				$response = curl_exec($session);
				curl_close($session);
				$p = xml_parser_create();
				xml_parse_into_struct($p, $response, $vals, $index);
				xml_parser_free($p);
				$response = array();
				if( isset( $index['ERROR'] ) ) {
					$this->http_error_code = 'Authentication failed.';
				}
				if( isset( $index[$tag] ) ) {
					foreach($index[$tag] as $v) {
						if( $tag == 'TAG' ) {
							$response[] = $vals[$v]['value'];
						} else {
							$response[$vals[$v]['attributes']['ID']] = $vals[$v]['value'];
						}
					}
				} else {
					return array();
				}
				return $response;
			}
			return array();
		}
	}
}