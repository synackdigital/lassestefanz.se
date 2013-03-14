<?php

class LSInstagramDownloader {

    protected $api;
    protected $tags;
    protected $images;

    public function __construct($tags = null)
    {
        if (class_exists('WPInstagram')) {
            $this->api = WPInstagram::get_api();
        }

        if ($tags) {
            $this->setTags($tags);
        }
    }

    /**
     * Getter for tags
     * @return array Array of tag names
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Setter for tags
     * @param array $tags Array of tag names
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Downloads images using the Instagram API
     * @return [type] [description]
     */
    public function downloadImagesFromTags()
    {
        $this->images = array();

        if ($this->api) {
            foreach ($this->tags as $tag) {
                $result = $this->api->recentPhotosForTag($tag);

                if ($result && property_exists($result, 'data')) {
                    $this->images = array_merge($this->images, $result->data);
                }
            }
        }

        return $this->images;
    }


    protected static function postmetaForImage($image)
    {
        $id = $image->id;

        $location = $image->location;
        $link = $image->link;

        $sizes = $image->images;

        // thumbnail
        // low_resolution
        // standard_resolution

        $username = $image->user->username;
        $full_name = $image->user->full_name;
        $user_id = $image->user->id;

        return array(
            LS_IGIM_ID => $id,
            LS_IGIM_URL => $link,
            LS_IGIM_LOCATION => $location,
            LS_IGIM_SIZE_THUMBNAIL => serialize(get_object_vars($sizes->thumbnail)),
            LS_IGIM_SIZE_LOW => serialize(get_object_vars($sizes->low_resolution)),
            LS_IGIM_SIZE_STANDARD => serialize(get_object_vars($sizes->standard_resolution)),
            LS_IGIM_USERNAME => $username,
            LS_IGIM_FULL_NAME => $full_name,
            LS_IGIM_USER_ID => $user_id,
        );
    }

    public static function storeImage($image)
    {
        $meta_data = self::postmetaForImage($image);

        $id = $image->id;
        $caption = $image->caption->text;
        $created_time = $image->created_time;
        $guid = sprintf('%s%s', trailingslashit( WP_CONTENT_URL ), 'instagram/image/' . $id );

        $tags = $image->tags;

        global $wpdb;
        $post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid = %s", $guid ), ARRAY_A );

        if (!$post) {
            $post = array();
        }

        $post['post_content'] = self::encodeImageData($image);
        $post['post_date'] = date_i18n('Y-m-d H:i:s', $created_time, false);
        $post['post_date_gmt'] = date_i18n('Y-m-d H:i:s', $created_time, true);
        $post['post_modified'] = date_i18n('Y-m-d H:i:s', false, false);
        $post['post_modified_gmt'] = date_i18n('Y-m-d H:i:s', false, true);
        $post['post_title'] = $caption;
        $post['comment_status'] = 'closed';
        $post['ping_status'] = 'closed';
        $post['post_type'] = LSInstagramImage::instance()->typeName();
        $post['guid'] = $guid;

        if (!array_key_exists('post_author', $post)) {
            $post['post_author'] = LasseStefanz::fan_photo_owner();
        }

        if (!array_key_exists('post_status', $post)) {
            $post['post_status'] = 'pending';
        }

        if (!array_key_exists('post_name', $post)) {
            $post['post_name'] = sanitize_title( $caption, 'instagram_' . $id );
        }

        $func = array_key_exists('ID', $post) ? 'wp_update_post' : 'wp_insert_post';
        $post_id = $func( $post );

        if ($post_id) {
            // Update tags tags
            wp_set_post_terms( $post_id, $tags, LS_IGIM_TAG, $append = false );

            // Update post meta
            foreach ($meta_data as $key => $value) {
                update_post_meta( $post_id, $key, $value );
            }
        }
    }

    public static function encodeImageData($image)
    {
        return gzencode(serialize($image));
    }

    public static function decodeImageData($data)
    {
        return unserialize(gzdecode($image));
    }

    public function syncImages()
    {
        // TODO: Add pagination up to given point in time (or max recursion depth)

        $this->downloadImagesFromTags();

        wp_defer_term_counting( true );

        foreach ($this->images as $image) {
            self::storeImage($image);
        }

        wp_defer_term_counting( false );
    }
}



