<?php
/**
 * Zapier Class
 */

if( !class_exists('CPRO_Zapier') ) {
	class CPRO_Zapier {
	
		//Class variables
		private $webhook_url;
		
		/*
		 * Function Name: __construct
		 * Function Description: Constructor
		 */
		
		public function __construct(array $config) {

			$webhook_url   = @$config['webhook_url'];
			
			//error checking
			if ( empty($webhook_url) ) {
				throw new \Exception("Required config parameter [webhook_url] is not set or empty", 1);
			}

			$this->webhook_url   = $webhook_url;
		} // __construct ends
		
		/*
		 * Function Name: subscribe_zapier
		 * Function Description: Subscribe a user
		 */
		
		public function subscribe_zapier( $name, $customfields = array() , $email = '' ) {
			if( $email != '' ){
				$postarr = array_merge( $customfields, array(
						'api_key' => $this->api_key,
						'email'   => $email,
						'name'    => $name,
						'list'    => $this->list_id,
						'boolean' => 'true'
						) );
				$postdata = http_build_query( $postarr );
				
				$ch = curl_init ( $this->project_name . '/subscribe' );
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
				$result = curl_exec($ch);
				
				if( $result == 1 ) {
					$status = 1;
				} else {
					$status = 0;
				}
				return array(
							'status' => $status,
							'message' => $result
						);
				
			} else {
				return array(
							'status' => 0,
							'message' => 'Email field is empty.'
						);
			}
		}//subscribe_zapier ends
		
	} //class ends
}//if ends