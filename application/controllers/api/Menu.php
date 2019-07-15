<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends MY_Controller{ 

	public function __construct(){
		parent::__construct();
		$this->load->model( 'menu_m' );
	}

	public function index_get( $id = false ){

		if( $this->is_restaurant() || $this->is_customer() ){

			if( $this->is_restaurant() ){
				$restaurant_id = $this->get_user_id();
			}elseif( $this->is_customer() ){
				$_restaurant_id = $this->input->get( 'restaurant_id' );
				// $this->data = $_restaurant_id;
				// $this->send();
				# check if this $_restaurant_id and this customer belongs to same company or not
				# By checking the equality of retaurant's company id and user's company_id
				$this->load->model( 'user_m' );
				$result = $this->user_m->get( '*', array( 'id' => $_restaurant_id ), 1 );
				if( $result && count( $result ) > 0 ){
					$row = $result[ 0 ];
					if( $row->company_id == $this->get_user_info( 'company_id' ) ){
						$restaurant_id = $_restaurant_id;
					}
				}
			}


			if( $restaurant_id > 0 ){

				$page = $this->input->get( 'page' );
				if( !$page ){
					$page = 1;
				}

				// $limit = $this->get_limit( $page );

				$where = array(
					$this->menu_m->table . '.restaurant_id' => $restaurant_id
				);

				// if( $id > 0 ){
				// 	$where['id' ] = $id;
				// 	$limit = 1;
				// }

				$query = $this->menu_m->get( '*', $where );
				// $this->data = $query;
				// $this->send();

				if( $query ){
					$this->data    = $query;
					$this->message = 'Fetched successfully.';
					if( !$id ){
						$this->total_rows = $this->menu_m->get_count( $where );
					}
				}else{
					$this->set_db_error( 'No item found.' );
				}
			}else{
				$this->invalid();
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
					'item_id', 
					'category_id', 
					'price' 
				), 
				'post' 
			);
			if( $data[ 'error' ] === false ){
	 			
				$data = $data[ 'data' ];
				$data[ 'restaurant_id' ] = $this->get_user_id();

				$insert_id = $this->menu_m->save( $data );
				//print_r($insert_id);
				if( $insert_id ){
					$this->data = $this->menu_m->get( '*', array( $this->menu_m->table. '.id' => $insert_id ), 1 );
					//print_r($data);
					$this->message = 'Menu created successfully.';
				}else{
					$this->set_db_error( 'Failed to create menu.' );
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
					'item_id', 
					'category_id', 
					'price' 
				), 
				'input_stream' 
			);

			if( $data[ 'error' ] === false ){
				$data = $data[ 'data' ];
				$where = array(
					$this->menu_m->table . '.id' => $id,
					$this->menu_m->table . '.restaurant_id' => $this->get_user_info( 'id' )
				);

				$affected = $this->menu_m->save( $data, $where );
				// $this->data = $affected;
				// $this->send();

				if( $affected ){
					$this->data    = $this->menu_m->get( '*', $where, 1 );
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

			$affected = $this->menu_m->delete( array(
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
