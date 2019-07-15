<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends REST_Controller{

	protected $data       = array();
	protected $status     = 200;
	protected $total_rows = false;
	protected $message    = '';

	public function __construct(){
		parent::__construct();
		$session_data = $this->get_user_info();
		if( !isset( $session_data[ 'id' ] ) ) {

			$controller = implode(
			    '/', [
			    $this->router->directory,
			    $this->router->class,
			    $this->router->method
			]);

			# Remove any double slashes for safety
			$controller = str_replace('//', '/', $controller);

			if( $controller !== "api/user/login" && !isset( $session_data[ 'id' ] ) ){
				$this->message = 'Please login';
				$this->status  = 400;
				$this->send();
				return;
			}
		}
	}

	protected function get_role_id( $role = 'sa' ){
		switch( $role ){
			case 'sa':
				return 1;
			case 'company':
				return 2;
			case 'restaurant':
				return 3;
			case 'customer':
				return 4;
		}
	}

	protected function send(){
		$data = array(
			'data'    => $this->data,
			'message' => $this->message,
			'status'  => $this->status,
		);

		if( $this->total_rows ){
			$data[ 'total_rows' ] = $this->total_rows;
		}
		
		$this->response( $data, $this->status );
	}

	protected function get_user_info( $key = false ){
		$session = $this->session->userdata();
		if( $key ){
			return ( isset( $session[ $key ] ) ) ? $session[ $key ] : false;
		}
		return $session;
	}
	
	protected function get_limit( $page = 1 ){
		$per_page = $this->config->item( 'per_page' );
		return array(
			$per_page,
			( $page - 1 ) * $per_page
		);
	}

	protected function get_user_id(){
		return $this->get_user_info( 'id' );
	}

	protected function get_user_role(){
		return $this->get_user_info( 'role_id' );
	}

	protected function is_sa( $role_id = false ){
		if( !$role_id ){
			$role_id = $this->get_user_role();
		}
		return $role_id == $this->get_role_id( 'sa' );
	}

	protected function is_company( $role_id = false ){
		if( !$role_id ){
			$role_id = $this->get_user_role();
		}
		return $role_id == $this->get_role_id( 'company' );
	}

	protected function is_restaurant( $role_id = false ){
		if( !$role_id ){
			$role_id = $this->get_user_role();
		}
		return $role_id == $this->get_role_id( 'restaurant' );
	}

	protected function is_customer( $role_id = false ){
		if( !$role_id ){
			$role_id = $this->get_user_role();
		}
		return $role_id == $this->get_role_id( 'customer' );
	}

	protected function set_db_error( $default = 'Something went wrong' ){
		$msg = $this->db->error();
		$this->message = ( $msg[ 'code' ] > 0 ) ? $msg[ 'message' ] : $default;
		$this->status = 400;
	}

	protected function validate( $input, $method ){
		$error = array();
		$data  = array();
		foreach ( $input as $field ) {
			$value = $this->input->$method( $field );
			if( empty($value ) ) {
				$error[ $field ] = $field . ' cannot be empty';
			}else{
				$data[ $field ] = $value;
			}
		}
		return array(
			'error' => count( $error ) > 0 ? $error : false,
			'data'  => $data
		);
	}
}