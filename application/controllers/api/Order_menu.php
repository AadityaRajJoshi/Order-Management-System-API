<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_menu extends MY_Controller{ 

	public function __construct(){
		parent::__construct();
		$this->load->model( 'order_menu_m' );
	}

	public function index_get($id = false){
		if ( $this->is_customer() || $this->is_company()) {
			// $page = $this->input->get( 'page' );
			// if( !$page ){
			// 	$page = 1;
			// }

			$id = $this->get_user_id();
			// $limit      = $this->get_limit( $page );

			// $where = array(
			// 	'id' => $id
			// );

			// if( $id > 0 ){
			// 	$where[ 'id' ] = $id;
			// 	$limit = 1;
			// }

			$query = $this->order_menu_m->get( '*');
			

			if( $query ){
				$this->data = $query;

				if( !$id ){
					$this->total_rows = $this->order_menu_m->get_count( $where );
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

	public function index_post($id = false){
		if ($id > 0 || $this->is_customer() ) {
			$data = $this->validate( 
				array( 
					'order_id', 
					'menu_id',  
					'qty',
					'price',
					'tax',
					'total_price'
				), 
				'post' 
			);
			if( $data[ 'error' ] === false ){
	 			
				$data = $data[ 'data' ];

				$this->load->model( 'menu_m' );

				$result = $this->menu_m->get( '*', array(
					 $this->menu_m->table. '.id' => $data[ 'menu_id' ],
				), 1 );

			    $data['customer_id'] = $this->get_user_id();

			    // $menu = $this->order_menu_m->get('*', array('menu_id' => $data[ 'menu_id' ]));

					$insert_id = $this->order_menu_m->save( $data );

					if( $insert_id ){
						$this->data= $this->order_menu_m->get( '*', array(  $this->order_menu_m->table. '.id' => $insert_id ), 1 );
						$this->message = 'Order Menu created successfully.';
					}else{
						$this->set_db_error( 'Failed to create Order menu.' );
					}	
		
		}else{
			$this->invalid();
		}
		$this->send();
		return;
		}
	}

		public function index_put($id = false){
			if ( $id > 0 && $this->is_company() ) {
	
				$qty         = $this->input->input_stream( 'qty' );
				$price       = $this->input->input_stream( 'price' );
				$total_price = $this->input->input_stream( 'total_price' );

				$where = array(
				'id' => $id
				);

				$data = array( 
					'qty'         => $qty,
					'price'       => $price,
					'total_price' => $total_price 
				);

				$affected = $this->order_menu_m->save( $data, $where );
				if( $affected ){
					$this->data = $this->order_menu_m->get( '*', $where, 1 );
					$this->message = 'Updated successfully.';
				}else{
					$this->set_db_error();
				}
			}else{
				$this->invalid();
			}
			$this->send();
			return;
		}

		public function index_delete($id = false){
			if ($id > 0 && $this->is_company()) {
				
				$affected = $this->order_menu_m->delete( array(
					'id' => $id,
					
				));

				if ($affected) {
					$this->message = "Deleted successfully.";
				}else{
					$this->set_db_error( 'Order menu not found' );
				}
			}else{
				$this->invalid();
			}

			$this->send();
			return;
		}
	}


