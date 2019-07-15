<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class order_menu_m extends MY_Model {

	public $table = 'order_menu';
	public $order = 'DESC';

	public function __construct(){
		$this->load->model( 'menu_m' );
		$this->load->model( 'item_m' );
		$this->load->model( 'category_m' );
		$this->load->model( 'user_m' );
	}

	public function get($column = '*', $where = false, $limit = false, $order = false){

		if ( $column == '*' ){ 
			$t = $this->table;
			$column = $t . '.id, '.$t.'.total_price, '.$t.'.qty, i.name item, c.name category, u.username ordered_by ';
		}

		$this->db->select( $column, false );

	    if ( $where != false ){
	        $this->db->where(  $where );
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
	    $this->db->join( $this->menu_m->table . ' m', 'm.id = ' . $this->table . '.menu_id' );
	    $this->db->join( $this->item_m->table . ' i', 'i.id = m.item_id');
	    $this->db->join( $this->category_m->table . ' c', 'c.id = m.category_id');
	    $this->db->join( $this->user_m->table . ' u', 'u.id = ' . $this->table . '.customer_id');

	    $this->db->from( $this->table, false );

	    $query = $this->db->get();

	    return $query ? $query->result() : false;

	}
	
}