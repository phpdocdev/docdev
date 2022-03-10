<?
class xmlobj{
	
	function xmlobj($verbose=0){
		$this->verbose = $verbose;
	}
	
	function parse($xml){

		$Root = new xml_node('xmlobj');
		$this->stack = array(&$Root);
		
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser, $this);
		xml_set_element_handler($this->xml_parser, "_tag_start", "_tag_end");
		xml_set_character_data_handler($this->xml_parser, "_tag_cdata");
		xml_parse($this->xml_parser, $xml);
		xml_parser_free($this->xml_parser);
		
		return $Root->get_members();
	}
	
	function _tag_start($parser, $name, $attrs){
		$this->_report_stack();
		$obj = new xml_node($name, $attrs);
		
		$this->stack[count($this->stack)-1]->_add_member($obj);
		
		$this->stack[] =& $this->stack[count($this->stack)-1]->get_last_member();
	}
	
	function _tag_end($parser, $name){
		$this->_report_stack();	
		$pop = array_pop($this->stack);
	}
	
	function _tag_cdata($parser, $data){
		$this->stack[count($this->stack)-1]->_add_cdata($data);
	}
	
	function _msg($msg){
		if(!$this->verbose){
			return 0;
		}
		print "$msg\n";
	}
	
	function _report_stack(){
		$names = array();
		foreach($this->stack as $s){
			$names[] = $s->get_name();
		}
		$this->_msg("Stack: " . count($this->stack) . ' ' . join(', ', $names));
	}
	
}

class xml_node{

	function xml_node($name, $attrs=array()){
		$this->name = $name;
		$this->attrs = $attrs;
		$this->data = '';
		$this->members = array();
	}
	
	function get_name(){
		return $this->name;
	}
	
	function get_attrs(){
		return $this->attrs;
	}

	function get_attr($key){
		return $this->attrs[$key];
	}
	
	function _add_cdata($data){
		$this->data .= $data;
	}
	
	function _add_member(&$mem){
		$this->members[] = $mem;
	}
	
	function get_members(){
		return $this->members;
	}

	function get_member($i){
		return $this->members[$i];
	}
	
	function get_last_member(){
		return $this->members[count($this->members)-1];
	}	

	function get_value(){
		return $this->data;
	}
	
	function get_member_byname($name){
		foreach($this->members as $m){
			if( $m->get_name() == $name ){
				return $m;
			}
		}
		return NULL;
	}
	
	function get_member_val_byname($name){
		foreach($this->members as $m){
			if( $m->get_name() == $name ){
				return $m->get_value();
			}
		}
		return NULL;
	}	
	
}

//	<ROOT>
//		<DBERROR>
//			<ERROR>
//				<DESC>COMMAND TEXT WAS NOT SET FOR THE COMMAND OBJECT.</DESC>
//				<ROW>0</ROW>
//				<FIELD/>
//				<APPID>WEBP</APPID>
//				<USERID>SDUWEBUSER</USERID>
//				<SP></SP>
//				<DATETIME>7/22/2003 11:58:44 AM</DATETIME>
//			</ERROR>
//		</DBERROR>
//	</ROOT>
//	
//	$Obj = array(
//		ROOT => array(
//			attrs => array(),
//			val = '',
//			DBERROR => array(
//				ERROR => array(
//					DESC => array(
//						val => 'COMMAND TEXT WAS NOT SET FOR THE COMMAND OBJECT.',
//					),
//					ROW => array(
//						val => 0,
//					),
//					FIELD => array(
//					
//					),
//				)
//			),
//		),
//	):

?>