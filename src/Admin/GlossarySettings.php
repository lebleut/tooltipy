<?php
namespace Tooltipy\Admin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;
use Tooltipy\Security\OptionSanitizer;

/**
 * Glossary settings section.
 * Replaces settings-glossary.php.
 */
class GlossarySettings {

    private Plugin $plugin;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
    }

    public function init(): void {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings(): void {
        add_settings_section( 'glossary_section', __( 'Glossary settings :', 'tooltipy-lang' ), [ $this, 'section_cb' ], 'my_keywords_glossary_settings' );

        add_settings_field( 'kttg_kws_per_page',          __( 'Keywords per page', 'tooltipy-lang' ),   [ $this, 'field_per_page' ],       'my_keywords_glossary_settings', 'glossary_section' );
        add_settings_field( 'kttg_glossary_text',         __( 'Glossary page labels', 'tooltipy-lang' ), [ $this, 'field_labels' ],          'my_keywords_glossary_settings', 'glossary_section' );
        add_settings_field( 'tltpy_glossary_show_thumb',  __( 'Thumbnails', 'tooltipy-lang' ),           [ $this, 'field_show_thumb' ],      'my_keywords_glossary_settings', 'glossary_section' );
        add_settings_field( 'bluet_kttg_show_glossary_link', __( 'Glossary link page', 'tooltipy-lang' ), [ $this, 'field_glossary_link' ],  'my_keywords_glossary_settings', 'glossary_section' );
        add_settings_field( 'tltpy_titles',               __( 'Titles', 'tooltipy-lang' ),               [ $this, 'field_titles' ],          'my_keywords_glossary_settings', 'glossary_section' );

        register_setting(
            'settings_group',
            'bluet_glossary_options',
            [
                'type'              => 'array',
                'sanitize_callback' => [ OptionSanitizer::class, 'glossary' ],
                'default'           => [],
            ]
        );
    }

    public function section_cb(): void {
        echo '<p class="tooltipy-settings__lead">' . esc_html__( 'Glossary page labels and shortcode options.', 'tooltipy-lang' ) . '</p>';
        echo '<p class="description">' . esc_html__( 'Use the shortcode', 'tooltipy-lang' )
            . ' <code>[' . esc_html( TLTPY_GLOSSARY_SHORTCODE ) . ']</code>'
            . ' ' . esc_html__( 'or legacy', 'tooltipy-lang' )
            . ' <code>[kttg_glossary]</code>.</p>';
    }

    public function field_per_page(): void {
        $options     = get_option( 'bluet_glossary_options', [] );
        $kws_per_page = $options['kttg_kws_per_page'] ?? '';
        ?>
        <input id="bt_kw_glossary_kpp" type="number" min="1" max="900" name="bluet_glossary_options[kttg_kws_per_page]" value="<?php echo esc_attr( $kws_per_page ); ?>" placeholder="<?php esc_attr_e( 'ALL', 'tooltipy-lang' ); ?>">
        <?php esc_html_e( 'Keywords Per Page (leave blank for unlimited keywords per page)', 'tooltipy-lang' ); ?>
        <?php
    }

    public function field_labels(): void {
        $options = get_option( 'bluet_glossary_options', [] );
        $text    = $options['kttg_glossary_text'] ?? [];

        $fields = [
            'kttg_glossary_text_all'              => '<b>ALL</b> label',
            'kttg_glossary_text_previous'         => '<b>Previous</b> label',
            'kttg_glossary_text_next'             => '<b>Next</b> label',
            'kttg_glossary_text_select_a_family'  => '<b>Select a family</b> label',
            'kttg_glossary_text_select_all_families' => '<b>All families</b> label',
        ];

        foreach ( $fields as $key => $label ) {
            $val = $text[ $key ] ?? '';
            echo wp_kses_post( $label ) . ' : ';
            echo '<input type="text" name="bluet_glossary_options[kttg_glossary_text][' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '"><br>';
        }
    }

    public function field_show_thumb(): void {
        $options = get_option( 'bluet_glossary_options', [] );
        ?>
        <label for="bt_kw_show_glossary_thumb_id"><?php esc_html_e( 'Show thumbnails on the glossary page', 'tooltipy-lang' ); ?></label>
        <input type="checkbox" id="bt_kw_show_glossary_thumb_id" name="bluet_glossary_options[tltpy_glossary_show_thumb]" <?php checked( $options['tltpy_glossary_show_thumb'] ?? '', 'on' ); ?> />
        <?php
    }

    public function field_glossary_link(): void {
        $options = get_option( 'bluet_glossary_options', [] );
        $show    = $options['bluet_kttg_show_glossary_link'] ?? '';
        $link    = $options['kttg_link_glossary_page_link'] ?? '';
        $label   = $options['kttg_link_glossary_label'] ?? '';
        $enabled = $show === 'on';
        ?>
        <div class="tooltipy-glossary-link-settings">
            <label class="tooltipy-settings__check" for="bt_kw_show_glossary_link_id">
                <input
                    type="checkbox"
                    id="bt_kw_show_glossary_link_id"
                    name="bluet_glossary_options[bluet_kttg_show_glossary_link]"
                    <?php checked( $show, 'on' ); ?>
                    data-tooltipy-glossary-toggle
                />
                <span><?php esc_html_e( 'Add glossary link page in the tooltips footer', 'tooltipy-lang' ); ?></span>
            </label>

            <div class="tooltipy-glossary-link-settings__fields" data-tooltipy-glossary-fields<?php echo $enabled ? '' : ' hidden'; ?>>
                <div class="tooltipy-glossary-link-settings__field">
                    <label for="bt_kw_glossary_page_link"><?php esc_html_e( 'Glossary page link', 'tooltipy-lang' ); ?></label>
                    <input
                        type="url"
                        id="bt_kw_glossary_page_link"
                        name="bluet_glossary_options[kttg_link_glossary_page_link]"
                        value="<?php echo esc_attr( $link ); ?>"
                        placeholder="https://…"
                        data-tooltipy-glossary-url
                    />
                    <p class="tooltipy-settings__warn" data-tooltipy-glossary-url-warning<?php echo ( $enabled && $link === '' ) ? '' : ' hidden'; ?>>
                        <?php esc_html_e( 'Please add the glossary page URL, otherwise the link will not appear in tooltips.', 'tooltipy-lang' ); ?>
                    </p>
                </div>
                <div class="tooltipy-glossary-link-settings__field">
                    <label for="bt_kw_glossary_link_label_id"><?php esc_html_e( 'Glossary link label', 'tooltipy-lang' ); ?></label>
                    <input
                        type="text"
                        id="bt_kw_glossary_link_label_id"
                        name="bluet_glossary_options[kttg_link_glossary_label]"
                        value="<?php echo esc_attr( $label ); ?>"
                        placeholder="<?php esc_attr_e( 'View glossary', 'tooltipy-lang' ); ?>"
                    />
                </div>
            </div>
        </div>
        <?php
    }

    public function field_titles(): void {
        $options = get_option( 'bluet_glossary_options', [] );
        ?>
        <label>
            <?php esc_html_e( 'Add links to titles', 'tooltipy-lang' ); ?>
            <input type="checkbox" name="bluet_glossary_options[link_titles]" <?php checked( $options['link_titles'] ?? '', 'on' ); ?> />
        </label>
        <?php
    }
}
