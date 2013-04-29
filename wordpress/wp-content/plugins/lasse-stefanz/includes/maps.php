<?php


class LSEventMaps {

    public function __construct()
    {
        if (function_exists('eo_get_events') && function_exists('eo_get_venue')) {
            add_shortcode('ls_events_map', array(__CLASS__, 'events_map_shortcode'));
        }
    }


    public static function events_map_shortcode($atts) {
        global $post;

        $args = shortcode_atts( array(
            'zoom' => 15,
            'scrollwheel' => 'true',
            'zoomcontrol' => 'true',
            'rotatecontrol' => 'true',
            'pancontrol' => 'true',
            'overviewmapcontrol' => 'true',
            'streetviewcontrol' => 'true',
            'maptypecontrol' => 'true',
            'draggable' => 'true',
            'maptypeid' => 'ROADMAP',
            'width' => '100%',
            'height' => '620px',
            'class' => '',
            'tooltip' => 'false'
        ), $atts );

        //Cast options as boolean:
        $bool_options = array('tooltip', 'scrollwheel', 'zoomcontrol', 'rotatecontrol', 'pancontrol', 'overviewmapcontrol', 'streetviewcontrol', 'draggable', 'maptypecontrol');
        foreach( $bool_options as $option  ){
            $args[$option] = ( $args[$option] == 'false' ? false : true );
        }

        $events = eo_get_events();

        return self::events_map($events, $args);
    }


    /**
     * Returns the mark-up for a Google map of the venue (and enqueues scripts).
     * Accepts an arguments array corresponding to the attributes supported by the shortcode.
     * @since 1.6
     *
     * @link http://www.stephenharris.info/2012/event-organiser-1-6-whats-new/ Examples of using eo_get_venue_map()
     *
     * @return string The markup of the map. False is no venue found.
     */
    public static function events_map($events=array(), $args=array()){

        //Map properties
        $args = shortcode_atts( array(
            'zoom' => 15,
            'scrollwheel' => true,
            'zoomcontrol' => true,
            'rotatecontrol' => true,
            'pancontrol' => true,
            'overviewmapcontrol' => true,
            'streetviewcontrol' => true,
            'maptypecontrol' => true,
            'draggable' => true,
            'maptypeid' => 'ROADMAP',
            'width' => '100%',
            'height' => '400px',
            'class' => '',
            'tooltip' => false
        ), $args );

        //Cast zoom as integer
        $args['zoom'] = (int) $args['zoom'];

        //Escape attributes
        $width = esc_attr($args['width']);
        $height = esc_attr($args['height']);
        $class = esc_attr($args['class']);

        $args['maptypeid'] = strtoupper($args['maptypeid']);

         //If class is selected use that style, otherwise use specified height and width
        if ( !empty($class) ) {
            $class .= " eo-venue-map googlemap";
            $style = "";
        } else {
            $class = "eo-venue-map googlemap";
            $style = "style='height:" . $height . ";width:" . $width . ";' ";
        }

        if( empty($events) )
            return false;

        $locations = array();

        //Set up venue locations for map
        foreach( $events as $event ){
            $venue_id = eo_get_venue($event->ID);

            if ($venue_id) {

                //Venue lat/lng array
                $latlng = array_filter(eo_get_venue_latlng($venue_id));

                if (count($latlng)) {

                    $event_title = apply_filters('the_title', $event->post_title);
                    $time = eo_get_the_start('Y-m-d H:i', $event->ID, null, $event->occurrence_id);
                    $venue_name = eo_get_venue_name($venue_id);;

                    $tooltip_content = sprintf('<p><strong>%s</strong><br/>%s</p>', $event_title, $time);
                    // $tooltip_content = apply_filters('eventorganiser_venue_tooltip', $tooltip_content, $venue_id);

                    if (array_key_exists($venue_id, $locations)) {
                        $locations[$venue_id]['tooltipContent'] .=  $tooltip_content;
                    } else {
                        $locations[$venue_id] = array('lat' => $latlng['lat'],'lng' => $latlng['lng'], 'tooltipContent' => sprintf('<h3>%s</h3>', $venue_name) . $tooltip_content);
                    }
                }
            }
        }

        $locations = array_values($locations);

        if (count($locations) && class_exists('EventOrganiser_Shortcodes')) {
            //This could be improved
            EventOrganiser_Shortcodes::$map[] = array_merge($args, array('locations' => $locations) );
            EventOrganiser_Shortcodes::$add_script = true;
            $id = count(EventOrganiser_Shortcodes::$map);

            return  "<div class='" . $class . "' id='eo_venue_map-{$id}' " . $style . "></div>";

        }
    }
}

