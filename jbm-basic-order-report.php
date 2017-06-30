<?php
/*
Plugin Name: _Basic Order Report
Description: Basic Order Report Page
Version: 1.0
*/

function jbm_basic_order_report() {
	$jb_parent_slug = 'woocommerce';
	$jb_page_title = 'Basic Order Report';
	$jb_menu_title = 'Basic Order Report';
	$jb_capability = 'manage_affiliates';
	$jb_menu_slug = 'jbm-basic-order-report';
	$jb_callback = 'jbm_basic_order_report_html';
	//$jb_icon_url = 'dashicons-media-spreadsheet';
	//$jb_menu_position = 120;
	add_submenu_page(  $jb_parent_slug, $jb_page_title,  $jb_menu_title,  $jb_capability,  $jb_menu_slug,  $jb_callback );
}

function jbm_basic_order_report_html() {
	if ( isset($_POST['start_date']) ) {
		$start_date = $_POST['start_date'];
	} else { 
		$start_date = date('Y-m-d', strtotime('-1 days'));
	}
	if ( isset($_POST['end_date']) ) {
		$end_date = $_POST['end_date'];
	} else { 
		$end_date = date('Y-m-d', strtotime('today'));
	}
	
	if ( isset($_POST["order_status"]) ) {
		foreach ( $_POST["order_status"] as $check_status ) {
			$varvar = str_replace('-','_',$check_status).'_check';
			${$varvar} = 'checked';
			$order_statuss[] = 'wc-'.$check_status;
		}
	} else {
		$completed_check = 'checked';
		$completed = 'completed';
		$order_statuss = array('wc-completed');
	}
		
?>
<style>
	.jb-affiliate-report, .jb-affiliate-report th, .jb-affiliate-report td {
		border: 1px solid #cdcdcd;
		border-collapse: collapse;
	}
	.jb-affiliate-report {
		width: 97%;
		margin: 3vw 1vw;
	}
	.jb-affiliate-report th, .jb-affiliate-report td {
		padding: 4px 8px;
	}
	.basic_order_search_form label {
		padding: 3px;
	}
</style>

<h1>Basic Order Report</h1>
<div>
<form action="" method="post" class="basic_order_search_form">
	<label>Start Date: <input type="date" name="start_date" id="start_date" value="<?=$start_date;?>"   /></label> Order Creation Date
	<br>
	<label>End Date: <input type="date" name="end_date" id="end_date" value="<?=$end_date;?>"   /></label><br>
	<label><input type="checkbox" name="order_status[]" value="completed" <?=$completed_check?> /> Completed</label> | 
	<label><input type="checkbox" name="order_status[]" value="processing" <?=$processing_check?> /> Processing</label> | 
	<label><input type="checkbox" name="order_status[]" value="pending" <?=$pending_check?> /> Pending</label> | 
	<label><input type="checkbox" name="order_status[]" value="on-hold" <?=$on_hold_check?> /> On Hold</label> | 
	<label><input type="checkbox" name="order_status[]" value="refunded" <?=$refunded_check?> /> Refunded</label> | 
	<label><input type="checkbox" name="order_status[]" value="cancelled" <?=$cancelled_check?> /> Cancelled</label> | 
	<label><input type="checkbox" name="order_status[]" value="failed" <?=$failed_check?> /> Failed</label>
	<p><button class="button" type="submit" name="jbm_order_query" value="1" >Search Orders</button></p>
</form>	
<?php
	
	if( isset($_POST['jbm_order_query']) ) :
		$args = array(
			//'date_after' => $start_date.' 00:00:00',
			//'date_before' => $end_date.' 23:59:59',
			'date_created' => $start_date.'...'.$end_date,
			'limit' => -1,
			'post_status' => $order_statuss,
		);
		$customer_orders = wc_get_orders( $args );

		$order_rows = "";
		$subtotal = 0;
		$shipping_total = 0;
		$total_tax = 0;
		$discount_total = 0;
		$total = 0;
		foreach ( $customer_orders as $customer_order ) {
			
			$order_date = wc_format_datetime( $customer_order->get_date_created(), 'Y-m-d H:i');
			if ( ! empty($customer_order->date_paid) ) 
				$date_paid = wc_format_datetime( $customer_order->get_date_paid(), 'Y-m-d H:i' );
			else $date_paid = '';
			if ( ! empty($customer_order->date_completed) ) 
				$date_completed = wc_format_datetime( $customer_order->get_date_completed(), 'Y-m-d H:i' );
			else $date_completed = '';
			
			$order_rows .= "
			<tr>
				<td><a href='/wp-admin/post.php?post=".$customer_order->get_id()."&action=edit' target='_blank'>".$customer_order->get_id()."</a></td>";
			if( current_user_can('manage_options') ) $order_rows .= "<td><a href='/wp-admin/admin.php?page=jbm-generate-referrals&order_id=".$customer_order->get_id()."' target='_blank'>".$customer_order->get_id()."</a></td>";
			$order_rows .= "
				<td>".$order_date."</td>
				<td>".$date_paid."</td>
				<td>".$date_completed."</td>
				<td><a href='/wp-admin/edit.php?post_status=all&post_type=shop_order&_customer_user=".$customer_order->customer_id."' target='_blank'>".$customer_order->billing_first_name.' '.$customer_order->billing_last_name."</a></td>
				<td>".$customer_order->status."</td>
				<td>".number_format($customer_order->subtotal,2)."</td>
				<td>".number_format($customer_order->shipping_total,2)."</td>
				<td>".number_format($customer_order->total_tax,2)."</td>
				<td>".number_format($customer_order->discount_total,2)."</td>
				<td>".number_format($customer_order->total,2)."</td>
			</tr>";
			$subtotal += $customer_order->subtotal;
			$shipping_total += $customer_order->shipping_total;
			$total_tax += $customer_order->total_tax;
			$discount_total += $customer_order->discount_total;
			$total += $customer_order->total;
		}
		$order_rows .= "
		<tr>
			<th>Totals</th>
			<th></th>";
		if( current_user_can('manage_options') ) $order_rows .= "<th></th>";
		$order_rows .= "
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th>".number_format($subtotal,2)."</th>
			<th>".number_format($shipping_total,2)."</th>
			<th>".number_format($total_tax,2)."</th>
			<th>".number_format($discount_total,2)."</th>
			<th>".number_format($total,2)."</th>
		</tr>";

		echo count($customer_orders)." Orders found.";
	endif;
	
?>
</div>
<div>
	<table class="jb-affiliate-report">
		<thead>
			<tr>
				<th>Order#</th>
				<?php if( current_user_can('manage_options') ) echo "<th>Referrals</th>";?>
				<th>Order Date</th>
				<th>Paid Date</th>
				<th>Ship Date</th>
				<th>Billing Name</th>
				<th>Status</th>
				<th>Sub Total</th>
				<th>Shipping</th>
				<th>Tax</th>
				<th>Discounts</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<?=$order_rows?>
		</tbody>
	</table>
</div>
<?php
}

add_action( 'admin_menu', 'jbm_basic_order_report' );
