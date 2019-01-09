<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CepatLakoo_Best_Selling_Products_Widget_Elementor extends Widget_Base {

	public function get_name() {
		return 'cepatlakoo-best-selling-product';
	}

	public function get_title() {
		return esc_html__( 'CL - Best Selling Products', 'cepatlakoo' );
	}

	public function get_icon() {
		// Icon name from the Elementor font file, as per http://dtbaker.net/web-development/creating-your-own-custom-elementor-widgets/
		return 'eicon-woocommerce';
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'cepatlakoo_section_name',
			[
				'label' => esc_html__( 'CL - Best Selling Products', 'cepatlakoo' ),
			]
		);

		$this->add_control(
         	'cepatlakoo_posts_column',
         	[
	            'label' => esc_html__( 'Number of Column', 'cepatlakoo' ),
	            'type' => Controls_Manager::SELECT,
	            'default' => '4',
	            'options' => [
	               '1' => '1',
	               '2' => '2',
	               '3' => '3',
	               '4' => '4',
	            ]
         	]
      	);

		$this->add_control(
			'cepatlakoo_posts_per_page',
			[
				'label' => esc_html__( 'Number of Items', 'cepatlakoo' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		
		// Reference : https://github.com/pojome/elementor/issues/738
   		$settings = $this->get_settings();
		$post_count = ! empty( $settings['cepatlakoo_posts_per_page'] ) ? (int)$settings['cepatlakoo_posts_per_page'] : 4;
		$post_column = ! empty( $settings['cepatlakoo_posts_column'] ) ? (int)$settings['cepatlakoo_posts_column'] : 4;

		echo do_shortcode( '[products limit="'. $post_count .'"  columns="'. $post_column .'" best_selling="true" ]' );
	}

	protected function content_template() {}

	public function render_plain_content( $instance = [] ) {}

}
Plugin::instance()->widgets_manager->register_widget_type( new CepatLakoo_Best_Selling_Products_Widget_Elementor() );
