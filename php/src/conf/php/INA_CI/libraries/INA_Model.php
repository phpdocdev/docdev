<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

#require parent class
require_once(BASEPATH.'libraries/Model'.EXT);

class INA_Model extends Model {
	protected $name;
	protected $fields;
	protected $rules;
	protected $cols;
	protected $id = 'id';# defaults to id
	protected $CI;

    function __construct($db='') {#new change, use default db instead of literally 'default'
        parent::__construct();
	
		$this->CI =& get_instance();
		
		if (is_a($db, 'CI_DB_mysql_driver'))
			$this->CI->db = $db;#pass in db connection
		else
			$this->CI->db = $this->CI->load->database($db, TRUE);#deprecating old way of connecting to db so that multiple connections can occur in the same app
		
		#$this->CI->load->database($db);
    }
	
	#where, select (or numeric id)
	function find($p){
		if (is_array($p))
			return $this->findAll($p)->row();//return first row
		
		elseif ($p) {
			$query = $this->findAll(
				array(
					'where' => array(sprintf("%s.%s", $this->name, $this->id), $p)#append the table name so that it isn't ambiguous
				)
			);
			
			return $query->num_rows() ? $query->row() : NULL;//return first row
		}
		
		return NULL;
	}
	
	function find_where($p1, $p2 = NULL){
		if ($p1 && !$p2)
			$query = $this->findAll(array('where' => $p1));
		else
			$query = $this->findAll(array('where' => array($p1, $p2)));
		
		return $query->num_rows() ? $query->row() : NULL;//return first row
	}
	
	#start, limit, where, select, orderby
	function findAll($p=NULL){
		if ($p['select'])
			$this->CI->db->select($p['select']);

		if ($p['where']) {
			if (is_array($p['where'])) {

				if ($p['where'][0] AND $p['where'][1]) //one where array
					$this->CI->db->where($p['where'][0], $p['where'][1]);
				else
					foreach ($p['where'] as $where => $val)
						if (ctype_digit($where) OR $where === 0)
							$this->CI->db->where($val);#is a string
						else
							$this->CI->db->where($where, $val);
			}
			else//where string
				$this->CI->db->where($p['where']);
		}
		
		if ($p['like']) {
			if (is_array($p['like'])) {

				if ($p['like'][0]) //one like array
					$this->CI->db->like($p['like'][0], $p['like'][1]);
				else
					foreach ($p['like'] as $like => $val)
						$this->CI->db->like($like, $val);
			}
			else//like string
				$this->CI->db->like($p['like']);
		}
		
		if ($p['orderby'])
			if (is_array($p['orderby']))
				$this->CI->db->orderby($p['orderby'][0], $p['orderby'][1]);//passed 2 params
			else
				$this->CI->db->orderby($p['orderby']);//passed a string
		
		if ($p['start'] && ctype_digit(trim($p['limit'])))
			$this->CI->db->limit($p['limit'], ctype_digit(trim($p['start'])) ? $p['start'] : 0);#make sure start is a number
		elseif (ctype_digit(trim($p['limit'])))
			$this->CI->db->limit($p['limit']);#make sure limit is a number
			
		if ($p['id'])
			$this->db->where($this->id, $p['id']);

		$name = is_array($p) && $p['from'] ? $p['from'] : $this->name;#optionally get table from param

		return $this->CI->db->get($name);//get from default table name
	}
	
	function delete($where){
		if (is_array($where))//if where array
			$result = $this->CI->db->delete($this->name, $where);
		elseif (is_numeric($where))//passed numeric row id
			$result = $this->CI->db->delete(
				$this->name, 
				array($this->id => $where)
			);
		else
			return NULL;
		
		if ($result > 0) return TRUE;
	}
	
	#params: data, fields
	function insert($p=NULL){
		$fields = $p['fields'] ? $p['fields'] : $this->cols;//assume object cols if none specified
		$name   = $p['name'] ? $p['name'] : $this->name;
		
		if (is_array($p) && !$p['data'])//assume $p is data if it is an array and there's no 'data' hash defined
			$data = $p;
		else
			$data = $p['data'] ? $p['data'] : $_POST;//assume POST has been submitted by default if no params specified
			
		if (!$data) return NULL;
		
		foreach ($fields as $field)
			#if ($data[$field])
			if (isset($data[$field]))#added dec 10, 2007
				$this->CI->db->set($field, $data[$field]);
		
		$result = $this->CI->db->insert($name);
		
		return $this->CI->db->insert_id();//return ID
	}
	
	#params: id, data, fields
	function update($p=NULL){

		if (is_numeric($p)) {##Patched Oct 15, 2007, again on Oct 19
			$id = $p;
			$p = array('id' => $id);
		}
		
		if (!$p['data']) $p['data'] = $_POST;
		
		if ($p['id'] && is_numeric($p['id']))
			$id = $p['id'];
		elseif ($p['data'][$this->id])
			$id = $p['data'][$this->id];
		else
			return NULL;##Aug 10, 07 moved this up because of the id issue
	
		if (!is_array($p['data'])) return NULL;
		
		if (!$p['fields'])
			$p['fields'] = $this->cols;

		if (!is_array($p['fields'])) return NULL;
		
		foreach ($p['fields'] as $field_name => $field)
			if (!is_numeric($field_name))
				$this->CI->db->set($field, $p['data'][$field_name]);
			#elseif ($p['data'][$field])
			else #jay patch feb, 07 - forces update including blanks!
				$this->CI->db->set($field, $p['data'][$field]);
		
		$this->CI->db->where($this->id, $id);
		$result = $this->CI->db->update($this->name);
		
		if ($result > 0) return TRUE;
	}
	
	function rules($edit_mode = NULL){
		if ($this->rules) return $this->rules;
	}
	
	function fields(){
		if ($this->fields) return $this->fields;
	}
	
	function size($p=NULL){
		$query = $this->findAll($p);
		
		return $query->num_rows();
	}
	
	/**
	 * This is an alternative function to size() which will run the full query, this function returns the count
	 * and is more efficient. It's only requirement is that the query before have SQL_CALC_FOUND_ROWS after each select
	 * and that this function be run immeditiately after that query. 
	 */
	function found_rows(){
		return $this->db->query('select FOUND_ROWS( ) as count')->row()->count;#faster count calculator
	}
	
	function drop_down_list($key, $value, $p = array()){	
		$name = $p['name'] ? $p['name'] : $this->name;#will default to model name if none specified
		
		if (!$p['no_table_prefix'])
			$table_prefix = $name.'.';		
		
		$this->CI->db->select("{$table_prefix}$key, {$table_prefix}$value");
		
		if ($p['orderby'])
			$this->CI->db->orderby($p['orderby']);
		
		$query = $this->CI->db->get($name); 
		
		if (!$p['key_field']) $p['key_field'] = $key;
		
		if ($query->num_rows() > 0) {
		
			if (isset($p['blank']))
				$rows [''] = $p['blank'];
			
			foreach($query->result() as $row)
				$rows[$row->{$p['key_field']}] = $row->$value;
		}

		return $rows;
	}
	
	function deactivate($id, $p=NULL){
		$active_field = $p['active_field'] ? $p['active_field'] : 'active';
		$active_value = $p['value'] ? $p['value'] : '0';
		 
		$this->CI->db->set($active_field, $active_value);#set active to zero
		$this->CI->db->where($this->id, $id);
		
		$result = $this->CI->db->update($this->name);
		
		if ($result > 0) return TRUE;
	}
}