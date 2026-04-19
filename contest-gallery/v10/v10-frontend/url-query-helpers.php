<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg1l_get_frontend_passthrough_query_args')){
	function cg1l_get_frontend_passthrough_query_args($args = []){
		$args = (is_array($args)) ? $args : [];

		if(!empty($args['query_args']) && is_array($args['query_args'])){
			return $args['query_args'];
		}

		$queryArgs = [];
		$allowedKeys = ['test','trest'];

		foreach($allowedKeys as $allowedKey){
			if(isset($_GET[$allowedKey]) && $_GET[$allowedKey] !== ''){
				$queryArgs[$allowedKey] = sanitize_text_field(wp_unslash($_GET[$allowedKey]));
			}
		}

		if(!empty($args['is_ecommerce_test']) && empty($queryArgs['test'])){
			$queryArgs['test'] = 'true';
		}

		return $queryArgs;
	}
}

if(!function_exists('cg1l_append_frontend_passthrough_query_args')){
	function cg1l_append_frontend_passthrough_query_args($url, $args = []){
		$url = trim((string)$url);

		if($url === ''){
			return '';
		}

		$queryArgs = cg1l_get_frontend_passthrough_query_args($args);

		if(empty($queryArgs)){
			return $url;
		}

		return add_query_arg($queryArgs, $url);
	}
}

if(!function_exists('cg1l_append_frontend_passthrough_query_args_to_entry_guid_data')){
	function cg1l_append_frontend_passthrough_query_args_to_entry_guid_data($data, $args = []){
		if(empty($data) || !is_array($data)){
			return is_array($data) ? $data : [];
		}

		$entryGuidKeys = [
			'entryGuid',
			'entryGuidUser',
			'entryGuidNoVoting',
			'entryGuidWinner',
			'entryGuidEcommerce'
		];

		foreach($data as $id => $row){
			if(empty($row) || !is_array($row)){
				continue;
			}

			foreach($entryGuidKeys as $entryGuidKey){
				if(!empty($row[$entryGuidKey])){
					$data[$id][$entryGuidKey] = cg1l_append_frontend_passthrough_query_args($row[$entryGuidKey], $args);
				}
			}
		}

		return $data;
	}
}
