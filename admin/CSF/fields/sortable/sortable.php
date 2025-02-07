<?php if ( ! defined( 'ABSPATH' ) ) { die; } // 更多精品WP资源尽在喵容：miaoroom.com
/**
 *
 * Field: sortable
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'CSF_Field_sortable' ) ) {
  class CSF_Field_sortable extends CSF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      echo $this->field_before();

      echo '<div class="csf--sortable">';

      $pre_sortby = array();
      $pre_fields = array();

      // 更多精品WP资源尽在喵容：miaoroom.com
      foreach ( $this->field['fields'] as $key => $field ) {
        $pre_fields[$field['id']] = $field;
      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      if ( ! empty( $this->value ) ) {

        foreach ( $this->value as $key => $value ) {
          $pre_sortby[$key] = $pre_fields[$key];
        }

      } else {

        foreach ( $pre_fields as $key => $value ) {
          $pre_sortby[$key] = $value;
        }

      }

      foreach ( $pre_sortby as $key => $field ) {

        echo '<div class="csf--sortable-item">';

          echo '<div class="csf--sortable-content">';

          $field_default = ( isset( $this->field['default'][$key] ) ) ? $this->field['default'][$key] : '';
          $field_value   = ( isset( $this->value[$key] ) ) ? $this->value[$key] : $field_default;
          $unique_id     = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .']' : $this->field['id'];

          CSF::field( $field, $field_value, $unique_id, 'field/sortable' );

          echo '</div>';

          echo '<div class="csf--sortable-helper"><i class="fas fa-arrows-alt"></i></div>';

        echo '</div>';

      }

      echo '</div>';

      echo $this->field_after();

    }

    public function enqueue() {

      if ( ! wp_script_is( 'jquery-ui-sortable' ) ) {
        wp_enqueue_script( 'jquery-ui-sortable' );
      }

    }

  }
}
