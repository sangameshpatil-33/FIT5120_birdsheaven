<?php
if( ! class_exists( 'CPRO_Moosend' ) ) {
    class CPRO_Moosend {

        /**
         * The Base URL for the API.
         *
         * @since 1.3.0
         * @var string $root
         */
        public $root = 'https://gateway.services.moosend.com/';

        /**
         * The API Key.
         *
         * @since 1.3.0
         * @var string $api_key
         */
        public $api_key = null;

        /**
         * Constructor.
         *
         * @since 1.3.0
         * @param string $apikey
         */
        public function __construct( $apikey = null ) {
            
            if ( ! $apikey ) {
                throw new \Exception( "You must provide a Moosend API Key" , 1 );
            }

            $this->api_key = $apikey;
        }

        /**
         * Make a connection call to Moosend.
         *
         * @since 1.3.0
         * @return array {
         *      @type bool|string $error The error message or false if no error.
         * }
         */
        public function connect() {

            $request = $this->root . 'user/api-key/' . $this->api_key;
            $list_opts = array(
                    'headers' => array(
                        'Accept'   => 'application/json',
                        'X-ApiKey' => $this->api_key
                    ),
             );

            $resp = wp_remote_get( $request, $list_opts );

            $response_code = wp_remote_retrieve_response_code( $resp );

            if( 200 !== $response_code ) {
                throw new \Exception( __( 'This is not a valid API Key.', 'convertpro-addon' ) , 1 );
            }

            return true;
        }

        /**
         * Get lists.
         *
         * @since 1.3.0
         * @return array List.
         */
        public function getList() {

            $response = array();

            $request = $this->root . 'members/lists?pageSize=100';
            $list_opts = array(
                    'headers' => array(
                        'X-ApiKey' => $this->api_key
                    ),
             );

            $resp = wp_remote_get( $request , $list_opts );

            $response_code = wp_remote_retrieve_response_code( $resp );

            $body = wp_remote_retrieve_body( $resp );
            $data = json_decode( $body );

            if( 200 !== $response_code || empty( $data->Items ) ) {
                throw new \Exception( __( 'It seems like there are no lists present in your account.', 'convertpro-addon' ) , 1 );
            }

            $response['Items'] = $data->Items;

            return $response;
        }

        /**
         * Subscribe an email address to Moosend.
         *
         * @since 1.3.0
         * @param string $data The other data.
         * @param string $api The Api key.
         * @return array {
         *      @type bool|string $error The error message or false if no error.
         * }
         */
        public function subscribe( $data, $api ) {

            $returnArray = array( 'error' => false );

            $endpoint = $this->root . 'members/subscribe/';

            $response = wp_remote_post(
                $endpoint, 
                array(
                    'headers' => array( 'X-ApiKey' => $this->api_key ),
                    'body'    => json_encode( $data )
                ));

            $response_code = wp_remote_retrieve_response_code( $response );

            if( ! is_wp_error( $response ) ) {
                $response_arr = json_decode( $response['body'] );

                if( isset( $response['response'] ) ) {
                    $code = isset( $response['response']['code'] ) ? $response['response']['code'] : false;
                    if ( isset($code) && $code !== 200 ) {
                        throw new \Exception( "It seems like the list has been changed or deleted.", 1 );    
                    }
                }
            } else {
                $returnArray['error'] = true;            
            }

            return $returnArray;
        }
    }
}
