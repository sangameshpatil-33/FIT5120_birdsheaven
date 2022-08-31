<?php

class CPRO_ElasticEmail {

    public $apikey;
    public $accountid;
    public $root  = '';
    public $debug = false;

    public function __construct( $apikey = null, $accountid = null ) {
        if ( ! $apikey ) {
            throw new \Exception("You must provide a Elastic Email API key", 1);
        }

        if ( ! $accountid ) {
            throw new \Exception("You must provide a Elastic Email Account ID", 1);
        }

        $this->apikey = $apikey;
        $this->accountid = $accountid;
    }

    public function connect() {

        $response = array( 'error' => false );

        $api_url = 'https://api.elasticemail.com/v2/list/list?apikey=';
        $url = $api_url . $apikey;

        $list_opts = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'apikey ' . $this->apikey
            ),
            'body' => array()
        );

        $resp = wp_remote_get( $api_url, $list_opts );

        // Test For Wp Errors

        if( !is_wp_error( $resp ) ) {
            $body = wp_remote_retrieve_body( $resp );
            $request = json_decode( $body );

                if( isset( $resp['response']['code'] ) ) {
                    if( $resp['response']['code'] != 200  ) {
                        // Not Connected
                        $response['error'] = $resp['response']['message'];
                    }
                } else {
                    $response['error'] = $request->detail;
                }

        }else {
            // Not Connected
            $response['error'] = $resp->get_error_message();
        }
        return $response;
    }

    public function getList( $apikey = '', $listid = '' ) {

        $response = array( 'error' => false );

        $api_url = 'https://api.elasticemail.com/v2/list/list?apikey=';
        $url = $api_url . $apikey;

        $list_opts = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => array()
        );

        $resp = wp_remote_get( $url, $list_opts );

        // Test For Wp Errors
        if( !is_wp_error( $resp ) ) {
            $body = wp_remote_retrieve_body( $resp );
            $request = json_decode( $body );

            if( isset( $resp['response']['code'] ) ) {
                if( $resp['response']['code'] == 200  ) {
                    // Not Connected
                    $response['error'] = $resp['response']['message'];
                           $response['error'] = false;
                    $response['lists'] = $request->data;
                }
            } else {
                // Not Connected
                $response['error'] = $request->detail;
            }

        }else {
            // Not Connected
            $response['error'] = $response->get_error_message();
        }

        return $response;
    }

    public function cp_v2_request_elastic_data( $method , $json_data ){

        $req_url   = 'https://api.elasticemail.com/v2/'.$method.'?'.$json_data;
        $opts = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => array(),               
        );
        $response = wp_remote_get( $req_url, $json_data ); 
        if( is_wp_error( $response ) ) {
            return array();
            exit;
        }

        $response_arr = wp_remote_retrieve_body( $response );
        $request = json_decode( $response_arr );
        return $request;
    }

    public function cp_v2_get_modified_data( $data ){            
        $dataAttributes = array_map(function( $value, $key ) {
            return $key.'='.$value.'';
        }, array_values( $data ), array_keys( $data ));

        $data = implode( '&', $dataAttributes );
        return $data;
    }
}