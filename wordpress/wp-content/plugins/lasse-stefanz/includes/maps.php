<?php


class LSEventMaps {

    public function __construct()
    {
        add_shortcode('ls_events_map', array(__CLASS__, 'events_map_shortcode'));
    }


    public static function events_map_shortcode($atts) {
        global $post;

        if( !empty($atts['event_venue']) )
            $atts['venue'] = $atts['event_venue'];

        //If venue is not set get from the venue being quiered or the post being viewed
        if( empty($atts['venue']) ){
            if( eo_is_venue() ){
                $atts['venue']= esc_attr(get_query_var('term'));
            }else{
                $atts['venue'] = eo_get_venue_slug(get_the_ID());
            }
        }

        $venue_slugs = explode(',',$atts['venue']);

        $args = shortcode_atts( array(
            'zoom' => 15, 'scrollwheel'=>'true','zoomcontrol'=>'true',
            'rotatecontrol'=>'true','pancontrol'=>'true','overviewmapcontrol'=>'true',
            'streetviewcontrol'=>'true','maptypecontrol'=>'true','draggable'=>'true',
            'maptypeid' => 'ROADMAP',
            'width' => '100%','height' => '200px','class' => '',
            'tooltip'=>'false'
            ), $atts );

        //Cast options as boolean:
        $bool_options = array('tooltip','scrollwheel','zoomcontrol','rotatecontrol','pancontrol','overviewmapcontrol','streetviewcontrol','draggable','maptypecontrol');
        foreach( $bool_options as $option  ){
            $args[$option] = ( $args[$option] == 'false' ? false : true );
        }

        return self::events_map($venue_slugs, $args);
    }


    /**
     * Returns the mark-up for a Google map of the venue (and enqueues scripts).
     * Accepts an arguments array corresponding to the attributes supported by the shortcode.
     * @since 1.6
     *
     * @link http://www.stephenharris.info/2012/event-organiser-1-6-whats-new/ Examples of using eo_get_venue_map()
     *
    * @param mixed $venue_slug_or_id The venue ID as an integer. Or Slug as string. Uses venue of current event if empty.
    * @return string The markup of the map. False is no venue found.
     */
    public static function events_map($venue_slug_or_id='', $args=array()){

            //Cast as array to allow multi venue support
            if( $venue_slug_or_id == '%all%' || is_array($venue_slug_or_id) && in_array('%all%',$venue_slug_or_id) ){
                $all_venues = eo_get_venues();
                if( $all_venues )
                    $venue_slug_or_id = array_map('intval',wp_list_pluck($all_venues,'term_id'));

            }
            if( !is_array($venue_slug_or_id) )
                $venue_slug_or_id = array( $venue_slug_or_id );

            $venue_ids = array_map('eo_get_venue_id_by_slugorid',$venue_slug_or_id);

            //Map properties
            $args = shortcode_atts( array(
                'zoom' => 15, 'scrollwheel'=>true, 'zoomcontrol'=>true, 'rotatecontrol'=>true,
                'pancontrol'=>true, 'overviewmapcontrol'=>true, 'streetviewcontrol'=>true,
                'maptypecontrol'=>true, 'draggable'=>true,'maptypeid' => 'ROADMAP',
                'width' => '100%','height' => '200px','class' => '',
                'tooltip'=>false
                ), $args );

            //Cast zoom as integer
            $args['zoom'] = (int) $args['zoom'];

            //Escape attributes
            $width = esc_attr($args['width']);
            $height = esc_attr($args['height']);
            $class = esc_attr($args['class']);

            $args['maptypeid'] = strtoupper($args['maptypeid']);

             //If class is selected use that style, otherwise use specified height and width
            if( !empty($class) ){
                $class .= " eo-venue-map googlemap";
                $style = "";
            }else{
                $class = "eo-venue-map googlemap";
                $style = "style='height:".$height.";width:".$width.";' ";
            }

            $venue_ids = array_filter($venue_ids);

            if( empty($venue_ids) )
                return false;

            //Set up venue locations for map
            foreach( $venue_ids as $venue_id ){

                //Venue lat/lng array
                $latlng = eo_get_venue_latlng($venue_id);

                //Venue tooltip description
                $tooltip_content = '<strong>'.eo_get_venue_name($venue_id).'</strong>';
                $address = array_filter(eo_get_venue_address($venue_id));
                if( !empty($address) )
                    $tooltip_content .='</br>'.implode(', ',$address);

                $tooltip_content = apply_filters('eventorganiser_venue_tooltip',$tooltip_content,$venue_id);

                $locations[] =array('lat'=>$latlng['lat'],'lng'=>$latlng['lng'], 'tooltipContent'=>$tooltip_content);
            }

            //This could be improved
            EventOrganiser_Shortcodes::$map[] = array_merge($args, array('locations'=>$locations) );
            EventOrganiser_Shortcodes::$add_script = true;
            $id = count(EventOrganiser_Shortcodes::$map);

            return  "<div class='".$class."' id='eo_venue_map-{$id}' ".$style."></div>";
    }
}

