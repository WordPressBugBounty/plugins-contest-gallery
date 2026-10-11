<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_voting_cookie_name')){
	function cg_voting_cookie_name($galleryId){
		$name = 'contest-gal1ery-'.absint($galleryId).'-voting';
		$blogId = get_current_blog_id();
		return $blogId>1 ? $name.'-blog-'.$blogId : $name;
	}
}

if(!function_exists('cg_sign_voting_cookie')){
	function cg_sign_voting_cookie($galleryId,$id,$legacyId){
		$payload = 'cg-frontend-voter|'.get_current_blog_id().'|'.absint($galleryId).'|voting|v1|'.$id.'|'.$legacyId;
		return hash_hmac('sha256',$payload,wp_salt('auth'));
	}
}

if(!function_exists('cg_get_voting_cookie_identity')){
	function cg_get_voting_cookie_identity($galleryId){
		$galleryId = absint($galleryId);
		$cookieName = cg_voting_cookie_name($galleryId);
		if(!$galleryId || empty($_COOKIE[$cookieName]) || !is_string($_COOKIE[$cookieName])){return array();}
		$cookie = wp_unslash($_COOKIE[$cookieName]);
		if(strlen($cookie)>220){return array();}
		$parts = explode('|',$cookie);
		if(count($parts)!==4 || $parts[0]!=='v1' || !preg_match('/^[A-Za-z0-9]{48}$/D',$parts[1]) || !preg_match('/^[a-f0-9]{64}$/D',$parts[3])){return array();}
		if($parts[2]!=='' && !cg_is_valid_frontend_cookie_value($parts[2])){return array();}
		if(!hash_equals(cg_sign_voting_cookie($galleryId,$parts[1],$parts[2]),$parts[3])){return array();}
		return array('id'=>'v1:'.$parts[1],'legacyId'=>$parts[2],'cookie'=>$cookie);
	}
}

if(!function_exists('cg_write_voting_cookie')){
	function cg_write_voting_cookie($galleryId,$cookie){
		if(headers_sent()){return false;}
		$expires = time()+YEAR_IN_SECONDS;
		$header = 'Set-Cookie: '.cg_voting_cookie_name($galleryId).'='.rawurlencode($cookie);
		$header .= '; expires='.gmdate('D, d-M-Y H:i:s T',$expires).'; Max-Age='.YEAR_IN_SECONDS.'; path=/; SameSite=Lax';
		if(is_ssl()){$header .= '; Secure';}
		header($header,false);
		return true;
	}
}

if(!function_exists('cg_bootstrap_voting_cookie')){
	function cg_bootstrap_voting_cookie($galleryId){
		global $wpdb;
		static $issued = array();
		$galleryId = absint($galleryId);
		if(!$galleryId){return '';}
		$identity = cg_get_voting_cookie_identity($galleryId);
		if(!empty($identity)){
			cg_write_voting_cookie($galleryId,$identity['cookie']);
			return $identity['cookie'];
		}
		$cookieName = cg_voting_cookie_name($galleryId);
		$candidate = isset($_COOKIE[$cookieName]) && is_string($_COOKIE[$cookieName]) ? wp_unslash($_COOKIE[$cookieName]) : '';
		$oldName = 'contest-gal1ery-'.$galleryId.'-voting';
		if(!isset($_COOKIE[$cookieName]) && $oldName!==$cookieName && isset($_COOKIE[$oldName]) && is_string($_COOKIE[$oldName])){
			$candidate = wp_unslash($_COOKIE[$oldName]);
			// Another blog may already have wrapped the old unsigned value. It can only add a restriction.
			if(strlen($candidate)<=220 && strpos($candidate,'v1|')===0){
				$parts = explode('|',$candidate);
				$candidate = count($parts)===4 ? $parts[2] : '';
			}
		}
		$key = get_current_blog_id().'|'.$galleryId.'|'.hash('sha256',$candidate);
		if(isset($issued[$key])){return $issued[$key];}
		if(headers_sent()){return '';}
		$legacyId = '';
		// An unsigned value can add restrictions, but never proves ownership of old votes.
		if(cg_is_valid_frontend_cookie_value($candidate)){
			$table = $wpdb->prefix.'contest_gal1ery_ip';
			$existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE GalleryID = %d AND CookieId = %s LIMIT 1",$galleryId,$candidate));
			if($existing){$legacyId = $candidate;}
		}
		$id = wp_generate_password(48,false,false);
		if(!is_string($id) || !preg_match('/^[A-Za-z0-9]{48}$/D',$id)){return '';}
		$cookie = 'v1|'.$id.'|'.$legacyId.'|'.cg_sign_voting_cookie($galleryId,$id,$legacyId);
		if(!cg_write_voting_cookie($galleryId,$cookie)){return '';}
		$issued[$key] = $cookie;
		// Do not copy the issued value into $_COOKIE: only a browser round trip authorizes voting.
		return $cookie;
	}
}

if(!function_exists('cg_get_voting_identity_where')){
	function cg_get_voting_identity_where($galleryId,$checkLogin,$checkCookie,$checkIp,$wpUserId,$userIp,$cookieId,$includeLegacy = false){
		global $wpdb;
		if($checkLogin){
			if(!is_user_logged_in() || !$wpUserId || absint($wpUserId)!==get_current_user_id()){return '1=0';}
			return $wpdb->prepare('WpUserId = %d',$wpUserId);
		}
		if($checkCookie){
			$identity = cg_get_voting_cookie_identity($galleryId);
			if(empty($identity) || $cookieId!==$identity['id']){return '1=0';}
			$where = $wpdb->prepare('CookieId = %s',$cookieId);
			if($checkIp){
				if(cg_normalize_user_ip($userIp)===''){return '1=0';}
				$where = '('.$where.' AND '.$wpdb->prepare('IP = %s',$userIp).')';
			}
			if($includeLegacy && $identity['legacyId']!==''){
				// Retain old restrictions even when the corrected IP policy changes the effective address.
				$where = '('.$where.' OR '.$wpdb->prepare('CookieId = %s',$identity['legacyId']).')';
			}
			return $where;
		}
		if(cg_normalize_user_ip($userIp)===''){return '1=0';}
		return $wpdb->prepare('IP = %s',$userIp);
	}
}

if(!function_exists('cg_count_voter_votes')){
	function cg_count_voter_votes($galleryId,$isOneStar,$identityWhere,$pictureId = 0,$category = false,$since = 0){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery_ip';
		$where = $wpdb->prepare('GalleryID = %d',absint($galleryId)).' AND ('.$identityWhere.') AND '.($isOneStar ? 'RatingS > 0' : 'Rating > 0');
		if($pictureId){$where .= $wpdb->prepare(' AND pid = %d',$pictureId);}
		if($category!==false){$where .= $wpdb->prepare(' AND Category = %d AND CategoriesOn = 1',$category);}
		if($since){$where .= $wpdb->prepare(' AND Tstamp > %d',$since);}
		return intval($wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where"));
	}
}
