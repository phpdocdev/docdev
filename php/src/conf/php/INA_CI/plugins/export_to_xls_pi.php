<?php

function export_to_xls($query, $data = array(), $file_name){
	$values = array_values($data);

	$contents = join(array_keys($data), "\t") . "\n";
	
	if ($query->num_rows() > 0)
		foreach($query->result() as $row)
			foreach ($values as $cnt => $val)
				if ($cnt < count($values) - 1)
					$contents .= "{$row->{$val}}\t";
				else
					$contents .= "{$row->{$val}}\n";
	
	$contents = strip_tags($contents); // remove html and php tags etc.

	header("Content-Disposition: attachment; filename={$file_name}.xls");
	print $contents;
}