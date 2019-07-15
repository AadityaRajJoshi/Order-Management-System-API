<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends MY_Controller{ 

	public function __construct(){
		parent::__construct();
		$this->load->model( 'category_m' );
	}

	public function index_get( $id = false ){

		if( $this->is_restaurant() ){

			$where = false;
			$limit = false;

			if( $id > 0 ){
				$where[ 'id' ] = $id;
				$limit = 1;
			}

			$query = $this->category_m->get( '*', $where, $limit );

			if( $query ){
				$this->data    = $query;
				$this->message = 'Fetched successfully.';
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
		if( $this->is_restaurant() ){

			$data = $this->validate( 
				array(
					'name'
				), 
				'post' 
			);

			if( $data[ 'error' ] === false ){
	 			
				$data = $data[ 'data' ];
				$data[ 'restaurant_id' ] = $this->get_user_id();

				$insert_id = $this->category_m->save( $data );
				if( $insert_id ){
					$this->data    = $this->category_m->get( '*', array( 'id' => $insert_id ), 1 );
					$this->message = 'Category created successfully.';
				}else{
					$this->set_db_error( 'Failed to create Category.' );
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

	public function index_put( $id = false ){

		if( $id > 0 && $this->is_restaurant() ){

			$data = $this->validate( 
				array(
				'name' 
				), 
				'input_stream' 
			);

            if( $data[ 'error' ] === false ){
				$data = $data[ 'data' ];
				$where = array(
					'id' => $id,
					'restaurant_id' => $this->get_user_info( 'id' )
				);
			$affected = $this->category_m->save( $data, $where );

			if( $affected ){
				$this->data = $this->category_m->get( '*', $where, 1 );
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
			$this->inavlid();
		}	

		$this->send();
		return;
	}

	public function index_delete( $id = false ){
		if( $id > 0 && $this->is_restaurant() ){

			$affected = $this->category_m->delete( array(
				'id' => $id,
				'restaurant_id' => $this->get_user_info( 'id' )
			));

			if( $affected ){
				$this->message = 'Deleted successfully.';
			}else{
				$this->set_db_error( 'Item not found' );
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
