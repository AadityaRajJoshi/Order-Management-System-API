<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->model( 'user_m' );
		$this->load->model( 'role_m' );
	}

	public function index_get( $id = false ){
		
		if( !$this->is_restaurant() ){
			# Restaurants usually don't have users

			$page = $this->input->get( 'page' );
			if( !$page ){
				$page = 1;
			}

			$limit = $this->get_limit( $page );
			$where = false;

			if( $id > 0 ){
				$where[ 'id' ] = $id;
				$limit = 1;
			}

			if( $this->is_sa() ){
				$where[ 'role_id' ] = $this->get_role_id( 'company' );
			}elseif( $this->is_company() ){
				$where[ 'company_id' ] = $this->get_user_id();
			}elseif( $this->is_customer() ){
				$where[ 'role_id' ]    = $this->get_rold_id( 'customer' );
				$where[ 'company_id' ] = $this->get_user_info( 'company_id' );
			}

			$result = $this->user_m->get( '*', $where, $limit );
			if( $result ){
				$this->data = $result;
				if( !$id ){
					$this->total_rows = $this->user_m->get_count( $where );
				}
				$this->message = 'Fetched Successfully.';
			}else{
				$this->set_db_error( 'No item found.' );
			}
		}else{
			$this->status = 400;
			$this->message = 'Invalid Request';
		}

		$this->send();
		return;
	}

	
	public function index_post(){
	
		$data = $this->validate( 
			array(
				'username', 
				'email', 
				'password' 
			), 
			'post' 
		);

		if ( $data[ 'error' ] === false ){
			$data = $data[ 'data' ];
			$data[ 'nicename' ] = $this->input->post( 'nicename' );
			$should_insert = false;
			$err_msg = 'Failed to create user.';

			if( $this->is_sa() ){
				# Super Administrator will only create company
				$data[ 'role_id' ] = $this->get_role_id( 'company' );
				$should_insert = true;
			}elseif( $this->is_company() ){
				# Company will create either Resturant or customer
				$role_id = $this->input->post( 'role_id' );
				if ( $this->is_customer( $role_id ) || $this->is_restaurant( $role_id ) ){
					$data[ 'role_id' ]    = $this->input->post( 'role_id' );
					$data[ 'company_id' ] = $this->get_user_id();
					$should_insert = true;
				}
			}else{
				$err_msg = 'You don\'t have permission to create user.';
			}

			if( $should_insert ){
				$insert_id = $this->user_m->save( $data );
				if( $insert_id ){
					$this->data    = $this->user_m->get( '*', array( 'id' => $insert_id ), 1 );
					$this->message = 'User Created Successfully.';
					$this->status  = 201;
				}else{
					$this->set_db_error( $err_msg );
				}
			}else{
				$this->message = $err_msg;
				$this->status  = 400;
			}
		}else{
			$this->status  = 422;
			$this->data    = $data[ 'error' ];
			$this->message = 'Validation Error';
		}
		$this->send();
		return;	
	}

	public function index_put($id){

		if ( $id > 0 && $this->is_Sa()  ) {
			$nicename = $this->input->input_stream( 'nicename' );

			$where = array(
				'id' => $id
			);

			$data = array( 
				'nicename' => $nicename 
			);

			$affected = $this->user_m->save( $data, $where );

			if ($affected) {
				$this->data = $this->user_m->get( '*', $where, 1 );
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

	public function index_delete( $id ){
		
		$current_user_id = $this->get_user_id();
		if( $id > 0 && $id != $current_user_id ){
			$is_deletable = false;

			if( $this->is_company() ){
				$result = $this->user_m->get( '*', array( 'id' => $id ), 1 );
					
				if( count( $result ) > 0 ){
					$row = $result[0];
					if( $this->is_customer($row['role_id'] ) && $row[ 'company_id' ] == $current_user_id ){
						$is_deletable = true;
					}
				}
			}elseif( $this->is_sa() ){
				$is_deletable = true;
			}

			$affected = $this->user_m->delete( array(
				'id' => $id
			));

			if( $affected ){
				$this->message = 'Deleted successfully.';
			}else{
				$this->set_db_error( 'User not found' );
				$this->status = 400;
			}
		}else{
			$this->message = 'Invalid request.';
		}

		$this->send();
		return;
	}

	public function login_post(){
		
		$where = array(
			'username' => $this->input->post( 'username' ),
			'password' => $this->input->post( 'password' )
		);

		$result = $this->user_m->get( '*', $where, 1 );

		if( $result ){
			$row = $result[ 0 ];
			$role = $this->role_m->get( '*', array( 'id' => $row->role_id ), 1 )[0];
			$data = array(
				'id'         => $row->id,
				'role'       => $role->name,
				'username'   => $row->username,
				'email'      => $row->email,
				'role_id'    => $row->role_id,
				'company_id' => $row->company_id
			);
                                                      
			if( $this->is_customer( $row->role_id ) ){
				# Get Restaurants for this user
				$result = $this->user_m->get( '*', array(
					'company_id' => $row->company_id,
					'role_id'    => $this->get_role_id( 'restaurant' )
				), 1 );

				$arr = array();
				if( $result ){
					foreach( $result as $restaurant ){
						$arr[] = $restaurant->id;
					}
				}
				$data[ 'restaurant' ] = $arr;
			}

			$this->session->set_userdata( $data );
			$this->data = $data;
			$this->message = 'Logged in Successfully';

		}else{
			$this->set_db_error( 'Invalid username or password.' );
			
		}

		$this->send();
		return;
	}

	public function logout_post(){
		$this->session->sess_destroy();
		$this->message = 'Logged out Successfully';

		$this->send();
		return;
	}

	public function order_post(){
		if ( $this->is_customer() ) {
			$data = $this->validate( 
				array(
					'order_id', 
					'menu_id',  
				), 
				'post' 
			);
			if( $data[ 'error' ] === false ){
					
				$data = $data[ 'data' ];

				$this->load->model( 'menu_m' );
				$result = $this->menu_m->get( '*', array(
					$this->menu_m->table . '.id' => $data[ 'menu_id' ],
				), 1 );

				if( $result ){
					$menu = $result[0];
					$restaurant = $this->get_user_info( 'restaurant' );
					if( in_array( $menu->restaurant_id, $restaurant ) ){
						
						$this->load->model( 'order_menu_m' );
					    $data['customer_id'] = $this->get_user_id();

					    $data[ 'tax' ] = '';
					    $data[ 'price' ] = $menu->price;

					    $qty = $this->input->post( 'qty' );
					    $qty = ( empty( $qty ) || $qty  == 0 ) ? 1 : $qty;
					    $data[ 'qty' ] = $qty;
					    $data[ 'total_price' ] = $qty * $data[ 'price' ];
					    
						$insert_id = $this->order_menu_m->save( $data );
						if( $insert_id ){
							$where = array( $this->order_menu_m->table .'.id' => $insert_id );
							$this->data    = $this->order_menu_m->get( '*', $where, 1 );
							$this->message = 'Order Menu created successfully.';
						}else{
							$this->set_db_error( 'Failed to create Order menu.' );
						}
					}else{
						$this->invalid();
					}
				}else{
					$this->invalid();
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

	public function invalid(){
		$this->message = 'Invalid request.';
		$this->status  = 404;
	}
}
