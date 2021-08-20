<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
	if( $preview ) {
		$last_order_id     = wc_get_orders( array( 'limit' => 1, 'return' => 'ids' ) );
		$template_order    = wc_get_order( reset( $last_order_id ) );
		$template_document = wcpdf_get_document( 'invoice', $template_order );

		// include preview styles and main div
		?>
		<style>
			<?php include_once( plugin_dir_path( __FILE__ ).'preview.css' ); ?>
		</style>
		<div id="preview">
		<?php
	} else {
		$template_order    = $this->order;
		$template_document = $this;
	}
?>

<?php do_action( 'wpo_wcpdf_before_document', $template_document->type, $template_order ); ?>

<table class="head container">
	<tr>
		<td class="header">
		<?php
		if( $template_document->has_header_logo() ) {
			$template_document->header_logo();
		} else {
			echo $template_document->get_title();
		}
		?>
		</td>
		<td class="shop-info">
			<?php do_action( 'wpo_wcpdf_before_shop_name', $template_document->type, $template_order ); ?>
			<div class="shop-name"><h3><?php $template_document->shop_name(); ?></h3></div>
			<?php do_action( 'wpo_wcpdf_after_shop_name', $template_document->type, $template_order ); ?>
			<?php do_action( 'wpo_wcpdf_before_shop_address', $template_document->type, $template_order ); ?>
			<div class="shop-address"><?php $template_document->shop_address(); ?></div>
			<?php do_action( 'wpo_wcpdf_after_shop_address', $template_document->type, $template_order ); ?>
		</td>
	</tr>
</table>

<h1 class="document-type-label">
<?php if( $template_document->has_header_logo() ) echo $template_document->get_title(); ?>
</h1>

<?php do_action( 'wpo_wcpdf_after_document_label', $template_document->type, $template_order ); ?>

