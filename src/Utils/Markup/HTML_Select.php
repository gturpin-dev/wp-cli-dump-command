<?php

namespace WPCLI_DumpCommand\Utils\Markup;

/**
 * HTML Selection Tag generator
 */
class HTML_Select {

	/**
	 * The selected option of the select
	 */
	private string $selected = '';
	
	private array $options = [];

	/**
	 * @param array $options array of options to add to the select ( key = value and value = label )
	 */
	public function __construct( array $options = [] ) {
		foreach ( $options as $key => $value ) {
			$this->add_option( $key, $value );
		}
	}

	/**
	 * Add an option to the select
	 *
	 * @param string $value The value of the option
	 * @param string $label The label of the option
	 *
	 * @return void
	 */
	public function add_option( string $value, string $label ): void {
		$value = sanitize_title( $value );      // Slugify the value
		$label = sanitize_text_field( $label ); // Sanitize the label
		$this->options[ $value ] = $label;
	}

	/**
	 * Remove an option from the select
	 *
	 * @param string $value The value of the option to remove
	 */
	public function remove_option( string $value ) {
		unset( $this->options[ $value ] );
	}

	/**
	 * Set the selected option of the select
	 *
	 * @param string $value The value of the option to select
	 *
	 * @return void
	 */
	public function set_selected( string $value ): void {
		$this->selected = $value;
	}
	
	/**
	 * Generate the HTML for the select
	 *
	 * @param string $name The name of the select
	 * @param string $id   The id of the select
	 *
	 * @return void
	 */
	public function build( string $name, string $id = null ) {
		// Bail if there are no options
		if ( empty( $this->options ) ) return;

		$id          = $id ?? $name;
		$select_html = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '">';

		foreach ( $this->options as $value => $label ) {
			$is_selected = $this->selected === $value;
			$select_html .= '<option value="' . esc_attr( $value ) . '" ' . selected( $is_selected, true, false ) . '>' . esc_html( $label ) . '</option>';
		}

		$select_html .= '</select>';

		return $select_html;
	}
}