<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings_Test' ) ) :

class Settings_Test {

	protected $option_name = 'wpo_wcpdf_settings_test';

	function __construct()	{
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_test', array( $this, 'output' ), 10, 1 );
	}

	public function output( $section ) {
		settings_fields( $this->option_name );
		do_settings_sections( $this->option_name );

		submit_button();
	}

	public function init_settings() {
		$page = $option_group = $option_name = $this->option_name;

		$settings_fields = array(
			array(
				'type'		=> 'section',
				'id'		=> 'test_settings',
				'title'		=> __( 'Test settings', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'	=> 'section',
			),
			array(
				'type'		=> 'setting',
				'id'		=> 'shop_name',
				'title'		=> __( 'Shop Name', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'	=> 'text_input',
				'section'	=> 'test_settings',
				'args'		=> array(
					'option_name'	=> $option_name,
					'id'			=> 'shop_name',
					'size'			=> '72',
					'translatable'	=> true,
				)
			),
			array(
				'type'		=> 'setting',
				'id'		=> 'shop_address',
				'title'		=> __( 'Shop Address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'	=> 'textarea',
				'section'	=> 'test_settings',
				'args'		=> array(
					'option_name'	=> $option_name,
					'id'			=> 'shop_address',
					'width'			=> '72',
					'height'		=> '8',
					'translatable'	=> true,
				)
			),
		);

		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
		return;
	}

}

endif; // class_exists

return new Settings_Test();