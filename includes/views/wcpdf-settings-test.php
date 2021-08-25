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
		</h2>
		<script src="<?= WPO_WCPDF()->plugin_url() ?>/assets/js/pdf_js/pdf.js"></script>
		<div class="preview-wrapper" style="position:relative; background-color:white; border-left: 1px solid #c3c4c7; border-bottom: 1px solid #c3c4c7; border-right: 1px solid #c3c4c7;">
			<canvas id="the-canvas" style="width: 100%; direction: ltr;"></canvas>
		</div>
		<?php
			$last_order_id = wc_get_orders( array( 'limit' => 1, 'return' => 'ids' ) );
			$order         = wc_get_order( reset( $last_order_id ) );
			$document      = wcpdf_get_document( 'invoice', $order );
			if( $document->exists() ) {
				try {
					$document = wcpdf_get_document( $document->type, $order->get_id(), true );
					if ( !$document ) { // something went wrong, continue trying with other documents
						return;
					}
					$filename = $document->get_filename();
					$pdf_path = $filename;
	
					$lock_file = apply_filters( 'wpo_wcpdf_lock_attachment_file', true );
	
					// if this file already exists in the temp path, we'll reuse it if it's not older than 60 seconds
					$max_reuse_age = apply_filters( 'wpo_wcpdf_reuse_attachment_age', 60 );
					if ( file_exists($pdf_path) && $max_reuse_age > 0 ) {
						// get last modification date
						if ($filemtime = filemtime($pdf_path)) {
							$time_difference = time() - $filemtime;
							if ( $time_difference < $max_reuse_age ) {
								// check if file is still being written to
								if ( $lock_file && WPO_WCPDF()->main->wait_for_file_lock( $pdf_path ) === false ) {
									return;
								} else {
									// make sure this gets logged, but don't abort process
									wcpdf_log_error( "Attachment file locked (reusing: {$pdf_path})", 'critical' );
								}
							}
						}
					}
	
					// get pdf data & store
					$pdf_data = $document->get_pdf();
	
					if ( $lock_file ) {
						file_put_contents ( $pdf_path, $pdf_data, LOCK_EX );
					} else {
						file_put_contents ( $pdf_path, $pdf_data );					
					}
	
					// wait for file lock
					if ( $lock_file && WPO_WCPDF()->main->wait_for_file_lock( $pdf_path ) === true ) {
						wcpdf_log_error( "Attachment file locked ({$pdf_path})", 'critical' );
					}
						
				} catch ( \Exception $e ) {
					wcpdf_log_error( $e->getMessage(), 'critical', $e );
					return;
				} catch ( \Dompdf\Exception $e ) {
					wcpdf_log_error( 'DOMPDF exception: '.$e->getMessage(), 'critical', $e );
					return;
				} catch ( \Error $e ) {
					wcpdf_log_error( $e->getMessage(), 'critical', $e );
					return;
				}
			}
		?>
		<script id="script">
			var url = '<?= $pdf_path; ?>';

			pdfjsLib.GlobalWorkerOptions.workerSrc = '<?= WPO_WCPDF()->plugin_url() ?>/assets/js/pdf_js/pdf.worker.js';

			var loadingTask = pdfjsLib.getDocument(url);

			loadingTask.promise.then(function(pdf) {

				pdf.getPage(1).then(function(page) {
					var scale = 1.5;
					var viewport = page.getViewport({ scale: scale, });

					var canvas = document.getElementById('the-canvas');
					var context = canvas.getContext('2d');
					canvas.height = viewport.height;
					canvas.width = viewport.width;

					var renderContext = {
						canvasContext: context,
						viewport: viewport,
					};
					page.render(renderContext);
				});
				
			});
		</script>
	</div>
</div>
