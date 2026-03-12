<?php
/**
 * Title: Tour Archive
 * Slug: blockskit-travel/template-archive-tour
 * Categories: template
 * Keywords: tours packages archive
 * Inserter: false
 */
$blockskit_banner = BLOCKSKIT_TRAVEL_URL . 'assets/images/inner-banner-img1.jpg';
?>
<!-- wp:cover {"url":"<?php echo esc_url( $blockskit_banner ); ?>","dimRatio":80,"overlayColor":"foreground","focalPoint":{"x":0.5,"y":0},"minHeight":480,"align":"full","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|large"},"padding":{"top":"var:preset|spacing|large","bottom":"var:preset|spacing|large"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull" style="margin-bottom:var(--wp--preset--spacing--large);padding-top:var(--wp--preset--spacing--large);padding-bottom:var(--wp--preset--spacing--large);min-height:480px"><span aria-hidden="true" class="wp-block-cover__background has-foreground-background-color has-background-dim-80 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( $blockskit_banner ); ?>" style="object-position:50% 0%" data-object-fit="cover" data-object-position="50% 0%"/><div class="wp-block-cover__inner-container"><!-- wp:columns {"className":"is-not-stacked-on-mobile"} -->
<div class="wp-block-columns is-not-stacked-on-mobile"><!-- wp:column {"width":"18%"} -->
<div class="wp-block-column" style="flex-basis:18%"></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.666%"} -->
<div class="wp-block-column" style="flex-basis:66.666%"><!-- wp:heading {"textAlign":"center","style":{"color":{"text":"#ffffff"},"typography":{"fontSize":"42px","fontWeight":"700","fontStyle":"normal"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#ffffff;font-size:42px;font-style:normal;font-weight:700"><?php esc_html_e( 'All Tour &amp; Travel Packages', 'blockskit-travel' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#e5e7eb"}}} -->
<p class="has-text-align-center" style="color:#e5e7eb"><?php esc_html_e( 'Discover all our Madagascar circuits and choose the adventure that suits you.', 'blockskit-travel' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"18%"} -->
<div class="wp-block-column" style="flex-basis:18%"></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></div>
<!-- /wp:cover -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"60px","bottom":"80px","left":"var:preset|spacing|x-small","right":"var:preset|spacing|x-small"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:60px;padding-bottom:80px;padding-right:var(--wp--preset--spacing--x-small);padding-left:var(--wp--preset--spacing--x-small)"><!-- wp:query {"queryId":20,"query":{"perPage":12,"pages":0,"offset":0,"postType":"tour","order":"asc","orderBy":"title","author":"","search":"","exclude":[],"sticky":"","inherit":true},"align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|medium"}},"layout":{"type":"grid","minimumColumnWidth":"20rem"}} -->
<!-- wp:group {"style":{"border":{"width":"1px","color":"#ededed","radius":"20px"},"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"backgroundColor":"pure-white","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-border-color has-pure-white-background-color has-background" style="border-color:#ededed;border-width:1px;border-radius:20px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","style":{"border":{"radius":"20px 20px 0 0"}}} /-->
<!-- wp:rdt/tour-meta /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center","style":{"spacing":{"padding":{"top":"40px","bottom":"40px"}}}} -->
<p class="has-text-align-center" style="padding-top:40px;padding-bottom:40px"><?php esc_html_e( 'No tour packages available at the moment. Please check back soon!', 'blockskit-travel' ); ?></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->

<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|large"}}}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
