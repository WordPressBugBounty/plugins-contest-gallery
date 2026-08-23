<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_paid_actions_admin_refund_label')){
	function cg_paid_actions_admin_refund_label($paymentStatus){
		$status = strtolower(str_replace(array('_','-'),' ',trim((string)$paymentStatus)));
		if(strpos($status,'partial')!==false && strpos($status,'refund')!==false){return 'Partially refunded';}
		if(strpos($status,'refund')!==false){return 'Refunded';}
		return '';
	}
}

if(!function_exists('cg_paid_actions_admin_payment_badge')){
	function cg_paid_actions_admin_payment_badge($detail,$fallbackGalleryId = 0){
		if(empty($detail) || empty($detail['source'])){return '';}

		$source = sanitize_key($detail['source']);
		if($source==='preview'){
			return "<span class='cg_paid_action_payment cg_paid_action_payment_preview'><span class='dashicons dashicons-visibility' aria-hidden='true'></span><span><strong>Test action</strong><small>No payment</small></span></span>";
		}
		if($source==='admin'){
			return "<span class='cg_paid_action_payment cg_paid_action_payment_admin'><span class='dashicons dashicons-admin-users' aria-hidden='true'></span><span><strong>Admin credit</strong><small>No payment</small></span></span>";
		}
		$isSandboxPurchase = $source==='sandbox_purchase';

		$price = cg_paid_actions_format_payment_price($detail);
		$titlePrefix = $isSandboxPurchase ? 'Paid via sandbox order' : 'Paid';
		$title = $price!=='' ? $titlePrefix.' · '.$price : $titlePrefix;
		$orderId = !empty($detail['parentOrder']) ? absint($detail['parentOrder']) : 0;
		$orderNumber = !empty($detail['orderNumber']) ? $detail['orderNumber'] : ($orderId ? '#'.$orderId : 'Order unavailable');
		$refundLabel = cg_paid_actions_admin_refund_label(isset($detail['paymentStatus']) ? $detail['paymentStatus'] : '');
		$meta = 'Order '.ltrim((string)$orderNumber,'#');
		if($refundLabel!==''){$meta .= ' · '.$refundLabel;}
		$classes = 'cg_paid_action_payment cg_paid_action_payment_purchase'.($isSandboxPurchase ? ' cg_paid_action_payment_sandbox' : '').($refundLabel!=='' ? ' cg_paid_action_payment_refunded' : '');
		$content = "<span class='".esc_attr($classes)."'><span class='dashicons dashicons-money-alt' aria-hidden='true'></span><span><strong>".esc_html($title)."</strong><small>".esc_html($meta)."</small></span></span>";
		if(!$orderId){return $content;}

		$galleryId = !empty($detail['orderGalleryId']) ? absint($detail['orderGalleryId']) : absint($fallbackGalleryId);
		$url = '?page='.cg_get_version().'/index.php&option_id='.$galleryId.'&cg_show_order=true&cg_order_id='.$orderId;
		return "<a class='cg_paid_action_payment_link' target='_blank' href='".esc_attr($url)."' title='Show order'>".$content."</a>";
	}
}

