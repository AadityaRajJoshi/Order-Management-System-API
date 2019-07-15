<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item extends MY_Controller{ 

	public function __construct(){
		parent::__construct();
		$this->load->model( 'item_m' );
	}

	public function index_get( $id = false ){

		if( $this->is_restaurant() ){

			$page = $this->input->get( 'page' );
			if( !$page ){
				$page = 1;
			}

			$restaurant_id = $this->get_user_id();
			//$limit         = $this->get_limit( $page );

			$where = array(
				'restaurant_id' => $restaurant_id
			);

			// if( $id > 0 ){
			// 	$where[ 'id' ] = $id;
			// 	$limit = 1;
			// }

			$query = $this->item_m->get( '*', $where);

			if( $query ){
				$this->data = $query;

				if( !$id ){
					$this->total_rows = $this->item_m->get_count( $where );
				}
				$this->message    = 'Fetched successfully.';
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
					'name', 
				), 
				'post' 
			);
			if( $data[ 'error' ] === false ){
	 			
				$data = $data[ 'data' ];
				$data[ 'restaurant_id' ] = $this->get_user_id();

				$insert_id = $this->item_m->save( $data );
				if( $insert_id ){
					$this->data = $this->item_m->get( '*', array( 'id' => $insert_id ), 1 );
					$this->message = 'Item created successfully.';
				}else{
					$this->set_db_error( 'Failed to create Item.' );
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
					$this->item_m->table . '.id' => $id,
					$this->item_m->table . '.restaurant_id' => $this->get_user_info( 'id' )
				);

				$affected = $this->item_m->save( $data, $where );

			if( $affected ){
				$this->data = $this->item_m->get( '*', $where, 1 );
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

	public function index_delete( $id = false ){
		if( $id > 0 && $this->is_restaurant() ){

			$affected = $this->item_m->delete( array(
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
