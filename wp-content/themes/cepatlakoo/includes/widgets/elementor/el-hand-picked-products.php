<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CepatLakoo_Hand_Picked_Products_Widget_Elementor extends Widget_Base {

	public function get_name() {
		return 'cepatlakoo-hand-picked-product';
	}

	public function get_title() {
		return esc_html__( 'CL - Hand Picked Products', 'cepatlakoo' );
	}

	public function get_icon() {
		// Icon name from the Elementor font file, as per http://dtbaker.net/web-development/creating-your-own-custom-elementor-widgets/
		return 'eicon-woocommerce';
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'cepatlakoo_section_name',
			[
				'label' => esc_html__( 'CL - Hand Picked Products', 'cepatlakoo' ),
			]
		);

		$this->add_control(
			'cepatlakoo_products_id',
			[
				'label' => esc_html__( 'Products ID', 'cepatlakoo' ),
				'type' => Controls_Manager::TEXT,
				'title' => esc_html__( 'Enter products id, separate by comma (,) ex : 1990, 1890, etc', 'cepatlakoo' ),
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

		$this->add_control(
         	'cepatlakoo_post_orderby',
         	[
	            'label' => esc_html__( 'Sort Order By', 'cepatlakoo' ),
	            'type' => Controls_Manager::SELECT,
	            'default' => 'date',
	            'options' => [
	               'date' => esc_html__( 'Date', 'cepatlakoo' ),
	               'title' => esc_html__( 'Title', 'cepatlakoo' ),
	               'rand' => esc_html__( 'Random', 'cepatlakoo' ),
	            ]
         	]
      	);

      	$this->add_control(
         	'cepatlakoo_post_order',
         	[
	            'label' => esc_html__( 'Sort Order', 'cepatlakoo' ),
	            'type' => Controls_Manager::SELECT,
	            'default' => 'desc',
	            'options' => [
	               'asc' => esc_html__( 'Ascending', 'cepatlakoo' ),
	               'desc' => esc_html__( 'Descending', 'cepatlakoo' ),
	            ]
         	]
      	);

		$this->end_controls_section();
	}

	protected function render() {
		
		// Reference : https://github.com/pojome/elementor/issues/738
		// get our input from the widget settings.
    	$settings = $this->get_settings();
		$post_ids = ! empty( $settings['cepatlakoo_products_id'] ) ? $settings['cepatlakoo_products_id'] : '';
		$post_count = ! empty( $settings['cepatlakoo_posts_per_page'] ) ? (int)$settings['cepatlakoo_posts_per_page'] : 4;
		$post_column = ! empty( $settings['cepatlakoo_posts_column'] ) ? (int)$settings['cepatlakoo_posts_column'] : 4;
		$post_order_by = ! empty( $settings['cepatlakoo_post_orderby'] ) ? $settings['cepatlakoo_post_orderby'] : 'date';
		$post_order = ! empty( $settings['cepatlakoo_post_order'] ) ? $settings['cepatlakoo_post_order'] : 'desc';
		
		echo do_shortcode( '[products limit="'. $post_count .'"  columns="'. $post_column .'" orderby="'. $post_order_by .'" order="'. $post_order .'" ids="'. $post_ids .'"]' );
	}

	protected function content_template() {}

	public function render_plain_content( $instance = [] ) {}

}
Plugin::instance()->widgets_manager->register_widget_type( new CepatLakoo_Hand_Picked_Products_Widget_Elementor() );
