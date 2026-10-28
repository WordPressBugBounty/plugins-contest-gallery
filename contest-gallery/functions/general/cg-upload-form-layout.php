<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_upload_form_row_layout')){
	function cg_upload_form_row_layout($layout, $columns){
		$columns = max(1, min(3, (int)$columns));
		$layouts = array('1' => 1, '1-1' => 2, '2-1' => 2, '1-2' => 2, '1-1-1' => 3);
		if(is_string($layout) && isset($layouts[$layout]) && $layouts[$layout] === $columns){
			return $layout;
		}
		return implode('-', array_fill(0, $columns, '1'));
	}
}

if(!function_exists('cg_upload_form_row_layouts')){
	// Read the real fields before rendering rows, including rows starting with an empty slot.
	function cg_upload_form_row_layouts($fields, $isRegistry = false){
		$rows = array();
		foreach($fields as $field){
			$field = (array)$field;
			if(empty($field['RowNumber']) || (!$isRegistry && empty($field['Field_Content']))){continue;}
			$content = $isRegistry ? array('row-layout' => isset($field['RowLayout']) ? $field['RowLayout'] : '') : (is_array($field['Field_Content']) ? $field['Field_Content'] : maybe_unserialize($field['Field_Content']));
			$columns = isset($field['RowCols']) ? $field['RowCols'] : 1;
			$layout = cg_upload_form_row_layout(isset($content['row-layout']) ? $content['row-layout'] : '', $columns);
			$row = (int)$field['RowNumber'];
			if(isset($rows[$row]) && $rows[$row] !== $layout){
				$layout = cg_upload_form_row_layout('', $columns);
			}
			$rows[$row] = $layout;
		}
		return $rows;
	}
}

if(!function_exists('cg_upload_form_prepare_layouts')){
	function cg_upload_form_prepare_layouts($fields){
		$rows = array();
		foreach($fields as $key => $field){
			if($key === 'new-0' || !is_array($field)){continue;}
			$row = !empty($field['RowNumber']) ? absint($field['RowNumber']) : 1;
			$columns = max(1, min(3, isset($field['RowCols']) ? (int)$field['RowCols'] : 1));
			$layout = cg_upload_form_row_layout(isset($field['RowLayout']) ? $field['RowLayout'] : '', $columns);
			if(!isset($rows[$row])){$rows[$row] = array('columns' => $columns, 'layout' => $layout);}
			elseif($rows[$row]['columns'] !== $columns || $rows[$row]['layout'] !== $layout){
				$rows[$row]['columns'] = max($rows[$row]['columns'], $columns);
				$rows[$row]['layout'] = cg_upload_form_row_layout('', $rows[$row]['columns']);
			}
		}
		foreach($fields as $key => $field){
			if($key === 'new-0' || !is_array($field)){continue;}
			$row = !empty($field['RowNumber']) ? absint($field['RowNumber']) : 1;
			$fields[$key]['RowCols'] = $rows[$row]['columns'];
			$fields[$key]['RowLayout'] = $rows[$row]['layout'];
		}
		return $fields;
	}
}

if(!function_exists('cg_upload_form_serialize_content')){
	function cg_upload_form_serialize_content($content, $field){
		$content['row-layout'] = cg_upload_form_row_layout(isset($field['RowLayout']) ? $field['RowLayout'] : '', isset($field['RowCols']) ? $field['RowCols'] : 1);
		return serialize($content);
	}
}
