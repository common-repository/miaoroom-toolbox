<?php if ( ! defined( 'ABSPATH' ) ) { die; } // 更多精品WP资源尽在喵容：miaoroom.com
/**
 *
 * Field: upload
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'CSF_Field_upload' ) ) {
  class CSF_Field_upload extends CSF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args = wp_parse_args( $this->field, array(
        'library'      => array(),
        'button_title' => esc_html__( 'Upload', 'csf' ),
        'remove_title' => esc_html__( 'Remove', 'csf' ),
      ) );

      echo $this->field_before();

      $library = ( is_array( $args['library'] ) ) ? $args['library'] : array_filter( (array) $args['library'] );
      $library = ( ! empty( $library ) ) ? implode(',', $library ) : '';
      $hidden  = ( empty( $this->value ) ) ? ' hidden' : '';

      echo '<div class="csf--wrap">';
      echo '<input type="text" name="'. esc_attr( $this->field_name() ) .'" value="'. esc_attr( $this->value ) .'"'. $this->field_attributes() .'/>';
      echo '<a href="#" class="button button-primary csf--button" data-library="'. esc_attr( $library ) .'">'. wp_kses_post( $args['button_title'] ) .'</a>';
      echo '<a href="#" class="button button-secondary csf-warning-primary csf--remove'. esc_attr( $hidden ) .'">'. wp_kses_post( $args['remove_title'] ) .'</a>';
      echo '</div>';

      echo $this->field_after();

    }
  }
}
