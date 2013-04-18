<?php
if (is_single()) {
    get_template_part( 'content', 'album-single' );
} else {
    get_template_part( 'content', 'album-archive' );
}
?>
