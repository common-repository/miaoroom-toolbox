<?php if ( ! defined( 'ABSPATH' ) ) { die; } // 更多精品WP资源尽在喵容：miaoroom.com
/**
 *
 * Field: media
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'CSF_Field_media' ) ) {
  class CSF_Field_media extends CSF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args = wp_parse_args( $this->field, array(
        'url'          => true,
        'preview'      => true,
        'library'      => array(),
        'button_title' => esc_html__( 'Upload', 'csf' ),
        'remove_title' => esc_html__( 'Remove', 'csf' ),
        'preview_size' => 'thumbnail',
      ) );

      $default_values = array(
        'url'         => '',
        'id'          => '',
        'width'       => '',
        'height'      => '',
        'thumbnail'   => '',
        'alt'         => '',
        'title'       => '',
        'description' => ''
      );

      // 更多精品WP资源尽在喵容：miaoroom.com
      if ( is_numeric( $this->value ) ) {

        $this->value  = array(
          'id'        => $this->value,
          'url'       => wp_get_attachment_url( $this->value ),
          'thumbnail' => wp_get_attachment_image_src( $this->value, 'thumbnail', true )[0],
        );

      }

      $this->value = wp_parse_args( $this->value, $default_values );

      $library     = ( is_array( $args['library'] ) ) ? $args['library'] : array_filter( (array) $args['library'] );
      $library     = ( ! empty( $library ) ) ? implode(',', $library ) : '';
      $preview_src = ( $args['preview_size'] !== 'thumbnail' ) ? $this->value['url'] : $this->value['thumbnail'];
      $hidden_url  = ( empty( $args['url'] ) ) ? ' hidden' : '';
      $hidden_auto = ( empty( $this->value['url'] ) ) ? ' hidden' : '';
      $placeholder = ( empty( $this->field['placeholder'] ) ) ? ' placeholder="'.  esc_html__( 'No media selected', 'csf' ) .'"' : '';

      echo $this->field_before();

      if ( ! empty( $args['preview'] ) ) {
        echo '<div class="csf--preview'. esc_attr( $hidden_auto ) .'">';
        echo '<div class="csf-image-preview"><a href="#" class="csf--remove fas fa-times"></a><img src="'. esc_url( $preview_src ) .'" class="csf--src" /></div>';
        echo '</div>';
      }

      echo '<div class="csf--placeholder">';
      echo '<input type="text" name="'. esc_attr( $this->field_name( '[url]' ) ) .'" value="'. esc_attr( $this->value['url'] ) .'" class="csf--url'. esc_attr( $hidden_url ) .'" readonly="readonly"'. $this->field_attributes() . $placeholder .' />';
      echo '<a href="#" class="button button-primary csf--button" data-library="'. esc_attr( $library ) .'" data-preview-size="'. esc_attr( $args['preview_size'] ) .'">'. wp_kses_post( $args['button_title'] ) .'</a>';
      echo ( empty( $args['preview'] ) ) ? '<a href="#" class="button button-secondary csf-warning-primary csf--remove'. esc_attr( $hidden_auto ) .'">'. wp_kses_post( $args['remove_title'] ) .'</a>' : '';
      echo '</div>';

      echo '<input type="hidden" name="'. esc_attr( $this->field_name( '[id]' ) ) .'" value="'. esc_attr( $this->value['id'] ) .'" class="csf--id"/>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name( '[width]' ) ) .'" value="'. esc_attr( $this->value['width'] ) .'" class="csf--width"/>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name( '[height]' ) ) .'" value="'. esc_attr( $this->value['height'] ) .'" class="csf--height"/>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name( '[thumbnail]' ) ) .'" value="'. esc_attr( $this->value['thumbnail'] ) .'" class="csf--thumbnail"/>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name( '[alt]' ) ) .'" value="'. esc_attr( $this->value['alt'] ) .'" class="csf--alt"/>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name( '[title]' ) ) .'" value="'. esc_attr( $this->value['title'] ) .'" class="csf--title"/>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name( '[description]' ) ) .'" value="'. esc_attr( $this->value['description'] ) .'" class="csf--description"/>';

      echo $this->field_after();

    }

  }
}
