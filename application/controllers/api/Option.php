<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Option extends MY_Controller{ 

	public function __construct(){
		parent::__construct();
		$this->load->model( 'option_m' );
	}

	public function index_get( $id = false ){
		if ($this->is_sa()) {
			
			$where = false;
			$limit = false;

			if( $id > 0 ){
				$where[ 'id' ] = $id;
				$limit = 1;
			}

			$query = $this->option_m->get( '*', $where, $limit );
			if ( $query ) {
				$this->data = $query;
				$this->message = 'Fetched Sucessfully';
			}else{
				$this->set_db_error( 'No item found.' );
			}
		}else{
			$this->invalid();
		}
		$this->send();
		return;
	}

	public function index_post(){
		if( $this->is_sa() ){

			$data = $this->validate( 
				array(
					'name', 
					'value'
				), 
				'post' 
			);
			if( $data[ 'error' ] === false ){
	 			
				$data = $data[ 'data' ];

				$insert_id = $this->option_m->save( $data );
				if( $insert_id ){
					$this->data = $this->option_m->get( '*', array( 'id' => $insert_id ), 1 );
					$this->message = 'Option created successfully.';
				}else{
					$this->set_db_error( 'Failed to create Option.' );
				}
			}else{
				$this->status  = 422;
				$this->data    = $data[ 'error' ];
				$this->message = 'Validation Error';
			}
		}else{
			$this->invalid();
		}

		$this->send();
		return;
	}

	public function index_put($id = False){
		if ( $id > 0 && $this->is_sa() ) {
			
			$data = $this->validate( 
				array(
					'name', 
					'value'
				), 
				'input_stream' 
			);
			
			if( $data[ 'error' ] === false ){
					
				$data = $data[ 'data' ];
				$where = array(
					'id' => $id
				);

				$affected = $this->option_m->save( $data, $where );
				if ($affected) {
					$this->data    = $this->option_m->get( '*', $where, 1 );
					$this->message = 'Updated successfully.';
				}else{
					$this->set_db_error();
				}

			}else{
				$this->status  = 422;
				$this->data    = $data[ 'error' ];
				$this->message = 'Validation Error';
			}

		}else{
			$this->invalid();
		}

		$this->send();
		return;
	}

	public function index_delete($id = false){
		if ( $id > 0 && $this->is_sa() ) {
			
			$affected = $this->option_m->delete(array(
				'id' => $id,
				'user_id' => $this->get_user_info( 'id' )
			);

			if ($affected) {
				$this->message = 'Deleted successfully';
			}else{
				$this->set_db_error( 'Option not found' );
			}
		}else{
			$this->invalid();
		}
		$this->send();
		return;
	}

	public function invalid(){
		$this->message = 'Invalid request.';
		$this->status  = 404;
	}
}	