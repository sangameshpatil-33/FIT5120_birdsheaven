<?php
if( ! class_exists( 'CPRO_SendGrid' ) ) {
    class CPRO_SendGrid {

        /**
         * The Base URL for the API.
         *
         * @since 1.2.1
         * @var string $root
         */
        public $root = 'https://api.sendgrid.com/v3/';

        /**
         * The API Key.
         *
         * @since 1.2.1
         * @var string $apikey
         */
        public $apikey = null;

        /**
         * Constructor.
         *
         * @since 1.2.1
         * @var string $apikey
         */
        public function __construct( $apikey = null ) {
            
            if ( ! $apikey ) {
                throw new \Exception( "You must provide a SendGrid API Key" , 1 );
            }

            $this->apikey = $apikey;

        }

        /**
         * Make a connection call to SendGrid.
         *
         * @since 1.2.1
         * @return array {
         *      @type bool|string $error The error message or false if no error.
         * }
         */
        public function connect() {

            $response = array( 'error' => false );

            $opts = array(
                'body' => array(
                    'api_key' => $this->apikey
                )
            );
            $request   = $this->root . 'templates';
            $list_opts = array(
                    'headers' => array(
                        'content-Type'  => 'application/json',
                        'authorization' => 'Bearer ' . $this->apikey
                    ),
             );
            $resp = wp_remote_get( $request, $list_opts );
            if( ! is_wp_error( $resp ) ) {

                $body    = wp_remote_retrieve_body( $resp );
                $request = json_decode( $body );

                if( isset( $resp['response']['code'] ) ) {
                    if( $resp['response']['code'] != 200  ) {
                        // Not Connected.
                        throw new \Exception( 'API Key not valid.' , 1 );
                    }
                } else {
                    // Not Connected.
                    throw new \Exception( __( 'Something went wrong.', 'convertpro-addon' ) , 1 );
                }

            } else {
                // Not Connected.
                throw new \Exception( $resp->get_error_message() , 1 );
            }

            return $response;
        }

        /**
         * Get lists.
         *
         * @since 1.2.1
         * @param string $api_key The Api key.
         * @return array {
         *      @type bool|string $error The error message or false if no error.
         * }
         */
        public function getList( $api_key ) {

            $response = array( 'error' => false );

            $request   = $this->root . 'marketing/lists?page_size=1000';
            $list_opts = array(
                    'headers' => array(
                        'content-Type'  => 'application/json',
                        'authorization' => 'Bearer ' . $api_key
                    ),
             );

            $resp = wp_remote_get( $request , $list_opts );

            if( ! is_wp_error( $resp ) ) {
                $body = wp_remote_retrieve_body( $resp );
                
                $request = json_decode( $body );
                
                if( isset( $resp['response']['code'] ) ) {
                    if( $resp['response']['code'] != 200  ) {
                        // Not Connected.
                        throw new \Exception( $resp['response']['message'] , 1 );
                    } else {
                        $response['lists'] = $request->result;
                    }
                } else {
                    // Not Connected.
                    throw new \Exception( __( 'Something went wrong.', 'convertpro-addon' ) , 1 );
                }
            } else {
                // Not Connected.
                throw new \Exception( $resp->get_error_message() , 1 );
            }

            return $response;
        }

        /**
         * Subscribe an email address to SendGrid.
         *
         * @since 1.2.1
         * @param int $list The list ID.
         * @param string $data The other data.
         * @param string $api The Api key.
         * @return array {
         *      @type bool|string $error The error message or false if no error.
         * }
         */
        public function subscribe( $list, $data, $api ) {

            $returnArray = array( 'error' => false );

            $user_data = array(
                "list_ids" => array(
                    $list
                ),
                "contacts" => array( $data )
            );

            $json_data = json_encode( $user_data );

            $curl = curl_init();

            curl_setopt_array( $curl, array(
                CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $json_data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'authorization: Bearer ' . $api
                ),
            ));

            $response = curl_exec( $curl );
            $err      = curl_error( $curl );

            curl_close( $curl );

            if( $err ) {
                throw new \Exception( "cURL Error #:" . $err , 1 );
            }else{
                return $returnArray;
            }
        }
    }
}
