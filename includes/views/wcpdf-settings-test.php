<?php defined( 'ABSPATH' ) or exit; ?>
<script type="text/javascript">
	jQuery( function( $ ) {
		$("#footer-thankyou").html("If you like <strong>WooCommerce PDF Invoices & Packing Slips</strong> please leave us a <a href='https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices-packing-slips?rate=5#postform'>★★★★★</a> rating. A huge thank you in advance!");
	});
</script>
<div class="wrap">
	<div class="icon32" id="icon-options-general"><br /></div>
	<h2><?php _e( 'WooCommerce PDF Invoices', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	
	<!-- Settings side -->
	<div class="settings" style="width:60%; display:inline; float:left; margin-right:0; margin-left:0;">
		<h2 class="nav-tab-wrapper">
		<?php
		foreach ($settings_tabs as $tab_slug => $tab_title ) {
			$tab_link = esc_url("?page=wpo_wcpdf_options_page&tab={$tab_slug}");
			printf('<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', $tab_link, $tab_slug, (($active_tab == $tab_slug) ? 'nav-tab-active' : ''), $tab_title);
		}
		?>
		</h2>

		<form method="post" action="options.php" id="wpo-wcpdf-preview" class="<?php echo "{$active_tab} {$active_section}"; ?>">
			<?php do_action( 'wpo_wcpdf_settings_output_'.$active_tab, $active_section ); ?>
		</form>
		<?php do_action( 'wpo_wcpdf_after_settings_page', $active_tab, $active_section ); ?>
	</div>

	
	<!-- Preview side -->
	<div class="preview" style="width:40%; display:inline-block; height:auto;">
		<h2 class="nav-tab-wrapper">
			<a href="#" class="nav-tab nav-tab-preview nav-tab-active">Preview</a>
			<div style="float:right;">
				<label style="font-weight:normal; font-size:.8em;">Source:</label>
				<select name="preview-source" id="preview-source">
					<option value="last-order">Last order</option>
					<option value="specific-order">Specific order</option>
				</select>
				<input type="number" name="preview-order" id="preview-order" placeholder="Order ID">
			</div>
		</h2>
		<script src="<?= WPO_WCPDF()->plugin_url() ?>/assets/js/pdf_js/pdf.js"></script>
		<?php
			$last_order_id = wc_get_orders( array( 'limit' => 1, 'return' => 'ids' ) );
			$order_id      = reset( $last_order_id );
		?>
		<div id="preview-wrapper" data-order_id="<?= $order_id; ?>" data-nonce="<?= wp_create_nonce( 'wpo_wcpdf_preview' ); ?>" style="position:relative; background-color:white; border-left: 1px solid #c3c4c7; border-bottom: 1px solid #c3c4c7; border-right: 1px solid #c3c4c7; padding:15px;"></div>
	</div>
</div>
