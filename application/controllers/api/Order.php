<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends MY_Controller{ 

	public function __construct(){
		parent::__construct();
		$this->load->model( 'order_m' );
	}

	public function index_get($id = false){
		if( $this->is_company() || $this->is_customer() ) {

			$page = $this->input->get('page');
			if(!$page) {
				$page = 1;
			}

			$field = $this->is_customer() ? 'company_id' : 'id';
			$company_id = $this->get_user_info( $field );

			// $limit = $this->get_limit( $page );

			$where = array(
				$this->order_m->table . '.company_id' => $company_id
			);

			// if( $id > 0 ){
			// 	$where[ 'id' ] = $id;
			// 	$limit = 1;
			// }

			$query = $this->order_m->get( '*', $where);

			if( $query ){
				$this->data = $query;
				if( !$id ){
					$this->total_rows = $this->order_m->get_count( $where );
				}
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
		if ( $this->is_company() ){
			$data = $this->validate( 
				array(
					'restaurant_id',
					'remark',
					'lock',
					'status'
				), 
				'post' 
			);
			if( $data[ 'error' ] === false ){
	 			
				$data = $data[ 'data' ];
				$data[ 'company_id' ] = $this->get_user_id();

				$insert_id = $this->order_m->save( $data );
				if( $insert_id ){
					$this->data = $this->order_m->get( '*', array( $this->order_m->table . '.id' => $insert_id ), 1 );
					$this->message = 'Order created successfully.';
				}else{
					$this->set_db_error( 'Failed to create Order.' );
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

	public function index_put($id = false) {

		if( $id > 0 && $this->is_company() ){
			
			$where = array(
				'id' => $id,
				'company_id' => $this->get_user_info( 'company_id' )
			);

			# Check whether the order is locked or not
			$result = $this->order_m->get( '*', $where, 1 );
			$lock = false;
			if( $result ){
				$row = $result[ 0 ];
				if( 1 == $row->lock ){
					$lock = true;
				}
			}

			if( !$lock ){
				$data = $this->validate( 
					array(
						'restaurant_id',
						'remark',
					), 
					'input_stream' 
				);
				
				if( $data[ 'error' ] === false ){

					$data = $data[ 'data' ];

					$affected = $this->order_m->save( $data, $where );

					if ($affected) {
						$this->data = $this->order_m->get( '*', $where, 1 );
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
				$this->status = 404;
				$this->message = 'Cannot update locked order';
			}

		}else{
			$this->invalid();
		}

		$this->send();
		return;
	}

	public function index_delete($id = false){
		if ( $id > 0 && $this->is_company() ){
			
			$affected = $this->order_m->delete(array(
				'id' => $id,
				
			));

			if ($affected) {
				$this->message = 'Deleted successfully';
			}else{
				$this->set_db_error( 'Order not found' );
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