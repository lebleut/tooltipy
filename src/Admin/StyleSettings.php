<?php
namespace Tooltipy\Admin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;

/**
 * Registers style settings fields and outputs custom CSS/JS.
 * Replaces add-style.php and the style-related fields from settings-page.php.
 */
class StyleSettings {

    private Plugin $plugin;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    public function init(): void {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'wp_head',    [ $this, 'output_custom_style' ] );
        add_action( 'admin_head', [ $this, 'output_custom_style' ] );
    }

    public function register_settings(): void {
        add_settings_section( 'style_section', __( 'Customise the tooltip style :', 'tooltipy-lang' ), [ $this, 'section_style_cb' ], 'my_keywords_style' );
        add_settings_section( 'highlight_fetch_mode_section', __( 'Highlight fetch mode :', 'tooltipy-lang' ), [ $this, 'section_highlight_cb' ], 'my_highlight_fetch_mode' );

        add_settings_field( 'bt_kw_fetch_mode',     __( 'Fetch mode', 'tooltipy-lang' ),                   [ $this, 'field_fetch_mode' ],     'my_keywords_style',       'style_section' );
        add_settings_field( 'bt_kw_tooltip_width',  __( 'Tooltip width', 'tooltipy-lang' ),                 [ $this, 'field_tooltip_width' ],  'my_keywords_style',       'style_section' );
        add_settings_field( 'bt_kw_desc_font_size', __( 'Description tooltip Font size', 'tooltipy-lang' ), [ $this, 'field_font_size' ],      'my_keywords_style',       'style_section' );
        add_settings_field( 'bt_kw_alt_img',        __( 'Activate tooltips for images ?', 'tooltipy-lang' ),[ $this, 'field_alt_img' ],        'my_keywords_style',       'style_section' );
        add_settings_field( 'bt_kw_add_css_classes',__( 'Add CSS classes', 'tooltipy-lang' ),               [ $this, 'field_css_classes' ],    'my_keywords_style',       'style_section' );

        add_settings_field( 'bt_kw_tt_colour',      __( 'Keyword style', 'tooltipy-lang' ),                 [ $this, 'field_kw_colour' ],      'my_highlight_fetch_mode', 'highlight_fetch_mode_section' );
        add_settings_field( 'bt_kw_desc_colour',    __( 'Description tooltip style', 'tooltipy-lang' ),     [ $this, 'field_desc_colour' ],    'my_highlight_fetch_mode', 'highlight_fetch_mode_section' );

        register_setting( 'settings_group', 'bluet_kw_style' );
    }

    public function section_style_cb(): void       { esc_html_e( 'Make your own style.', 'tooltipy-lang' ); }
    public function section_highlight_cb(): void   { esc_html_e( 'Style for the highlight fetch mode.', 'tooltipy-lang' ); }

    public function field_fetch_mode(): void {
        $options = get_option( 'bluet_kw_style', [] );
        $mode    = $options['bt_kw_fetch_mode'] ?? 'highlight';
        ?>
        <p>
            <input value="highlight" id="bt_kw_fetch_mode-highlight" type="radio" name="bluet_kw_style[bt_kw_fetch_mode]" <?php checked( $mode, 'highlight' ); ?> />
            <label for="bt_kw_fetch_mode-highlight"><?php esc_html_e( 'Highlight Mode', 'tooltipy-lang' ); ?></label>
        </p>
        <p>
            <input value="icon" id="bt_kw_fetch_mode-icon" type="radio" name="bluet_kw_style[bt_kw_fetch_mode]" <?php checked( $mode, 'icon' ); ?> />
            <label for="bt_kw_fetch_mode-icon"><?php esc_html_e( 'Icon Mode', 'tooltipy-lang' ); ?></label>
        </p>
        <?php
    }

    public function field_tooltip_width(): void {
        $options = get_option( 'bluet_kw_style', [] );
        $width   = $options['bt_kw_tooltip_width'] ?? '';
        ?>
        <input id="bt_kw_tooltip_width_id" type="number" min="1" max="5000" name="bluet_kw_style[bt_kw_tooltip_width]" value="<?php echo esc_attr( $width ); ?>"> px
        <?php
    }

    public function field_font_size(): void {
        $options = get_option( 'bluet_kw_style', [] );
        ?>
        <input id="bt_kw_desc_font_size_id" type="number" min="1" max="50" name="bluet_kw_style[bt_kw_desc_font_size]" value="<?php echo esc_attr( $options['bt_kw_desc_font_size'] ?? '' ); ?>"> px
        <?php
    }

    public function field_alt_img(): void {
        $options = get_option( 'bluet_kw_style', [] );
        ?>
        <input type="checkbox" id="bt_kw_alt_img" name="bluet_kw_style[bt_kw_alt_img]" <?php checked( $options['bt_kw_alt_img'] ?? '', 'on' ); ?> />
        <?php esc_html_e( 'alt property of the images will be displayed as a tooltip', 'tooltipy-lang' ); ?>
        <?php
    }

    public function field_css_classes(): void {
        $options = get_option( 'bluet_kw_style', [] );
        $kw_cls  = $options['bt_kw_add_css_classes']['keyword'] ?? '';
        $pop_cls = $options['bt_kw_add_css_classes']['popup']   ?? '';
        ?>
        <p><label>
            <input id="bt_kw_keyword_classes_id" type="text" name="bluet_kw_style[bt_kw_add_css_classes][keyword]" value="<?php echo esc_attr( $kw_cls ); ?>">
            <?php esc_html_e( 'To inline keywords', 'tooltipy-lang' ); ?>
        </label></p>
        <p><label>
            <input id="bt_kw_popup_classes_id" type="text" name="bluet_kw_style[bt_kw_add_css_classes][popup]" value="<?php echo esc_attr( $pop_cls ); ?>">
            <?php esc_html_e( 'To tooltips', 'tooltipy-lang' ); ?>
        </label></p>
        <p><i><?php esc_html_e( "Separated with spaces, please don't use special characters", 'tooltipy-lang' ); ?></i></p>
        <?php
    }

    public function field_kw_colour(): void {
        $options = get_option( 'bluet_kw_style', [] );
        $bg_col  = $options['bt_kw_tt_bg_color'] ?? '';
        $ft_col  = $options['bt_kw_tt_color']    ?? '';
        $no_bg   = ! empty( $options['bt_kw_on_background'] );
        ?>
        <?php esc_html_e( 'Background Colour', 'tooltipy-lang' ); ?> : <br>
        <p>
            <input id="bluet_kw_no_background" type="checkbox" name="bluet_kw_style[bt_kw_on_background]" <?php checked( $no_bg ); ?> />
            <label for="bluet_kw_no_background" style="border-bottom: black 1px dotted;"><?php esc_html_e( 'No background (Dotted style)', 'tooltipy-lang' ); ?></label>
        </p>
        <div id="bluet_kw_bg_hide">
            <input type="text" class="color-field" name="bluet_kw_style[bt_kw_tt_bg_color]" value="<?php echo esc_attr( $bg_col ); ?>">
        </div>
        <br><?php esc_html_e( 'Font Colour', 'tooltipy-lang' ); ?> : <br>
        <input type="text" class="color-field" name="bluet_kw_style[bt_kw_tt_color]" value="<?php echo esc_attr( $ft_col ); ?>">
        <?php
    }

    public function field_desc_colour(): void {
        $options = get_option( 'bluet_kw_style', [] );
        $desc_bg = $options['bt_kw_desc_bg_color'] ?? '';
        $desc_ft = $options['bt_kw_desc_color']    ?? '';
        ?>
        <?php esc_html_e( 'Description Background Colour', 'tooltipy-lang' ); ?> : <br>
        <input type="text" class="color-field" name="bluet_kw_style[bt_kw_desc_bg_color]" value="<?php echo esc_attr( $desc_bg ); ?>">
        <br><?php esc_html_e( 'Description font Colour', 'tooltipy-lang' ); ?> :<br>
        <input type="text" class="color-field" name="bluet_kw_style[bt_kw_desc_color]" value="<?php echo esc_attr( $desc_ft ); ?>">
        <?php
    }

    /**
     * Output dynamic CSS and JS based on saved style options.
     * Replaces bluet_kw_custom_style().
     */
    public function output_custom_style(): void {
        $options = get_option( 'bluet_kw_style', [] );

        $tt_color       = esc_attr( $options['bt_kw_tt_color']      ?? 'inherit' );
        $tt_bg_color    = esc_attr( $options['bt_kw_tt_bg_color']    ?? '#0D45AA' );
        $desc_color     = esc_attr( $options['bt_kw_desc_color']     ?? '#ffffff' );
        $desc_bg_color  = esc_attr( $options['bt_kw_desc_bg_color']  ?? '#5eaa0d' );
        $desc_font_size = esc_attr( $options['bt_kw_desc_font_size'] ?? '14' );
        $tooltip_width  = isset( $options['bt_kw_tooltip_width'] ) && $options['bt_kw_tooltip_width'] !== ''
            ? 'width:' . (int) $options['bt_kw_tooltip_width'] . 'px !important;'
            : '';
        $no_background  = ! empty( $options['bt_kw_on_background'] );

        if ( $no_background ) {
            $tt_style = "color:{$tt_color};background:none;border-bottom:1px dotted;";
        } else {
            $tt_style = "color:{$tt_color};background-color:{$tt_bg_color};";
        }

        echo "\n<style type='text/css'>\n";
        echo ":root{--tooltipy-arrow-color:{$desc_bg_color};}\n";
        echo ".bluet_tooltip{{$tt_style}}\n";
        echo ".bluet_text_content{color:{$desc_color};background-color:{$desc_bg_color};font-size:{$desc_font_size}px;{$tooltip_width}}\n";
        echo ".tippy-box[data-theme~='tooltipy'] .bluet_text_content{color:{$desc_color};background-color:{$desc_bg_color};font-size:{$desc_font_size}px;{$tooltip_width}}\n";
        echo "</style>\n";

        // JS: handle "No background" checkbox toggling on the settings page
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            if(typeof jQuery("#bluet_kw_no_background") !== "undefined"){
                if(jQuery("#bluet_kw_no_background").is(":checked")){
                    jQuery("#bluet_kw_bg_hide").hide();
                }
                jQuery("#bluet_kw_no_background").change(function(){
                    jQuery("#bluet_kw_bg_hide").toggle(!this.checked);
                });
            }
            jQuery(document).on("keywordsFetched", function(){
                // notify any attached handler
            });
        });
        </script>
        <?php
    }
}