if(!function_exists('cg_paid_actions_order_card')){
	function cg_paid_actions_order_card($orderId,$order){
		$orderId = absint($orderId);
		if(!$orderId || empty($order) || !current_user_can('manage_options')){return;}
		if(!empty($order->IsFullPaid)){
			cg_paid_actions_grant_order($orderId);
		}

		$rows = cg_paid_actions_get_order_credit_rows($orderId);
		if(empty($rows)){return;}
		$userId = !empty($order->WpUserId) ? absint($order->WpUserId) : 0;
		$user = $userId ? get_userdata($userId) : false;
		$userLogin = $user && !empty($user->user_login) ? $user->user_login : 'WordPress user';
		$userEmail = $user && !empty($user->user_email) ? $user->user_email : (!empty($order->PayerEmail) ? $order->PayerEmail : 'Email unavailable');
		$environment = !empty($order->IsTest) ? 'Sandbox' : 'Live';
		$environmentClass = !empty($order->IsTest) ? 'sandbox' : 'live';

		echo "<section id='cgPaidActionsOrderCard' class='cg_paid_actions_order_card' data-order-id='".esc_attr($orderId)."'>";
		echo "<div class='cg_paid_actions_order_header'><div><div class='cg_paid_actions_eyebrow'>CUSTOMER CREDITS · ".esc_html(strtoupper($environment))." ENVIRONMENT</div><h2>Available gallery credits</h2><p>Set the customer's absolute available balance for the relevant credit pool. Purchased quantity, price, payment data and invoice stay unchanged.</p></div><div class='cg_paid_actions_order_context'><span class='cg_paid_actions_order_environment cg_paid_actions_order_environment_".esc_attr($environmentClass)."'><span class='dashicons ".(!empty($order->IsTest) ? 'dashicons-hammer' : 'dashicons-money-alt')."' aria-hidden='true'></span>".esc_html($environment)." balance</span><span class='cg_paid_actions_order_user'><strong>".esc_html($userLogin)."</strong><small>".esc_html($userEmail)." · WordPress user #".esc_html($userId)."</small></span></div></div>";
		echo "<div class='cg_paid_actions_order_rows'>";
		foreach($rows as $row){
			$type = cg_paid_actions_normalize_type($row->PaidActionType);
			if($type===''){continue;}
			$scope = $row->PaidActionScope==='shared' ? 'shared' : 'local';
			$granted = isset($row->Granted) ? absint($row->Granted) : 0;
			$consumed = isset($row->Consumed) ? absint($row->Consumed) : 0;
			$revoked = isset($row->Revoked) ? absint($row->Revoked) : 0;
			$typeLabel = cg_paid_actions_type_label($type,true);
			$poolLabel = $scope==='shared' ? 'Shared '.strtolower($typeLabel).' pool' : 'Local '.strtolower($typeLabel).' pool · Gallery '.absint($row->GalleryID);
			$balanceInputId = 'cgPaidActionsOrderBalance'.$orderId.'-'.absint($row->id);
			echo "<article class='cg_paid_actions_order_row' data-item-id='".esc_attr(absint($row->id))."'>";
			$creditSource = !empty($row->GrantType) && $row->GrantType==='sandbox_purchase' ? 'Paid via sandbox order' : 'Paid via live order';
			echo "<div class='cg_paid_actions_order_identity'><strong>".esc_html($typeLabel)."</strong><span>".esc_html($creditSource)." · Gallery ".esc_html(absint($row->GalleryID))." · ".esc_html(ucfirst($scope))." pool</span></div>";
			echo "<div class='cg_paid_actions_order_metrics'><span>Purchased <b>".esc_html(absint($row->Units))."</b></span><span>Consumed <b>".esc_html($consumed)."</b></span><span class='cg_paid_actions_order_metric_revoked' title='Credits from this order withdrawn through a refund or an administrative balance adjustment.'>Revoked <b>".esc_html($revoked)."</b><span class='dashicons dashicons-editor-help' aria-hidden='true'></span></span><span>Granted by this order <b>".esc_html($granted)."</b></span></div>";
			echo "<div class='cg_paid_actions_order_balance'><div class='cg_paid_actions_order_balance_copy'><label for='".esc_attr($balanceInputId)."'>Available ".esc_html(strtolower($environment))." balance</label><small>".esc_html($userLogin)." · User #".esc_html($userId)." · ".esc_html($poolLabel)."</small></div><div class='cg_paid_actions_order_balance_controls'><input id='".esc_attr($balanceInputId)."' type='number' min='0' step='1' value='".esc_attr(absint($row->CurrentScopeBalance))."'><button type='button' class='button button-primary cg_paid_actions_order_save'>Update balance</button></div><small class='cg_paid_actions_order_balance_help'>This is the total available ".esc_html(strtolower($environment))." balance in this pool. It can include credits from other orders and administrator adjustments.</small></div>";
			echo "</article>";
		}
		echo "</div><div class='cg_paid_actions_order_note'><strong>Revoked</strong> means credits from this order were withdrawn through a refund or an administrative balance adjustment. They were not consumed. Full refunds automatically revoke unused credits from this order. Consumed credits are never restored. Partial refunds require a manual balance adjustment here.</div><div class='cg_paid_actions_order_message' aria-live='polite'></div></section>";
		?>
		<script data-cg-processing="true">
			jQuery(function($){
				$('#cgPaidActionsOrderCard').on('click','.cg_paid_actions_order_save',function(){
					var $button = $(this), $row = $button.closest('.cg_paid_actions_order_row'), $card = $button.closest('#cgPaidActionsOrderCard');
					var balance = Math.max(0,parseInt($row.find('input').val(),10) || 0);
					$button.prop('disabled',true).text('Saving…');
					$card.find('.cg_paid_actions_order_message').text('');
					$.post('admin-ajax.php',{
						action:'post_cg_paid_actions_set_order_balance',
						orderId:$card.attr('data-order-id'),
						itemId:$row.attr('data-item-id'),
						balance:balance,
						cg_nonce:CG1LBackendNonce.nonce
					}).done(function(response){
						if(response && response.success){
							$row.find('input').val(response.data.balance);
							$card.find('.cg_paid_actions_order_message').text("The customer's available balance has been updated.");
						}else{
							$card.find('.cg_paid_actions_order_message').text(response && response.data && response.data.message ? response.data.message : 'The balance could not be updated.');
						}
					}).fail(function(xhr){
						var message = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data.message : 'The balance could not be updated.';
						$card.find('.cg_paid_actions_order_message').text(message);
					}).always(function(){$button.prop('disabled',false).text('Update balance');});
				});
			});
		</script>
		<?php
	}
}
