<?php if ( ! defined( 'ABSPATH' ) ) { die; } // 更多精品WP资源尽在喵容：miaoroom.com
/**
 *
 * Field: backup
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'CSF_Field_backup' ) ) {
  class CSF_Field_backup extends CSF_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $unique = $this->unique;
      $nonce  = wp_create_nonce( 'csf_backup_nonce' );
      $export = add_query_arg( array( 'action' => 'csf-export', 'unique' => $unique, 'nonce' => $nonce ), admin_url( 'admin-ajax.php' ) );

      echo $this->field_before();

      echo '<textarea name="csf_import_data" class="csf-import-data"></textarea>';
      echo '<button type="submit" class="button button-primary csf-confirm csf-import" data-unique="'. esc_attr( $unique ) .'" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html__( 'Import', 'csf' ) .'</button>';
      echo '<small>( '. esc_html__( 'copy-paste your backup string here', 'csf' ).' )</small>';

      echo '<hr />';
      echo '<textarea readonly="readonly" class="csf-export-data">'. esc_attr( json_encode( get_option( $unique ) ) ) .'</textarea>';
      echo '<a href="'. esc_url( $export ) .'" class="button button-primary csf-export" target="_blank">'. esc_html__( 'Export and Download Backup', 'csf' ) .'</a>';

      echo '<hr />';
      echo '<button type="submit" name="csf_transient[reset]" value="reset" class="button csf-warning-primary csf-confirm csf-reset" data-unique="'. esc_attr( $unique ) .'" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html__( 'Reset All', 'csf' ) .'</button>';
      echo '<small class="csf-text-error">'. esc_html__( 'Please be sure for reset all of options.', 'csf' ) .'</small>';

      echo $this->field_after();

    }

  }
}
