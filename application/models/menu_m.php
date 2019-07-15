<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class menu_m extends MY_Model {

	public $table = 'menu';
	public $order = 'DESC';

	public function __construct(){
		$this->load->model( 'category_m' );
		$this->load->model( 'item_m' );
	}

	public function get( $column = '*', $where = false, $limit = false, $order = false ){
		
		if( $column == '*' ){
			$column = $this->table . '.id, i.name as item, c.name as category, ' . $this->table . '.price, ' . $this->table . '.restaurant_id';
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
	    $this->db->join( $this->item_m->table . ' i', 'i.id = ' . $this->table . '.item_id' );
	    $this->db->join( $this->category_m->table . ' c', 'c.id = ' . $this->table . '.category_id' );

	    $this->db->from( $this->table, false );
	    $query = $this->db->get();


	    return $query ? $query->result() : false;
	   
	}
	
}