<table class="order-data-addresses">
	<tr>
		<td class="address billing-address">
			<!-- <h3><?php _e( 'Billing Address:', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3> -->
			<?php do_action( 'wpo_wcpdf_before_billing_address', $template_document->type, $template_order ); ?>
			<?php $template_document->billing_address(); ?>
			<?php do_action( 'wpo_wcpdf_after_billing_address', $template_document->type, $template_order ); ?>
			<?php if ( isset($template_document->settings['display_email']) ) { ?>
			<div class="billing-email"><?php $template_document->billing_email(); ?></div>
			<?php } ?>
			<?php if ( isset($template_document->settings['display_phone']) ) { ?>
			<div class="billing-phone"><?php $template_document->billing_phone(); ?></div>
			<?php } ?>
		</td>
		<td class="address shipping-address">
			<?php if ( !empty($template_document->settings['display_shipping_address']) && ( $template_document->ships_to_different_address() || $template_document->settings['display_shipping_address'] == 'always' ) ) { ?>
			<h3><?php _e( 'Ship To:', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
			<?php do_action( 'wpo_wcpdf_before_shipping_address', $template_document->type, $template_order ); ?>
			<?php $template_document->shipping_address(); ?>
			<?php do_action( 'wpo_wcpdf_after_shipping_address', $template_document->type, $template_order ); ?>
			<?php } ?>
		</td>
		<td class="order-data">
			<table>
				<?php do_action( 'wpo_wcpdf_before_order_data', $template_document->type, $template_order ); ?>
				<?php if ( isset($template_document->settings['display_number']) ) { ?>
				<tr class="invoice-number">
					<th><?php echo $template_document->get_number_title(); ?></th>
					<td><?php $template_document->invoice_number(); ?></td>
				</tr>
				<?php } ?>
				<?php if ( isset($template_document->settings['display_date']) ) { ?>
				<tr class="invoice-date">
					<th><?php echo $template_document->get_date_title(); ?></th>
					<td><?php $template_document->invoice_date(); ?></td>
				</tr>
				<?php } ?>
				<tr class="order-number">
					<th><?php _e( 'Order Number:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<td><?php $template_document->order_number(); ?></td>
				</tr>
				<tr class="order-date">
					<th><?php _e( 'Order Date:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<td><?php $template_document->order_date(); ?></td>
				</tr>
				<tr class="payment-method">
					<th><?php _e( 'Payment Method:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<td><?php $template_document->payment_method(); ?></td>
				</tr>
				<?php do_action( 'wpo_wcpdf_after_order_data', $template_document->type, $template_order ); ?>
			</table>			
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_order_details', $template_document->type, $template_order ); ?>

<table class="order-details">
	<thead>
		<tr>
			<th class="product"><?php _e('Product', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="quantity"><?php _e('Quantity', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="price"><?php _e('Price', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $items = $template_document->get_order_items(); if( sizeof( $items ) > 0 ) : foreach( $items as $item_id => $item ) : ?>
		<tr class="<?php echo apply_filters( 'wpo_wcpdf_item_row_class', 'item-'.$item_id, $template_document->type, $template_order, $item_id ); ?>">
			<td class="product">
				<?php $description_label = __( 'Description', 'woocommerce-pdf-invoices-packing-slips' ); // registering alternate label translation ?>
				<span class="item-name"><?php echo $item['name']; ?></span>
				<?php do_action( 'wpo_wcpdf_before_item_meta', $template_document->type, $item, $template_order  ); ?>
				<span class="item-meta"><?php echo $item['meta']; ?></span>
				<dl class="meta">
					<?php $description_label = __( 'SKU', 'woocommerce-pdf-invoices-packing-slips' ); // registering alternate label translation ?>
					<?php if( !empty( $item['sku'] ) ) : ?><dt class="sku"><?php _e( 'SKU:', 'woocommerce-pdf-invoices-packing-slips' ); ?></dt><dd class="sku"><?php echo $item['sku']; ?></dd><?php endif; ?>
					<?php if( !empty( $item['weight'] ) ) : ?><dt class="weight"><?php _e( 'Weight:', 'woocommerce-pdf-invoices-packing-slips' ); ?></dt><dd class="weight"><?php echo $item['weight']; ?><?php echo get_option('woocommerce_weight_unit'); ?></dd><?php endif; ?>
				</dl>
				<?php do_action( 'wpo_wcpdf_after_item_meta', $template_document->type, $item, $template_order  ); ?>
			</td>
			<td class="quantity"><?php echo $item['quantity']; ?></td>
			<td class="price"><?php echo $item['order_price']; ?></td>
		</tr>
		<?php endforeach; endif; ?>
	</tbody>
	<tfoot>
		<tr class="no-borders">
			<td class="no-borders">
				<div class="document-notes">
					<?php do_action( 'wpo_wcpdf_before_document_notes', $template_document->type, $template_order ); ?>
					<?php if ( $template_document->get_document_notes() ) : ?>
						<h3><?php _e( 'Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
						<?php $template_document->document_notes(); ?>
					<?php endif; ?>
					<?php do_action( 'wpo_wcpdf_after_document_notes', $template_document->type, $template_order ); ?>
				</div>
				<div class="customer-notes">
					<?php do_action( 'wpo_wcpdf_before_customer_notes', $template_document->type, $template_order ); ?>
					<?php if ( $template_document->get_shipping_notes() ) : ?>
						<h3><?php _e( 'Customer Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
						<?php $template_document->shipping_notes(); ?>
					<?php endif; ?>
					<?php do_action( 'wpo_wcpdf_after_customer_notes', $template_document->type, $template_order ); ?>
				</div>				
			</td>
			<td class="no-borders" colspan="2">
				<table class="totals">
					<tfoot>
						<?php foreach( $template_document->get_woocommerce_totals() as $key => $total ) : ?>
						<tr class="<?php echo $key; ?>">
							<th class="description"><?php echo $total['label']; ?></th>
							<td class="price"><span class="totals-price"><?php echo $total['value']; ?></span></td>
						</tr>
						<?php endforeach; ?>
					</tfoot>
				</table>
			</td>
		</tr>
	</tfoot>
</table>

<div class="bottom-spacer"></div>

<?php do_action( 'wpo_wcpdf_after_order_details', $template_document->type, $template_order ); ?>

<?php if ( $template_document->get_footer() ): ?>
<div id="footer">
	<!-- hook available: wpo_wcpdf_before_footer -->
	<?php $template_document->footer(); ?>
	<!-- hook available: wpo_wcpdf_after_footer -->
</div><!-- #letter-footer -->
<?php endif; ?>
<?php do_action( 'wpo_wcpdf_after_document', $template_document->type, $template_order ); ?>

<?php if( $preview ) echo '</div>'; ?>