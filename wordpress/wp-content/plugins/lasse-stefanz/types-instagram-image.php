<?php

include_once(dirname(__FILE__) . '/defines.php');

class LSInstagramImage extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = basename(dirname(__FILE__));
        $this->shouldSetThumbnail(true);
        $this->setRewriteSlug(apply_filters('ls_rewrite_slug_for_type', __('images', 'lasse-stefanz'), $name));

        add_filter( "manage_{$name}_posts_columns", array(&$this, 'manage_posts_columns') );
        add_action( "manage_{$name}_posts_custom_column", array(&$this, 'manage_posts_custom_column'), 10, 2 );

        add_action( 'admin_init', array(&$this, 'admin_init') );

        // Filters
        add_filter('the_content', array(&$this, 'the_content'));

        // Customize admin screen
        add_action("wp_ajax_{$name}_publish", array(&$this, 'ajax_change_status'));
        add_action("wp_ajax_{$name}_trash", array(&$this, 'ajax_change_status'));

        add_filter("views_edit-{$name}", array(&$this, 'admin_views'));
        add_filter('admin_body_class', array(&$this, 'admin_body_class'));

        parent::__construct($name, $labels, $collection, $args);
    }

    /**
     * Initializes post type fields
     * @return void
     */
    protected function initializeFields() {

        $this->fields = new FieldCollection();

        $this->fields->addField( new CustomField(
            array(
                'name' => LS_IGIM_ID,
                'label' => __( 'Image ID', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

        $this->fields->addField( new URLField(
            array(
                'name' => LS_IGIM_URL,
                'label' => __( 'Image URL', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LS_IGIM_LOCATION,
                'label' => __( 'Location', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LS_IGIM_SIZE_THUMBNAIL,
                'label' => __( 'Thumbnail', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LS_IGIM_SIZE_LOW,
                'label' => __( 'Low Resolution', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LS_IGIM_SIZE_STANDARD,
                'label' => __( 'Standard Resolution', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LS_IGIM_USERNAME,
                'label' => __( 'Username', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LS_IGIM_FULL_NAME,
                'label' => __( 'Full name', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LS_IGIM_USER_ID,
                'label' => __( 'User ID', 'lasse-stefanz' ),
                'readonly' => true,
            )
        ));

    }

    /**
     * Returns a list arguments user for taxonomy creation
     * @return array Arguments array
     */
    protected function keywordTaxonomies() {

        return array(
            array(
                'single' => __( 'Tag', 'lasse-stefanz' ),
                'multiple' => __( 'Tags', 'lasse-stefanz' ),
                'slug' => LS_IGIM_TAG,
                'types' => array( $this->name )
            )
        );
    }

    /**
     * Default post type arguments
     * @return array Post type arguments
     */
    public function defaultArgs() {
        $args = parent::defaultArgs();

        $args['supports'] = array(
            'title',
        );
        $args['menu_position'] = 10;

        return $args;
    }


    /**
     * Query arguments for post type
     * @param  array $args Additional arguments
     * @return array       Array of arguments
     */
    public static function queryArgs($args = null) {
        $defaults = parent::queryArgs();
        $type_args = array(
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => 3,

        );

        return array_merge($defaults, $type_args, (array)$args);
    }


    public function admin_init()
    {
        wp_enqueue_script( 'ls.instagram', plugins_url( 'admin/js/instagram.js', __FILE__ ), array('jquery'), false, true );
    }

    public function manage_posts_columns($defaults)
    {
        $featured_col = array_slice($defaults, 0, 1);
        array_shift($defaults);

        $featured_col['instagram_image'] = __('Image', 'lasse-stefanz');
        $featured_col['instagram_actions'] = __('Actions', 'lasse-stefanz');

        $defaults = array_merge($featured_col, $defaults);

        return $defaults;
    }


    public function manage_posts_custom_column($column_name, $post_id)
    {
        if ($column_name == 'instagram_image') {

            $attrs = array(
                'data-url' => self::getImageURL($post_id, 'low_resolution'),
                'class' => 'instagram-image',
            );

            echo self::getImageMarkup($post_id, 'thumbnail', array('width' => 64, 'height' => 64, 'attributes' => $attrs));
        }

        if ($column_name == 'instagram_actions') {

            echo '<ul class="buttons">';
            printf('<li><a href="#" class="instagram-action instagram-publish button button-primary" data-id="%d" data-action="%s_publish">%s</a></li>', $post_id, $this->name, __('Publish', 'lasse-stefanz'));
            printf('<li><a href="#" class="instagram-action instagram-trash button" data-id="%d" data-action="%s_trash">%s</a></li>', $post_id, $this->name, __('Remove', 'lasse-stefanz'));
            echo '</ul>';
        }
    }


    public static function getImageData($post_id, $size)
    {
        $data = null;

        switch ($size) {
            case LS_IGIM_SIZE_THUMBNAIL:
            case 'thumbnail':
                $data = get_post_meta( $post_id, LS_IGIM_SIZE_THUMBNAIL, true );
                break;
            case LS_IGIM_SIZE_LOW:
            case 'low_resolution':
                $data = get_post_meta( $post_id, LS_IGIM_SIZE_LOW, true );
                break;
            case LS_IGIM_SIZE_STANDARD:
            case 'standard_resolution':
                $data = get_post_meta( $post_id, LS_IGIM_SIZE_STANDARD, true );
                break;
        }

        if ($data) {
            $data = unserialize($data);
        }

        return $data;
    }


    public static function getImageMarkup($post_id, $size, $args = null)
    {
        $data = self::getImageData($post_id, $size);

        if ($data && array_key_exists('url', $data)) {
            $url = $width = $height = $attributes = null;
            extract($data);
            $alt = get_the_title($post_id);

            extract((array)$args);

            $attrs = '';
            if ($attributes) {
                foreach ($attributes as $key => $value) {
                    $attrs .= sprintf('%s="%s" ', esc_attr($key), esc_attr($value));
                }
            }

            return sprintf(
                '<img src="%s" width="%d" height="%d" alt="%s" %s>',
                esc_attr( $url ),
                esc_attr( $width ),
                esc_attr( $height ),
                esc_attr( $alt ),
                $attrs
            );
        }

        return null;
    }

    public static function getImageURL($post_id, $size)
    {
        $data = self::getImageData($post_id, $size);

        if ($data && array_key_exists('url', $data)) {
            return $data['url'];
        }

        return null;
    }


    public function admin_body_class($class)
    {
        global $pagenow, $typenow;

        if ($pagenow == 'edit.php' && $typenow == $this->name) {
            $class .= " edit-{$this->name}";
        }

        return $class;
    }

    public function admin_views($views)
    {

        global $wp_post_statuses;

        if (array_key_exists('trash', $views)) {

            $label = trim(strip_tags(__($wp_post_statuses['trash']->label_count[0])), " (%s)");
            $views['trash'] = str_replace($label, __('Hidden', 'lasse-stefanz'), $views['trash']);
        }

        return $views;
    }


    public function the_content($content)
    {
        global $post;

        if ($post->post_type == $this->name) {

            $size = apply_filters( 'ls_instagram_content_image_size', LS_IGIM_SIZE_STANDARD );

            return $this->getImageMarkup($post->ID, $size);
        }

        return $content;
    }


    /* AJAX Callbacks */
    public function ajax_change_status()
    {
        header("Content-Type: application/json; charset=utf8");

        $modified = false;

        if (is_admin()) {
            $id = $_POST['id'];
            $action = $_POST['action'];

            $post = get_post($id);
            switch ($action) {
                case "{$this->name}_publish":
                    $post->post_status = 'publish';
                    $modified = true;
                    break;
                case "{$this->name}_trash":
                    $post->post_status = 'trash';
                    $modified = true;
                    break;
            }

            wp_update_post( $post );
        }

        echo json_encode(array('result' => $modified));

        die();
    }

}

$instagram_image = LSInstagramImage::type('igimage', array('singular' => __('Fan photo', 'lasse-stefanz'), 'plural' => __('Fan photos', 'lasse-stefanz')));

