<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require REST_CONTROLLER_PATH;

class Launches extends REST_Controller {

    function __construct()
    {
        parent::__construct();
    }

	public function index_get($parament = false)
	{
		$json_array['status'] = ERROR_CODE;

		try {
			$json_array['status'] 		= SUCCESS_CODE;
        	$json_array['parament'] 	= $parament;
        	$json_array['flight_info']  = $this->flight_info($parament);
        	$this->response($json_array, REST_Controller::HTTP_OK);

		} catch( Exception $e ) {
			log_message( 'error', $e->getMessage( ) . ' in ' . $e->getFile() . ':' . $e->getLine() );
		}
		$this->response($json_array, REST_Controller::HTTP_OK);
	}

	protected function flight_info($pagination = false) {

		$flight_info = false;

		try {
			$url = 'https://api.spacexdata.com/v3/launches';
	        $crl = curl_init();
	                  
			curl_setopt($crl, CURLOPT_URL, $url);
			curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($crl);

			if(!$response) {
				log_message( 'error', curl_error($ch) . ' in ' . curl_errno($ch) );
				return $flight_info;
			}
			curl_close($crl);
		} catch( Exception $e ) {
			log_message( 'Error to call curl API');
			log_message( 'error', $e->getMessage( ) . ' in ' . $e->getFile() . ':' . $e->getLine() );
		}

		try {

			$pagination = explode('&', $pagination);
			// directly we can fetch the page number but need to change in URL 
			// by / we can get page number and per page data
			$flight_info	= json_decode($response,true);
			$total 			= count( $flight_info );
			$page 		= isset($pagination[0]) ? (isset(explode('=',$pagination[0])[1]) ? explode('=',$pagination[0])[1] :1) : 1;
			$pagesize 	= isset($pagination[1]) ? (isset(explode('=',$pagination[1])[1]) ? explode('=',$pagination[1])[1] :10) : 10;
			$totalPages = ceil( $total / $pagesize );

			$page = max($page, 1);
			$page = min($page, $totalPages); //get last page when $_GET['page'] > $totalPages
			$offset = ($page - 1) * $pagesize;
			if( $offset < 0 ) $offset = 0;

			$flight_info = array_slice($flight_info, $offset, $pagesize);
		} catch( Exception $e ) {
			log_message( 'Error to call curl API');
			log_message( 'error', $e->getMessage( ) . ' in ' . $e->getFile() . ':' . $e->getLine() );
		}
	
		return $flight_info;
	}

	
}
