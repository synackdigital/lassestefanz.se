<?php

include_once(dirname(__FILE__) . '/defines.php');

class LSPost extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = basename(dirname(__FILE__));
        // $this->shouldSetThumbnail(true);

        parent::__construct($name, $labels, $collection, $args);
    }

    /**
     * Initializes post type fields
     * @return void
     */
    protected function initializeFields() {

        $this->fields = new FieldCollection();

        $names = self::_oEmbedProviderNames();
        $names = implode(", ", $names);

        $this->fields->addField( new URLField(
            array(
                'name' => LS_POST_VIDEO_URL,
                'label' => __( 'Video URL', 'ls-plugin' ),
                'description' => __('Will be used in place of featured image if speciefied. Valid sources are:', 'ls-plugin'),
                'editing_info' => $names,
            )
        ));

    }

    protected static function _oEmbedProviders() {
        $oe = new WP_oEmbed();

        return $oe->providers;
    }

    protected static function _oEmbedProviderName($p)
    {
        if (is_array($p) && array_key_exists(0, $p)) {

            $url_data = parse_url($p[0]);
            $host = $url_data['host'];

            $host = preg_replace('/^([^\.]*\.([^\.]+\.[^\.]+))$/i', '$2', $host);

            return $host;
        }

        return null;
    }

    protected static function _oEmbedProviderNames() {
        $providers = array_values(self::_oEmbedProviders());
        $names = array_map(array(__CLASS__, '_oEmbedProviderName'), $providers);

        return array_unique($names);
    }


    public static function videoURL($id = null)
    {
        return self::optionForKey(LS_POST_VIDEO_URL, $id);
    }

    public function hasVideo($id = null)
    {
        $u = self::videoURL($id);
        return !empty($u);
    }

    public static function video($id = null, $args = null)
    {
        $args = wp_parse_args( $args, array(
            'width' => null,
            'height' => null,
            'echo' => true,
        ) );
        extract($args);

        $url = self::videoURL($id);

        $oe = new WP_oEmbed();
        $markup = $oe->get_html($url);

        // $markup = apply_filters( 'the_content', $url );

        if ($echo)
            echo $markup;

        return $markup;
    }
}

include_once(ABSPATH . WPINC . '/class-oembed.php');

$lspost = LSPost::type('post', array('singular' => __('Post'), 'plural' => __('Posts')));


function ls_has_video($id = null) {
    return LSPost::hasVideo($id);
}

function ls_video_url($id = null) {
    return LSPost::videoURL($id);
}

function ls_video($id = null, $args = null) {
    return LSPost::video($id, $args);
}
