<?php
/**
 * Dynamic blocks — Rabe Dago Tour
 */


// ============================================================
// rdt/tour-meta — Tour card meta (stars, duration, location, title, price, book now)
// Used inside the Query Loop block on the tours archive page.
// ============================================================
function rdt_register_dynamic_blocks() {
    register_block_type( 'rdt/tour-meta', array(
        'render_callback' => 'rdt_render_tour_meta',
        'uses_context'    => array( 'postId', 'postType' ),
    ) );
}
add_action( 'init', 'rdt_register_dynamic_blocks' );


function rdt_render_tour_meta( $attributes, $content, $block ) {
    $post_id = isset( $block->context['postId'] ) ? intval( $block->context['postId'] ) : get_the_ID();
    if ( ! $post_id ) {
        return '';
    }

    $duration = get_post_meta( $post_id, 'tour_duration', true );
    $price    = get_post_meta( $post_id, 'tour_price',    true );
    $location = get_post_meta( $post_id, 'tour_location', true );
    $rating   = (int) ( get_post_meta( $post_id, 'tour_rating', true ) ?: 5 );
    $rating   = max( 1, min( 5, $rating ) );

    $stars_filled = str_repeat( '★', $rating );
    $stars_empty  = str_repeat( '☆', 5 - $rating );
    $title        = get_the_title( $post_id );
    $url          = get_permalink( $post_id );

    // SVG: calendar icon
    $icon_calendar = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';

    // SVG: location pin icon
    $icon_pin = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>';

    ob_start();
    ?>
    <div class="rdt-tour-meta">

        <div class="rdt-tour-meta__top">
            <span class="rdt-tour-meta__stars" aria-label="<?php echo esc_attr( sprintf( __( '%d out of 5 stars', 'blockskit-travel' ), $rating ) ); ?>">
                <span class="rdt-tour-meta__stars-filled"><?php echo esc_html( $stars_filled ); ?></span><span class="rdt-tour-meta__stars-empty"><?php echo esc_html( $stars_empty ); ?></span>
            </span>
            <?php if ( $duration ) : ?>
            <span class="rdt-tour-meta__duration">
                <?php echo $icon_calendar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG constant ?>
                <?php echo esc_html( $duration ); ?>
            </span>
            <?php endif; ?>
        </div>

        <?php if ( $location ) : ?>
        <div class="rdt-tour-meta__location">
            <?php echo $icon_pin; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG constant ?>
            <?php echo esc_html( $location ); ?>
        </div>
        <?php endif; ?>

        <a class="rdt-tour-meta__title" href="<?php echo esc_url( $url ); ?>">
            <?php echo esc_html( $title ); ?>
        </a>

        <div class="rdt-tour-meta__footer">
            <?php if ( $price ) : ?>
            <span class="rdt-tour-meta__price"><?php echo esc_html( $price ); ?></span>
            <?php endif; ?>
            <a class="rdt-tour-meta__book-now" href="<?php echo esc_url( $url ); ?>">
                <?php esc_html_e( 'BOOK NOW', 'blockskit-travel' ); ?> &#8594;
            </a>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
