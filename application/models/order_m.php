<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class order_m extends MY_Model {

	public $table = 'order';
	public $order = 'DESC';

	public function __construct(){
		$this->load->model( 'user_m' );
	}

	public function get( $column = '*', $where = false, $limit = false, $order = false ){
		
		if( $column == '*' ){
			$column = $this->table . '.id, remark, status, u.username restaurant_name, v.id company_id';
		}

	    $this->db->select( $column, false );

	    if ( $where != false ){
	        $this->db->where( $where );
	    }

	    if( $limit != false ){
	    	if( is_array( $limit ) ){
	        	$this->db->limit( $limit[0], $limit[1] );
	    	}else{
	        	$this->db->limit( $limit );
	    	}
	    }

	    if ($order != false){
	        $this->db->order_by( $order );
	    }else{
	        $this->db->order_by( $this->table . '.id', $this->order );
	    }

	    $this->db->join( $this->user_m->table . ' u', 'u.id = ' . $this->table . '.restaurant_id'  );
	    $this->db->join( $this->user_m->table . ' v', 'v.id = ' . $this->table . '.company_id'  );
	   
	    $this->db->from( $this->table, false );
	    $query = $this->db->get();

	    return $query ? $query->result() : false;
	   
	}
	
}