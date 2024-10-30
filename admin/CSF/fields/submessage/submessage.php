<?php if ( ! defined( 'ABSPATH' ) ) { die; } // 更多精品WP资源尽在喵容：miaoroom.com
/**
 *
 * Field: submessage
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'CSF_Field_submessage' ) ) {
  class CSF_Field_submessage extends CSF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $style = ( ! empty( $this->field['style'] ) ) ? $this->field['style'] : 'normal';

      echo '<div class="csf-submessage csf-submessage-'. esc_attr( $style ) .'">'. wp_kses_post( $this->field['content'] ) .'</div>';

    }

  }
}
