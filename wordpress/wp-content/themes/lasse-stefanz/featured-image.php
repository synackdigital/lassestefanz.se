<?php if (has_post_thumbnail()) : ?>
<div class="featured-image">
    <?php
        the_post_thumbnail( LS_CAMPAIGN_IMAGE_SIZE );
    ?>
</div>
<?php endif; ?>
