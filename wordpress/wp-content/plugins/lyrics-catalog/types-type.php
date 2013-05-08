<?php

include_once(dirname(__FILE__) . '/defines.php');

class LCType extends HWPType {



    /**
     * Initializes post type fields
     * @return void
     */
    protected function initializeFields() {

        if (!$this->fields)
            $this->fields = new FieldCollection();

        $this->fields->addField( new URLField(
            array(
                'name' => LC_SOUNDCLOUD_URL,
                'label' => __( 'Soundcloud URL', 'lyrics-catalog' ),
            )
        ));

    }

    public static function soundcloudURL($id = null)
    {
        return self::optionForKey(LC_SOUNDCLOUD_URL, $id);
    }

    public static function soundcloudPlayer($id = null, $args = null)
    {
        $args = wp_parse_args( $args, array(
            'echo' => false,
            'class' => 'soundcloud-player',
        ) );
        extract($args);

        $url = self::soundcloudURL();

        if (!$url)
            return null;

        $markup = sprintf('<div class="%s" data-url="%s"></div>', esc_attr(implode(" ", (array)$class)), esc_attr($url));

        if ($echo)
            echo $markup;

        return $markup;
        // http://soundcloud.com/oembed?format=js&callback=sc_data&maxwidth=100%25&maxheight=81&color=ff0000&url=https://soundcloud.com/lassestefanz/2011-cuba-libre-smakprov
    }

}

function lc_soundcloud_player($id = null, $args = null) {
    return LCType::soundcloudPlayer();
}
