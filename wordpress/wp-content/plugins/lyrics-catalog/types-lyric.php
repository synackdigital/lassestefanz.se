<?php

include_once(dirname(__FILE__) . '/defines.php');

class LCLyric extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = 'lyrics-catalog';
        $this->shouldSetThumbnail(false);
        $this->setRewriteSlug(__('lyric', 'lyrics-catalog'));

        parent::__construct($name, $labels, $collection, $args);
    }

    protected function initializeFields() {

        $this->fields = new FieldCollection();

        $this->fields->addField( new CustomField(
            array(
                'name' => LC_LYRIC_AUTHOR,
                'label' => __( 'Author', 'lyrics-catalog' ),
            )
        ));

        /*
        $this->fields->addField( new URLField(
            array(
                'name' => SE_PROJECT_EXTERNAL_URL,
                'label' => __( 'External URL', 'lyrics-catalog' ),
            )
        ));

        $this->fields->addField( new URLField(
            array(
                'name' => SE_PROJECT_EXTERNAL_URL_TITLE,
                'label' => __( 'External URL title', 'lyrics-catalog' ),
                'description' => sprintf(__('Will be used as link title for the above URL. Leave empty to use the default title, "%s".', 'lyrics-catalog'), __('External link', 'lyrics-catalog'))
            )
        ));
        */
    }

    public function defaultArgs() {
        $args = parent::defaultArgs();

        $args['supports'] = array(
            'title',
            'editor',
            'revisions',
            'page-attributes',
        );


        return $args;
    }

    public static function queryArgs($args = null) {
        $defaults = parent::queryArgs();
        $type_args = array(
            'orderby' => 'menu_order',
            'order' => 'ASC',
        );

        return array_merge($defaults, $type_args, (array)$args);
    }


}


$lyric = LCLyric::type('lyric', array('singular' => __('Lyric', 'lyrics-catalog'), 'plural' => __('Lyrics', 'lyrics-catalog')));
