<?php /* Template Name: CustomPageT1 */ ?>

<?php get_header(); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

            <div class="tp-error">
                <h2>Hubo un error al realizar la compra</h2>
                <p><?php do_action('error_message'); ?> </p>
            </div>

        </main><!-- .site-main -->

        <?php get_sidebar( 'content-bottom' ); ?>

    </div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
