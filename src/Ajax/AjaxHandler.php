<?php
namespace Tooltipy\Ajax;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;
use Tooltipy\Keyword\KeywordRepository;

/**
 * Handles AJAX loading of tooltip content blocks.
 * Replaces advanced/load-ajax.php.
 */
class AjaxHandler {

    private Plugin            $plugin;
    private KeywordRepository $repo;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
        $this->repo   = new KeywordRepository( $plugin );
    }

    public function init(): void {
        add_action( 'wp_ajax_tltpy_load_keywords',        [ $this, 'load_keywords' ] );
        add_action( 'wp_ajax_nopriv_tltpy_load_keywords', [ $this, 'load_keywords' ] );
        add_action( 'wp_footer', [ $this, 'output_tooltip_container' ] );
        add_action( 'wp_footer', [ $this, 'output_ajax_script' ] );
    }

    /**
     * Output the hidden div that serves as a container for all tooltip blocks.
     * This must be present in the DOM before the JS runs.
     */
    public function output_tooltip_container(): void {
        if ( is_admin() ) {
            return;
        }
        echo '<div id="tooltip_blocks_to_show" style="display:none;"></div>' . "\n";
    }

    /**
     * AJAX action: returns HTML for the tooltip popup blocks.
     */
    public function load_keywords(): void {
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'tltpy_load_keywords_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        if ( empty( $_POST['keyword_ids'] ) ) {
            die();
        }

        $keyword_ids = [];
        foreach ( (array) $_POST['keyword_ids'] as $id ) {
            $keyword_ids[] = (int) sanitize_key( $id );
        }
        $keyword_ids = array_unique( array_filter( $keyword_ids ) );

        $query        = $this->repo->query_by_ids( $keyword_ids );
        $settings     = get_option( 'bluet_kw_settings', [] );
        $style_opt    = get_option( 'bluet_kw_style', [] );
        $glossary_opt = get_option( 'bluet_glossary_options', [] );
        $hide_title   = ! empty( $settings['bt_kw_hide_title'] ) && $settings['bt_kw_hide_title'] === 'on';
        $cat_name     = $this->plugin->get_cat_name();
        $extra_cls    = $style_opt['bt_kw_add_css_classes']['popup'] ?? '';

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $kw_id      = get_the_id();
                $youtube    = (string) get_post_meta( $kw_id, 'bluet_youtube_video_id', true );
                $yt_class   = strlen( $youtube ) > 5 ? 'tooltipy-pop-youtube' : '';

                $families_arr = wp_get_post_terms( $kw_id, $cat_name, [ 'fields' => 'ids' ] );
                $families_cls = '';
                if ( is_array( $families_arr ) ) {
                    $families_cls = implode( ' ', array_map( fn( $fid ) => 'tooltipy-pop-cat-' . $fid, $families_arr ) );
                }

                ?>
                <span class="bluet_block_to_show tooltipy-pop tooltipy-pop-<?php echo esc_attr( $kw_id . ' ' . $families_cls . ' ' . $yt_class . ' ' . $extra_cls ); ?>" data-tooltip="<?php echo esc_attr( (string) $kw_id ); ?>">
                    <div class="bluet_hide_tooltip_button">&times;</div>
                    <div class="bluet_block_container">
                        <?php if ( $youtube === '' ) : ?>
                            <div class="bluet_img_in_tooltip"><?php echo get_the_post_thumbnail( $kw_id, 'medium' ); ?></div>
                        <?php else : ?>
                            <div class="bluet_img_in_tooltip">
                                <iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube ); ?>?rel=0&showinfo=0" frameborder="0" allowfullscreen width="100%"></iframe>
                            </div>
                        <?php endif; ?>
                        <div class="bluet_text_content">
                            <?php if ( ! $hide_title ) : ?>
                                <span class="bluet_title_on_block"><?php the_title(); ?></span>
                            <?php endif; ?>
                            <?php the_content(); ?>
                        </div>
                        <div class="bluet_block_footer">
                            <?php if ( ! empty( $glossary_opt['bluet_kttg_show_glossary_link'] ) && $glossary_opt['bluet_kttg_show_glossary_link'] === 'on' ) : ?>
                                <p class="bluet_block_glossary_link">
                                    <a href="<?php echo esc_url( $glossary_opt['kttg_link_glossary_page_link'] ?? '' ); ?>">
                                        <?php echo esc_html( ! empty( $glossary_opt['kttg_link_glossary_label'] ) ? $glossary_opt['kttg_link_glossary_label'] : 'View glossary' ); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </span>
                <?php
            }
        }

        wp_reset_postdata();

        die();
    }

    /**
     * Output the footer JS that triggers the AJAX call when keywords are highlighted.
     */
    public function output_ajax_script(): void {
        if ( is_admin() ) {
            return;
        }

        $ajax_url = admin_url( 'admin-ajax.php' );
        $nonce    = wp_create_nonce( 'tltpy_load_keywords_nonce' );
        $mq       = wp_script_is( 'wp-mediaelement', 'enqueued' );
        ?>
        <script>
        var currentHoveredKeyword = false;

        jQuery(document).on("keywordsFetched", function() {
            var keyw = [];
            jQuery("body .bluet_tooltip").each(function(){
                keyw.push(jQuery(this).data('tooltip'));
            });

            jQuery.post(
                '<?php echo esc_url( $ajax_url ); ?>',
                {
                    'action':      'tltpy_load_keywords',
                    'keyword_ids': keyw,
                    'nonce':       '<?php echo esc_js( $nonce ); ?>'
                },
                function(response){
                    jQuery('#tooltip_blocks_to_show .bluet_block_to_show').remove(':not(#loading_tooltip)');
                    jQuery('#tooltip_blocks_to_show').append(response);
                    jQuery.event.trigger("keywordsLoaded");
                }
            );
        });

        jQuery(document).on("keywordsLoaded", function() {
            jQuery('#loading_tooltip').remove();

            if( currentHoveredKeyword && currentHoveredKeyword?.trigger && typeof currentHoveredKeyword.trigger === 'function' ){
                currentHoveredKeyword.trigger('mouseover');
                currentHoveredKeyword = 'done';
            }

            <?php if ( $mq ) : ?>
            jQuery('.tooltipy-pop .wp-audio-shortcode[style*="visibility:hidden"], .tooltipy-pop .wp-video-shortcode[style*="visibility:hidden"]').mediaelementplayer();
            jQuery('.tooltipy-pop .wp-audio-shortcode[style*="visibility: hidden"], .tooltipy-pop .wp-video-shortcode[style*="visibility: hidden"]').mediaelementplayer();
            <?php endif; ?>
        });
        </script>
        <?php
    }
}
