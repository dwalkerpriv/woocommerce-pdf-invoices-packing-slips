jQuery( function( $ ) {
	$('.edit-next-number').on('click', function( event ) {
		// enable input & show save button
		$( this ).hide();
		$( this ).siblings( 'input' ).prop('disabled', false);
		$( this ).siblings( '.save-next-number.button').show();
	});

	$('.save-next-number').on('click', function( event ) {
		$input = $( this ).siblings( 'input' );
		$input.addClass('ajax-waiting');
		var data = {
			security:      $input.data('nonce'),
			action:        "wpo_wcpdf_set_next_number",
			store:         $input.data('store'),
			number:        $input.val(), 
		};

		xhr = $.ajax({
			type:		'POST',
			url:		wpo_wcpdf_admin.ajaxurl,
			data:		data,
			success:	function( response ) {
				$input.removeClass('ajax-waiting');
				$input.siblings( '.edit-next-number' ).show();
				$input.prop('disabled', 'disabled');
				$input.siblings( '.save-next-number.button').hide();
			}
		});
	});

	$("[name='wpo_wcpdf_documents_settings_invoice[display_number]']").on('change', function (event) {
		if ($(this).val() == 'order_number') {
			$(this).closest('td').find('.description').slideDown();
		} else {
			$(this).closest('td').find('.description').hide();
		}
	}).trigger('change');






	// Preview on page load
	$( document ).ready( ajax_load_preview( $('#wpo-wcpdf-preview #shop_name') ) );

	// Preview on user input
	$( '#wpo-wcpdf-preview #shop_name' ).on( 'keyup paste', function() {
		setTimeout( function() {
			ajax_load_preview( $('#wpo-wcpdf-preview #shop_name') );
		}, 2000 );
	} );

	function ajax_load_preview( elem ) {
		let shop_name = elem.val();
		let wrapper   = $( '#preview-wrapper' );
		let order_id  = wrapper.data('order_id');
		let nonce     = wrapper.data('nonce');
		let worker    = wpo_wcpdf_admin.pdfjs_worker;
		let data      = {
			security:  nonce,
			action:    'wpo_wcpdf_preview',
			order_id:  order_id,
			shop_name: shop_name,
		};

		// block ui
		wrapper.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );
		
		$.ajax({
			type:     'POST',
			url:      wpo_wcpdf_admin.ajaxurl,
			data:     data,
			success: function( response ) {
				if( response.data.pdf_data ) {
					let canvas_id = 'preview-canvas';
					$( '#'+canvas_id ).remove();
					wrapper.append( '<canvas id="'+canvas_id+'" style="width:100%;"></canvas>' );
					pdf_js( worker, canvas_id, response.data.pdf_data );
					wrapper.unblock();
				}
			}
		});
	}

	function pdf_js( worker, canvas_id, pdf_data ) {
		// atob() is used to convert base64 encoded PDF to binary-like data.
		// (See also https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/
		// Base64_encoding_and_decoding.)
		var pdfData = atob( pdf_data );

		// Loaded via <script> tag, create shortcut to access PDF.js exports.
		var pdfjsLib = window['pdfjs-dist/build/pdf'];

		// The workerSrc property shall be specified.
		pdfjsLib.GlobalWorkerOptions.workerSrc = worker;

		// Using DocumentInitParameters object to load binary data.
		var loadingTask = pdfjsLib.getDocument({data: pdfData});
		loadingTask.promise.then(function(pdf) {
			console.log('PDF loaded');

			// fix for multiple renders https://stackoverflow.com/a/59591027
			if (this.pdf) {
				this.pdf.destroy();
			}
			this.pdf         = pdf;
			this.total_pages = this.pdf.numPages;
			
			// Fetch the first page
			var pageNumber = 1;
			pdf.getPage(pageNumber).then(function(page) {
				console.log('Page loaded');
				
				var scale = 1.5;
				var viewport = page.getViewport({scale: scale});

				// Prepare canvas using PDF page dimensions
				var canvas = document.getElementById(canvas_id);
				var context = canvas.getContext('2d');
				canvas.height = viewport.height;
				canvas.width = viewport.width;

				// Render PDF page into canvas context
				var renderContext = {
					canvasContext: context,
					viewport: viewport
				};
				var renderTask = page.render(renderContext);
				renderTask.promise.then(function () {
					console.log('Page rendered');
				});
			});
		}, function (reason) {
			// PDF loading error
			console.error(reason);
		}
	);
	}
});