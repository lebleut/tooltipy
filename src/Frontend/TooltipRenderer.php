<?php
namespace Tooltipy\Frontend;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;
use Tooltipy\Keyword\KeywordData;
use Tooltipy\Security\Sanitizer;

/**
 * Generates the HTML markup for tooltip blocks.
 * Replaces tltpy_tooltip_layout() and tltpy_all_tooltips_layout().
 */
class TooltipRenderer {

    private Plugin $plugin;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    /**
     * Render a single tooltip block (the hidden pop-up panel).
     */
    public function render_tooltip_block( KeywordData $kw, bool $show_glossary_link = false ): string {
        $settings    = get_option( 'bluet_kw_settings', [] );
        $hide_title  = ! empty( $settings['bt_kw_hide_title'] ) && $settings['bt_kw_hide_title'] === 'on';
        $title_html  = '';

        if ( ! $hide_title ) {
            $title_html = '<span class="bluet_title_on_block">' . esc_html( $kw->term ) . '</span>';
        }

        $close_btn = '<img src="' . esc_url( TOOLTIPY_PLUGIN_URL . 'assets/close_button.png' ) . '" class="bluet_hide_tooltip_button" />';
        $content   = Sanitizer::tooltip_html( (string) wpautop( $kw->content ) );

        if ( $kw->has_video() ) {
            return $this->render_video_block( $kw, $title_html, $close_btn, $content );
        }

        return sprintf(
            '<span class="bluet_block_to_show" data-tooltip="%d">%s<div class="bluet_block_container"><div class="bluet_img_in_tooltip">%s</div><div class="bluet_text_content">%s%s</div>%s</div></span>',
            $kw->id,
            $close_btn,
            $kw->thumbnail_html, // WP thumbnail HTML
            $title_html,
            $content,
            $this->render_glossary_footer( $show_glossary_link )
        );
    }

    /**
     * Render a video tooltip block (YouTube embed).
     */
    private function render_video_block( KeywordData $kw, string $title_html, string $close_btn, string $content ): string {
        $youtube_id = Sanitizer::youtube_id( $kw->youtube_id );
        if ( $youtube_id === '' ) {
            return sprintf(
                '<span class="bluet_block_to_show" data-tooltip="%d">%s<div class="bluet_block_container"><div class="bluet_text_content">%s%s</div></div></span>',
                $kw->id,
                $close_btn,
                $title_html,
                $content
            );
        }

        $iframe_id = 'iframe_' . $kw->id;
        $iframe    = '<div class="bluet_img_in_tooltip" id="' . esc_attr( $iframe_id ) . '">'
            . '<iframe width="300" height="200" src="https://www.youtube.com/embed/'
            . esc_attr( $youtube_id )
            . '?rel=0&amp;showinfo=0&amp;enablejsapi=1" frameborder="0" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>'
            . '</div>';

        return sprintf(
            '<span class="bluet_block_to_show" data-tooltip="%d" onmouseover="callPlayer(\'%s\',\'playVideo\')" onmouseleave="callPlayer(\'%s\',\'pauseVideo\')">%s<div class="bluet_block_container">%s<div class="bluet_text_content">%s</div></div></span>',
            $kw->id,
            esc_js( $iframe_id ),
            esc_js( $iframe_id ),
            $close_btn,
            $iframe,
            $title_html . $content
        );
    }

    /**
     * Render the optional glossary-link footer.
     */
    private function render_glossary_footer( bool $show ): string {
        if ( ! $show ) {
            return '';
        }

        $glossary_options = get_option( 'bluet_glossary_options', [] );
        $show_link        = ! empty( $glossary_options['bluet_kttg_show_glossary_link'] ) && $glossary_options['bluet_kttg_show_glossary_link'] === 'on';

        if ( ! $show_link ) {
            return '';
        }

        $url   = esc_url( $glossary_options['kttg_link_glossary_page_link'] ?? '' );
        $label = esc_html( $glossary_options['kttg_link_glossary_label'] ?? '' );
        if ( $label === '' ) {
            $label = esc_html__( 'View glossary', 'tooltipy-lang' );
        }

        return '<div class="bluet_block_footer"><p class="bluet_block_glossary_link"><a href="' . $url . '">' . $label . '</a></p></div>';
    }

    /**
     * Render the AJAX version of a tooltip block (used in load-ajax response).
     */
    public function render_ajax_block( int $kw_id, string $families_class, string $youtube_class, string $extra_classes ): string {
        $settings     = get_option( 'bluet_kw_settings', [] );
        $hide_title   = ! empty( $settings['bt_kw_hide_title'] ) && $settings['bt_kw_hide_title'] === 'on';
        $youtube_id   = Sanitizer::youtube_id( (string) get_post_meta( $kw_id, 'bluet_youtube_video_id', true ) );
        $glossary_opt = get_option( 'bluet_glossary_options', [] );
        $extra_classes = Sanitizer::css_classes( $extra_classes );
        $families_class = Sanitizer::css_classes( $families_class );
        $youtube_class  = Sanitizer::css_classes( $youtube_class );
        $content        = Sanitizer::tooltip_html( (string) apply_filters( 'the_content', get_post_field( 'post_content', $kw_id ) ) );

        ob_start();
        ?>
        <span class="bluet_block_to_show tooltipy-pop tooltipy-pop-<?php echo esc_attr( (string) $kw_id ); ?> <?php echo esc_attr( trim( $families_class . ' ' . $youtube_class . ' ' . $extra_classes ) ); ?>" data-tooltip="<?php echo esc_attr( (string) $kw_id ); ?>">
            <div class="bluet_hide_tooltip_button">&times;</div>
            <div class="bluet_block_container">
                <?php if ( $youtube_id === '' ) : ?>
                    <div class="bluet_img_in_tooltip"><?php echo get_the_post_thumbnail( $kw_id, 'medium' ); ?></div>
                <?php else : ?>
                    <div class="bluet_img_in_tooltip">
                        <iframe src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_id ); ?>?rel=0&showinfo=0" frameborder="0" allowfullscreen width="100%" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>
                <?php endif; ?>
                <div class="bluet_text_content">
                    <?php if ( ! $hide_title ) : ?>
                        <span class="bluet_title_on_block"><?php echo esc_html( get_the_title( $kw_id ) ); ?></span>
                    <?php endif; ?>
                    <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses_post applied ?>
                </div>
                <?php if ( ! empty( $glossary_opt['bluet_kttg_show_glossary_link'] ) && $glossary_opt['bluet_kttg_show_glossary_link'] === 'on' ) : ?>
                    <div class="bluet_block_footer">
                        <p class="bluet_block_glossary_link">
                            <a href="<?php echo esc_url( $glossary_opt['kttg_link_glossary_page_link'] ?? '' ); ?>">
                                <?php echo esc_html( ! empty( $glossary_opt['kttg_link_glossary_label'] ) ? $glossary_opt['kttg_link_glossary_label'] : __( 'View glossary', 'tooltipy-lang' ) ); ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </span>
        <?php
        return (string) ob_get_clean();
    }
}
