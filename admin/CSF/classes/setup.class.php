<?php if ( ! defined( 'ABSPATH' ) ) { die; } // 更多精品WP资源尽在喵容：miaoroom.com
/**
 *
 * Setup Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'CSF' ) ) {
  class CSF {

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static $version = '2.1.5';
    public static $premium = true;
    public static $dir     = null;
    public static $url     = null;
    public static $inited  = array();
    public static $fields  = array();
    public static $args    = array(
      'options'            => array(),
      'customize_options'  => array(),
      'metaboxes'          => array(),
      'profile_options'    => array(),
      'shortcoders'        => array(),
      'taxonomy_options'   => array(),
      'widgets'            => array(),
      'comment_metaboxes'  => array(),
    );

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static $shortcode_instances = array();

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function init() {

      // 更多精品WP资源尽在喵容：miaoroom.com
      do_action( 'csf_init' );

      // 更多精品WP资源尽在喵容：miaoroom.com
      self::constants();

      // 更多精品WP资源尽在喵容：miaoroom.com
      self::includes();

      // 更多精品WP资源尽在喵容：miaoroom.com
      self::textdomain();

      add_action( 'after_setup_theme', array( 'CSF', 'setup' ) );
      add_action( 'init', array( 'CSF', 'setup' ) );
      add_action( 'switch_theme', array( 'CSF', 'setup' ) );
      add_action( 'admin_enqueue_scripts', array( 'CSF', 'add_admin_enqueue_scripts' ), 20 );
      add_action( 'admin_head', array( 'CSF', 'add_admin_head_css' ), 99 );
      add_action( 'customize_controls_print_styles', array( 'CSF', 'add_admin_head_css' ), 99 );

    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function setup() {

      // 更多精品WP资源尽在喵容：miaoroom.com
      self::include_plugin_file( 'views/welcome.php' );

      // 更多精品WP资源尽在喵容：miaoroom.com
      $params = array();
      if ( ! empty( self::$args['options'] ) ) {
        foreach ( self::$args['options'] as $key => $value ) {
          if ( ! empty( self::$args['sections'][$key] ) && ! isset( self::$inited[$key] ) ) {

            $params['args']     = $value;
            $params['sections'] = self::$args['sections'][$key];
            self::$inited[$key] = true;

            CSF_Options::instance( $key, $params );

            if ( ! empty( $value['show_in_customizer'] ) ) {
              $value['output_css'] = false;
              $value['enqueue_webfont'] = false;
              self::$args['customize_options'][$key] = $value;
              self::$inited[$key] = null;
            }

          }
        }
      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      $params = array();
      if ( ! empty( self::$args['customize_options'] ) ) {
        foreach ( self::$args['customize_options'] as $key => $value ) {
          if ( ! empty( self::$args['sections'][$key] ) && ! isset( self::$inited[$key] ) ) {

            $params['args']     = $value;
            $params['sections'] = self::$args['sections'][$key];
            self::$inited[$key] = true;

            CSF_Customize_Options::instance( $key, $params );


          }
        }
      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      $params = array();
      if ( ! empty( self::$args['metaboxes'] ) ) {
        foreach ( self::$args['metaboxes'] as $key => $value ) {
          if ( ! empty( self::$args['sections'][$key] ) && ! isset( self::$inited[$key] ) ) {

            $params['args']     = $value;
            $params['sections'] = self::$args['sections'][$key];
            self::$inited[$key] = true;

            CSF_Metabox::instance( $key, $params );

          }
        }
      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      $params = array();
      if ( ! empty( self::$args['profile_options'] ) ) {
        foreach ( self::$args['profile_options'] as $key => $value ) {
          if ( ! empty( self::$args['sections'][$key] ) && ! isset( self::$inited[$key] ) ) {

            $params['args']     = $value;
            $params['sections'] = self::$args['sections'][$key];
            self::$inited[$key] = true;

            CSF_Profile_Options::instance( $key, $params );

          }
        }
      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      $params = array();
      if ( ! empty( self::$args['shortcoders'] ) ) {

        foreach ( self::$args['shortcoders'] as $key => $value ) {
          if ( ! empty( self::$args['sections'][$key] ) && ! isset( self::$inited[$key] ) ) {

            $params['args']     = $value;
            $params['sections'] = self::$args['sections'][$key];
            self::$inited[$key] = true;

            CSF_Shortcoder::instance( $key, $params );

          }
        }

        // 更多精品WP资源尽在喵容：miaoroom.com
        if ( ! empty( CSF::$shortcode_instances ) ) {
          CSF_Shortcoder::once_editor_setup();
        }

      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      $params = array();
      if ( ! empty( self::$args['taxonomy_options'] ) ) {
        foreach ( self::$args['taxonomy_options'] as $key => $value ) {
          if ( ! empty( self::$args['sections'][$key] ) && ! isset( self::$inited[$key] ) ) {

            $params['args']     = $value;
            $params['sections'] = self::$args['sections'][$key];
            self::$inited[$key] = true;

            CSF_Taxonomy_Options::instance( $key, $params );

          }
        }
      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      if ( ! empty( self::$args['widgets'] ) && class_exists( 'WP_Widget_Factory' ) ) {

        $wp_widget_factory = new WP_Widget_Factory();

        foreach ( self::$args['widgets'] as $key => $value ) {
          if ( ! isset( self::$inited[$key] ) ) {
            self::$inited[$key] = true;
            $wp_widget_factory->register( CSF_Widget::instance( $key, $value ) );
          }
        }

      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      $params = array();
      if ( ! empty( self::$args['comment_metaboxes'] ) ) {
        foreach ( self::$args['comment_metaboxes'] as $key => $value ) {
          if ( ! empty( self::$args['sections'][$key] ) && ! isset( self::$inited[$key] ) ) {

            $params['args']     = $value;
            $params['sections'] = self::$args['sections'][$key];
            self::$inited[$key] = true;

            CSF_Comment_Metabox::instance( $key, $params );

          }
        }
      }

      do_action( 'csf_loaded' );

    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createOptions( $id, $args = array() ) {
      self::$args['options'][$id] = $args;
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createCustomizeOptions( $id, $args = array() ) {
      self::$args['customize_options'][$id] = $args;
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createMetabox( $id, $args = array() ) {
      self::$args['metaboxes'][$id] = $args;
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createShortcoder( $id, $args = array() ) {
      self::$args['shortcoders'][$id] = $args;
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createTaxonomyOptions( $id, $args = array() ) {
      self::$args['taxonomy_options'][$id] = $args;
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createProfileOptions( $id, $args = array() ) {
      self::$args['profile_options'][$id] = $args;
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createWidget( $id, $args = array() ) {
      self::$args['widgets'][$id] = $args;
      self::set_used_fields( $args );
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createCommentMetabox( $id, $args = array() ) {
      self::$args['comment_metaboxes'][$id] = $args;
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function createSection( $id, $sections ) {
      self::$args['sections'][$id][] = $sections;
      self::set_used_fields( $sections );
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function constants() {

      // 更多精品WP资源尽在喵容：miaoroom.com
      $dirname        = wp_normalize_path( dirname( dirname( __FILE__ ) ) );
      $theme_dir      = wp_normalize_path( get_parent_theme_file_path() );
      $plugin_dir     = wp_normalize_path( WP_PLUGIN_DIR );
      $located_plugin = ( preg_match( '#'. self::sanitize_dirname( $plugin_dir ) .'#', self::sanitize_dirname( $dirname ) ) ) ? true : false;
      $directory      = ( $located_plugin ) ? $plugin_dir : $theme_dir;
      $directory_uri  = ( $located_plugin ) ? WP_PLUGIN_URL : get_parent_theme_file_uri();
      $foldername     = str_replace( $directory, '', $dirname );
      $protocol_uri   = ( is_ssl() ) ? 'https' : 'http';
      $directory_uri  = set_url_scheme( $directory_uri, $protocol_uri );

      self::$dir = $dirname;
      self::$url = $directory_uri . $foldername;

    }

    public static function include_plugin_file( $file, $load = true ) {

      $path     = '';
      $file     = ltrim( $file, '/' );
      $override = apply_filters( 'csf_override', 'csf-override' );

      if ( file_exists( get_parent_theme_file_path( $override .'/'. $file ) ) ) {
        $path = get_parent_theme_file_path( $override .'/'. $file );
      } elseif ( file_exists( get_theme_file_path( $override .'/'. $file ) ) ) {
        $path = get_theme_file_path( $override .'/'. $file );
      } elseif ( file_exists( self::$dir .'/'. $override .'/'. $file ) ) {
        $path = self::$dir .'/'. $override .'/'. $file;
      } elseif ( file_exists( self::$dir .'/'. $file ) ) {
        $path = self::$dir .'/'. $file;
      }

      if ( ! empty( $path ) && ! empty( $file ) && $load ) {

        global $wp_query;

        if ( is_object( $wp_query ) && function_exists( 'load_template' ) ) {

          load_template( $path, true );

        } else {

          require_once( $path );

        }

      } else {

        return self::$dir .'/'. $file;

      }

    }

    public static function is_active_plugin( $file = '' ) {
      return in_array( $file, (array) get_option( 'active_plugins', array() ) );
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function sanitize_dirname( $dirname ) {
      return preg_replace( '/[^A-Za-z]/', '', $dirname );
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function include_plugin_url( $file ) {
      return esc_url( self::$url ) .'/'. ltrim( $file, '/' );
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function includes() {

      // 更多精品WP资源尽在喵容：miaoroom.com
      self::include_plugin_file( 'functions/actions.php'    );
      self::include_plugin_file( 'functions/deprecated.php' );
      self::include_plugin_file( 'functions/helpers.php'    );
      self::include_plugin_file( 'functions/sanitize.php'   );
      self::include_plugin_file( 'functions/validate.php'   );

      // 更多精品WP资源尽在喵容：miaoroom.com
      self::include_plugin_file( 'classes/abstract.class.php' );
      self::include_plugin_file( 'classes/fields.class.php'   );
      self::include_plugin_file( 'classes/options.class.php'  );

      // 更多精品WP资源尽在喵容：miaoroom.com
      if ( self::$premium ) {
        self::include_plugin_file( 'classes/customize-options.class.php' );
        self::include_plugin_file( 'classes/metabox.class.php'           );
        self::include_plugin_file( 'classes/profile-options.class.php'   );
        self::include_plugin_file( 'classes/shortcoder.class.php'        );
        self::include_plugin_file( 'classes/taxonomy-options.class.php'  );
        self::include_plugin_file( 'classes/widgets.class.php'           );
        self::include_plugin_file( 'classes/comment-metabox.class.php'   );
      }

    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function maybe_include_field( $type = '' ) {
      if ( ! class_exists( 'CSF_Field_'. $type ) && class_exists( 'CSF_Fields' ) ) {
        self::include_plugin_file( 'fields/'. $type .'/'. $type .'.php' );
      }
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function textdomain() {
      load_textdomain( 'csf', self::$dir .'/languages/'. get_locale() .'.mo' );
    }

    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function set_used_fields( $sections ) {

      if ( ! empty( $sections['fields'] ) ) {

        foreach ( $sections['fields'] as $field ) {

          if ( ! empty( $field['fields'] ) ) {
            self::set_used_fields( $field );
          }

          if ( ! empty( $field['tabs'] ) ) {
            self::set_used_fields( array( 'fields' => $field['tabs'] ) );
          }

          if ( ! empty( $field['accordions'] ) ) {
            self::set_used_fields( array( 'fields' => $field['accordions'] ) );
          }

          if ( ! empty( $field['type'] ) ) {
            self::$fields[$field['type']] = $field;
          }

        }

      }

    }

    //
    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function add_admin_enqueue_scripts() {

      // 更多精品WP资源尽在喵容：miaoroom.com
      $min = ( apply_filters( 'csf_dev_mode', false ) || WP_DEBUG ) ? '' : '.min';

      // 更多精品WP资源尽在喵容：miaoroom.com
      wp_enqueue_media();

      // 更多精品WP资源尽在喵容：miaoroom.com
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_script( 'wp-color-picker' );

      // 更多精品WP资源尽在喵容：miaoroom.com
      if ( apply_filters( 'csf_fa4', false ) ) {
        wp_enqueue_style( 'csf-fa', 'https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome'. $min .'.css', array(), '4.7.0', 'all' );
      } else {
        wp_enqueue_style( 'csf-fa5', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/css/all'. $min .'.css', array(), '5.13.0', 'all' );
        wp_enqueue_style( 'csf-fa5-v4-shims', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/css/v4-shims'. $min .'.css', array(), '5.13.0', 'all' );
      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      wp_enqueue_style( 'csf', CSF::include_plugin_url( 'assets/css/csf'. $min .'.css' ), array(), '1.0.0', 'all' );

      // 更多精品WP资源尽在喵容：miaoroom.com
      if ( is_rtl() ) {
        wp_enqueue_style( 'csf-rtl', CSF::include_plugin_url( 'assets/css/csf-rtl'. $min .'.css' ), array(), '1.0.0', 'all' );
      }

      // 更多精品WP资源尽在喵容：miaoroom.com
      wp_enqueue_script( 'csf-plugins', CSF::include_plugin_url( 'assets/js/csf-plugins'. $min .'.js' ), array(), '1.0.0', true );
      wp_enqueue_script( 'csf', CSF::include_plugin_url( 'assets/js/csf'. $min .'.js' ), array( 'csf-plugins' ), '1.0.0', true );

      wp_localize_script( 'csf', 'csf_vars', array(
        'color_palette'  => apply_filters( 'csf_color_palette', array() ),
        'i18n'           => array(
          // 更多精品WP资源尽在喵容：miaoroom.com
          'confirm'             => esc_html__( 'Are you sure?', 'csf' ),
          'reset_notification'  => esc_html__( 'Restoring options.', 'csf' ),
          'import_notification' => esc_html__( 'Importing options.', 'csf' ),

          // 更多精品WP资源尽在喵容：miaoroom.com
          'typing_text'     => esc_html__( 'Please enter %s or more characters', 'csf' ),
          'searching_text'  => esc_html__( 'Searching...', 'csf' ),
          'no_results_text' => esc_html__( 'No results match', 'csf' ),
        ),
      ) );

      // 更多精品WP资源尽在喵容：miaoroom.com
      $enqueued = array();

      if ( ! empty( self::$fields ) ) {
        foreach ( self::$fields as $field ) {
          if ( ! empty( $field['type'] ) ) {
            $classname = 'CSF_Field_' . $field['type'];
            self::maybe_include_field( $field['type'] );
            if ( class_exists( $classname ) && method_exists( $classname, 'enqueue' ) ) {
              $instance = new $classname( $field );
              if ( method_exists( $classname, 'enqueue' ) ) {
                $instance->enqueue();
              }
              unset( $instance );
            }
          }
        }
      }

      do_action( 'csf_enqueue' );

    }

    //
    // 更多精品WP资源尽在喵容：miaoroom.com
    //
    // 更多精品WP资源尽在喵容：miaoroom.com
    // 更多精品WP资源尽在喵容：miaoroom.com
    //
    public static function add_admin_head_css() {

      global $wp_version;

      $current_branch = implode( '.', array_slice( preg_split( '/[.-]/', $wp_version ), 0, 2 ) );

      if ( version_compare( $current_branch, '5.3', '<' ) ) {

        echo '<style type="text/css">
          .csf-field-slider .csf--unit,
          .csf-field-border .csf--label,
          .csf-field-spacing .csf--label,
          .csf-field-dimensions .csf--label,
          .csf-field-spinner .ui-button-text-only{
            border-color: #ddd;
          }
          .csf-warning-primary{
            box-shadow: 0 1px 0 #bd2130 !important;
          }
          .csf-warning-primary:focus{
            box-shadow: none !important;
          }
        </style>';

      }

    }

    //
    // 更多精品WP资源尽在喵容：miaoroom.com
    public static function field( $field = array(), $value = '', $unique = '', $where = '', $parent = '' ) {

      // 更多精品WP资源尽在喵容：miaoroom.com
      if ( ! empty( $field['_notice'] ) ) {

        $field_type = $field['type'];

        $field            = array();
        $field['content'] = sprintf( esc_html__( 'Ooops! This field type (%s) can not be used here, yet.', 'csf' ), '<strong>'. $field_type .'</strong>' );
        $field['type']    = 'notice';
        $field['style']   = 'danger';

      }

      $depend     = '';
      $hidden     = '';
      $unique     = ( ! empty( $unique ) ) ? $unique : '';
      $class      = ( ! empty( $field['class'] ) ) ? ' ' . esc_attr( $field['class'] ) : '';
      $is_pseudo  = ( ! empty( $field['pseudo'] ) ) ? ' csf-pseudo-field' : '';
      $field_type = ( ! empty( $field['type'] ) ) ? esc_attr( $field['type'] ) : '';

      if ( ! empty( $field['dependency'] ) ) {

        $dependency      = $field['dependency'];
        $hidden          = ' hidden';
        $data_controller = '';
        $data_condition  = '';
        $data_value      = '';
        $data_global     = '';

        if ( is_array( $dependency[0] ) ) {
          $data_controller = implode( '|', array_column( $dependency, 0 ) );
          $data_condition  = implode( '|', array_column( $dependency, 1 ) );
          $data_value      = implode( '|', array_column( $dependency, 2 ) );
          $data_global     = implode( '|', array_column( $dependency, 3 ) );
        } else {
          $data_controller = ( ! empty( $dependency[0] ) ) ? $dependency[0] : '';
          $data_condition  = ( ! empty( $dependency[1] ) ) ? $dependency[1] : '';
          $data_value      = ( ! empty( $dependency[2] ) ) ? $dependency[2] : '';
          $data_global     = ( ! empty( $dependency[3] ) ) ? $dependency[3] : '';
        }

        $depend .= ' data-controller="'. esc_attr( $data_controller ) .'"';
        $depend .= ' data-condition="'. esc_attr( $data_condition ) .'"';
        $depend .= ' data-value="'. esc_attr( $data_value ) .'"';
        $depend .= ( ! empty( $data_global ) ) ? ' data-depend-global="true"' : '';

      }

      if ( ! empty( $field_type ) ) {

        // 更多精品WP资源尽在喵容：miaoroom.com
        echo '<div class="csf-field csf-field-'. $field_type . $is_pseudo . $class . $hidden .'"'. $depend .'>';

        if ( ! empty( $field['fancy_title'] ) ) {
          echo '<div class="csf-fancy-title">' . wp_kses_post( $field['fancy_title'] ) .'</div>';
        }

        if ( ! empty( $field['title'] ) ) {
          echo '<div class="csf-title">';
          echo '<h4>'. wp_kses_post( $field['title'] ) .'</h4>';
          echo ( ! empty( $field['subtitle'] ) ) ? '<div class="csf-text-subtitle">'. wp_kses_post( $field['subtitle'] ) .'</div>' : '';
          echo '</div>';
        }

        echo ( ! empty( $field['title'] ) || ! empty( $field['fancy_title'] ) ) ? '<div class="csf-fieldset">' : '';

        $value = ( ! isset( $value ) && isset( $field['default'] ) ) ? $field['default'] : $value;
        $value = ( isset( $field['value'] ) ) ? $field['value'] : $value;

        self::maybe_include_field( $field_type );

        $classname = 'CSF_Field_'. $field_type;

        if ( class_exists( $classname ) ) {
          $instance = new $classname( $field, $value, $unique, $where, $parent );
          $instance->render();
        } else {
          echo '<p>'. esc_html__( 'This field class is not available!', 'csf' ) .'</p>';
        }

      } else {
        echo '<p>'. esc_html__( 'This type is not found!', 'csf' ) .'</p>';
      }

      echo ( ! empty( $field['title'] ) || ! empty( $field['fancy_title'] ) ) ? '</div>' : '';
      echo '<div class="clear"></div>';
      echo '</div>';

    }

  }

  CSF::init();
}
