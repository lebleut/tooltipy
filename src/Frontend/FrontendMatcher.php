<?php
namespace Tooltipy\Frontend;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tooltipy\Plugin;
use Tooltipy\Keyword\KeywordRepository;
use Tooltipy\Keyword\KeywordData;
use Tooltipy\Security\Sanitizer;

/**
 * Outputs the JavaScript block that uses findAndReplaceDOMText to highlight keywords.
 * Also outputs the tooltip container markup.
 * Replaces the massive tltpy_place_tooltips() function.
 */
class FrontendMatcher {

    private Plugin             $plugin;
    private KeywordRepository  $repo;

    public function __construct( Plugin $plugin ) {
        $this->plugin = $plugin;
        $this->repo   = new KeywordRepository( $plugin );
    }

    public function init(): void {
        add_action( 'wp_footer',    [ $this, 'output' ] );
        add_action( 'admin_footer', [ $this, 'output' ] );
    }

    public function output(): void {
        if ( is_admin() ) {
            return;
        }

        $post_id    = get_the_id();
        $exclude_me = get_post_meta( $post_id, 'bluet_exclude_post_from_matching', true );

        if ( is_singular() && $exclude_me === 'on' ) {
            return;
        }

        $settings  = get_option( 'bluet_kw_settings', [] );
        $adv       = get_option( 'bluet_kw_advanced', [] );
        $style_opt = get_option( 'bluet_kw_style', [] );

        $position       = $settings['bt_kw_position']       ?? 'bottom';
        $animation_type = $settings['bt_kw_animation_type'] ?? 'flipInX';
        $animation_speed= $settings['bt_kw_animation_speed'] ?? '';
        $match_all      = ! empty( $settings['bt_kw_match_all'] ) && $settings['bt_kw_match_all'] === 'on';

        $css_kw  = Sanitizer::css_classes( (string) ( $style_opt['bt_kw_add_css_classes']['keyword'] ?? '' ) );
        $css_pop = Sanitizer::css_classes( (string) ( $style_opt['bt_kw_add_css_classes']['popup'] ?? '' ) );
        $fetch_mode = $style_opt['bt_kw_fetch_mode'] ?? 'highlight';

        // Excluded keywords for this post
        $excluded_kws = [];
        if ( is_singular() ) {
            $raw = get_post_meta( $post_id, 'bluet_exclude_keywords_from_matching', true );
            $excluded_kws = array_filter( array_map( 'strtolower', array_map( 'trim', explode( ',', (string) $raw ) ) ) );
        }

        $all_keywords = $this->repo->get_all();

        // Filter out excluded keywords for this page
        $keywords = array_filter( $all_keywords, function ( KeywordData $kw ) use ( $excluded_kws ) {
            return ! in_array( strtolower( trim( $kw->term ) ), $excluded_kws, true );
        } );

        if ( empty( $keywords ) ) {
            return;
        }

        // Cover / exclude areas from advanced settings
        $cover_classes  = [];
        $cover_tags     = [];
        $exclude_areas  = [];

        if ( ! empty( $adv['kttg_cover_areas'] ) ) {
            $cover_classes = array_filter( explode( ' ', Sanitizer::css_classes( (string) $adv['kttg_cover_areas'] ) ) );
        }
        if ( ! empty( $adv['kttg_cover_tags'] ) ) {
            $cover_tags = array_filter(
                array_map(
                    static function ( string $tag ): string {
                        return (string) preg_replace( '/[^a-z0-9]/', '', strtolower( $tag ) );
                    },
                    explode( ' ', (string) $adv['kttg_cover_tags'] )
                )
            );
        }
        if ( ! empty( $adv['kttg_exclude_areas'] ) ) {
            $exclude_areas = array_filter( explode( ' ', Sanitizer::css_classes( (string) $adv['kttg_exclude_areas'] ) ) );
        }

        $exclude_anchors = ! empty( $adv['kttg_exclude_anchor_tags'] ) && $adv['kttg_exclude_anchor_tags'] === 'on';
        $exclude_headings = $adv['kttg_exclude_heading_tags'] ?? [];
        $exclude_common   = $adv['kttg_exclude_common_tags']  ?? [];

        $custom_events = [];
        if ( ! empty( $adv['kttg_custom_events'] ) ) {
            foreach ( array_filter( explode( ',', (string) $adv['kttg_custom_events'] ) ) as $event ) {
                $event = trim( $event );
                if ( preg_match( '/^[A-Za-z][A-Za-z0-9_-]{0,63}$/', $event ) ) {
                    $custom_events[] = $event;
                }
            }
        }

        $position        = sanitize_key( (string) $position );
        if ( ! in_array( $position, [ 'top', 'bottom', 'left', 'right' ], true ) ) {
            $position = 'bottom';
        }
        $animation_type  = sanitize_html_class( (string) $animation_type );
        $animation_speed = sanitize_html_class( (string) $animation_speed );

        $icon_mode = ( $fetch_mode === 'icon' );
        $fetch_all_flag = $match_all ? 'g' : '';

        $icon_img_url = esc_url( TOOLTIPY_PLUGIN_URL . 'assets/qst-mark-1.png' );
        $loading_gif  = esc_url( TOOLTIPY_PLUGIN_URL . 'assets/loading.gif' );

        ?>
        <script type="text/javascript">
        jQuery(function($) {
            window.tltpy_fetch_kws = function(){
                window.kttg_tab = <?php
					$tab_rows = [];
					foreach ( $keywords as $kw ) {
						$term = html_entity_decode( $kw->term, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
						$syns = html_entity_decode( $kw->synonyms, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
						$pattern = preg_quote( $term, '/' );
						if ( $syns !== '' ) {
							foreach ( array_filter( array_map( 'trim', explode( '|', $syns ) ) ) as $syn ) {
								$pattern .= '|' . preg_quote( $syn, '/' );
							}
						}
						$tab_rows[] = [
							$pattern,
							$kw->case_sensitive,
							$kw->is_prefix,
							$kw->families_class,
							$kw->has_video() ? 'tooltipy-kw-youtube' : '',
							esc_url_raw( $kw->icon_url ),
							0,
						];
					}
					echo wp_json_encode( $tab_rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE );
				?>;

                window.tooltipIds = <?php
					echo wp_json_encode(
						array_map( static fn( $kw ) => (string) $kw->id, array_values( $keywords ) ),
						JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
					);
				?>;
                var class_to_cover = [<?php
                    foreach ( $cover_classes as $cls ) {
                        if ( $cls !== '' ) { echo '".' . esc_js( $cls ) . '",'; }
                    }
                ?>];
                var tags_to_cover = [<?php
                    foreach ( $cover_tags as $tag ) {
                        if ( $tag !== '' ) { echo '"' . esc_js( $tag ) . '",'; }
                    }
                ?>];

                var areas_to_cover = class_to_cover.concat(tags_to_cover);
                if(areas_to_cover.length === 0){ areas_to_cover.push("body"); }

                var fetch_all = "<?php echo esc_js( $fetch_all_flag ); ?>";

                var zones_to_exclude = [
                    ".kttg_glossary_content",
                    "#tooltip_blocks_to_show",
                    <?php
                    foreach ( $exclude_areas as $area ) {
                        if ( $area !== '' ) { echo '".' . esc_js( $area ) . '",'; }
                    }
                    if ( $exclude_anchors ) { echo '"a",'; }
                    for ( $i = 1; $i < 7; $i++ ) {
                        if ( ! empty( $exclude_headings[ 'h' . $i ] ) && $exclude_headings[ 'h' . $i ] === 'on' ) {
                            echo '"h' . $i . '",';
                        }
                    }
                    foreach ( $exclude_common as $tag => $val ) {
                        echo '"' . esc_js( $tag ) . '",';
                    }
                    ?>
                ];

                for(var j=0; j < areas_to_cover.length; j++){
                    var tmp_classes = areas_to_cover.slice();
                    tmp_classes.splice(j,1);

                    if(
                        tmp_classes.length > 0 &&
                        $(areas_to_cover[j]).parents(tmp_classes.join(",")).length > 0
                    ){ continue; }

                    for(var cls=0; cls < $(areas_to_cover[j]).length; cls++){
                        var zone = $(areas_to_cover[j])[cls];
                        if(zone === undefined){ continue; }

                        for(var i=0; i < kttg_tab.length; i++){
                            var suffix = '';
                            if(kttg_tab[i][2] === true){ suffix = '\\w*'; }

                            var txt_to_find = kttg_tab[i][0];
                            var text_sep = '[\\s\\<\\>\\,\\;\\:\\!\\$\\^\\*\\=\\-\\(\\)\'\"\\&\\?\\.\\\/\\§\\%\\£\\¨\\+\\°\\~\\#\\{\\}\\[\\]\\|\\`\\^\\@\\¤]';

                            var japanese_chinese = /[\u3000-\u303F]|[\u3040-\u309F]|[\u30A0-\u30FF]|[\uFF00-\uFFEF]|[\u4E00-\u9FAF]|[\u2605-\u2606]|[\u2190-\u2195]|\u203B/;
                            if(japanese_chinese.test(txt_to_find)){ text_sep = ""; }

                            var pattern =
                                text_sep+"("+txt_to_find+")"+suffix+""+text_sep
                                +"|^("+txt_to_find+")"+suffix+"$"
                                +"|"+text_sep+"("+txt_to_find+")"+suffix+"$"
                                +"|^("+txt_to_find+")"+suffix+text_sep;

                            var iscase = (kttg_tab[i][1] === false) ? 'i' : '';
                            var reg    = new RegExp(pattern, fetch_all+iscase);

                            var tooltipy_families_class = kttg_tab[i][3];
                            var tooltipy_video_class    = kttg_tab[i][4];

                            if(typeof findAndReplaceDOMText === 'function'){
                                delete findAndReplaceDOMText.NON_PROSE_ELEMENTS.button;
                                findAndReplaceDOMText(zone, {
                                    preset: 'prose',
                                    find: reg,
                                    replace: function(portion){
                                        if(portion.text.trim() === "" && portion.node.textContent.substr(portion.node.textContent.length-1) === " "){
                                            portion.text = portion.text + " ";
                                        }
                                        var splitted       = portion.text.split(new RegExp(txt_to_find,'i'));
                                        var txt_to_display = portion.text.match(new RegExp(txt_to_find,'i'));
                                        var zones_str      = zones_to_exclude.join(", ");

                                        if(
                                            $(portion.node.parentNode).parents(zones_str).length > 0 ||
                                            $(portion.node.parentNode).is(zones_str)
                                        ){ return portion.text; }

                                        if(
                                            $(portion.node.parentNode).parents(".bluet_tooltip").length > 0 ||
                                            $(portion.node.parentNode).is(".bluet_tooltip")
                                        ){ return portion.text; }

                                        <?php if ( ! $match_all ) : ?>
                                        if(kttg_tab[i][6] === 1){ return portion.text; }
                                        <?php endif; ?>

                                        kttg_tab[i][6]++;

                                        var before_kw = (splitted[0] !== undefined) ? splitted[0] : "";
                                        var after_kw  = (splitted[1] !== undefined) ? splitted[1] : "";

                                        if(portion.text !== "" && portion.text !== " " && portion.text !== "\t" && portion.text !== "\n"){
                                            var elem = document.createElement("span");
                                            var kttg_icon = '';
                                            if(kttg_tab[i][5] !== ""){ kttg_icon = '<img src="'+kttg_tab[i][5]+'" >'; }

                                            <?php if ( $icon_mode ) : ?>
                                            if(suffix !== ""){
                                                var reg2 = new RegExp(suffix,"");
                                                var suff_after_kw = after_kw.split(reg2)[1] || "";
                                                elem.innerHTML = (txt_to_display==null) ? before_kw+after_kw.match(reg2)+suff_after_kw : before_kw+txt_to_display+after_kw.match(reg2)+'<img src="<?php echo esc_js( $icon_img_url ); ?>" class="bluet_tooltip tooltipy-kw-prefix tooltipy-kw-icon" data-tooltip='+tooltipIds[i]+' />'+suff_after_kw;
                                            } else {
                                                elem.innerHTML = (txt_to_display==null) ? before_kw+after_kw : before_kw+txt_to_display+'<img src="<?php echo esc_js( $icon_img_url ); ?>" class="bluet_tooltip tooltipy-kw-icon" data-tooltip='+tooltipIds[i]+' /> '+after_kw;
                                            }
                                            <?php else : ?>
                                            if(suffix !== ""){
                                                var reg3 = new RegExp(suffix,"");
                                                var suff_after_kw2 = after_kw.split(reg3)[0] || "";
                                                if(suff_after_kw2 === "" && after_kw.split(reg3)[1] !== undefined){ suff_after_kw2 = after_kw.split(reg3)[1]; }
                                                var just_after_kw  = after_kw.match(reg3) || "";
                                                if(suff_after_kw2 === " "){ suff_after_kw2 = "  "; }
                                                if(before_kw === " "){ before_kw = "  "; }
                                                elem.innerHTML = (txt_to_display==null) ? before_kw+just_after_kw+suff_after_kw2 : before_kw+'<span class="bluet_tooltip tooltipy-kw-prefix" data-tooltip='+tooltipIds[i]+'>'+kttg_icon+txt_to_display+""+just_after_kw+'</span>'+suff_after_kw2;
                                            } else {
                                                if(after_kw === " "){ after_kw = "  "; }
                                                if(before_kw === " "){ before_kw = "  "; }
                                                elem.innerHTML = (txt_to_display==null) ? before_kw+after_kw : before_kw+'<span class="bluet_tooltip" data-tooltip='+tooltipIds[i]+'>'+kttg_icon+txt_to_display+'</span>'+after_kw;
                                            }
                                            <?php endif; ?>

                                            $($(elem).children(".bluet_tooltip")[0]).addClass("tooltipy-kw tooltipy-kw-"+tooltipIds[i]+" "+tooltipy_families_class+" "+tooltipy_video_class+" <?php echo esc_js( $css_kw ); ?>");
                                            return elem;
                                        } else {
                                            return "";
                                        }
                                    }
                                });
                            }
                        }
                    }
                }

                $.event.trigger("keywordsFetched");
            };

            $(document).ready(function(){
                tltpy_fetch_kws();
                window.tooltipyTippy = window.tooltipyTippy || {};
                window.tooltipyTippy.placement = "<?php echo esc_js( $position ); ?>";
                animation_type  = "<?php echo esc_js( $animation_type ); ?>";
                animation_speed = "<?php echo esc_js( $animation_speed ); ?>";
                if (typeof tooltipyInitTippy === "function") {
                    tooltipyInitTippy(".bluet_tooltip, .bluet_img_tooltip", "<?php echo esc_js( $position ); ?>");
                }
            });

            $(document).on("keywordsLoaded",function(){
                if (typeof tooltipyInitTippy === "function") {
                    tooltipyInitTippy(".bluet_tooltip, .bluet_img_tooltip", "<?php echo esc_js( $position ); ?>");
                }
            });

            <?php foreach ( $custom_events as $event ) : ?>
            document.addEventListener('<?php echo esc_js( trim( $event ) ); ?>', function(){
                tltpy_fetch_kws();
            });
            <?php endforeach; ?>
        });
        </script>

        <?php
        // Loading placeholder
        $loading_html = '<span id="loading_tooltip" class="bluet_block_to_show" data-tooltip="0"><div class="bluet_block_container"><div class="bluet_text_content"><img width="15px" src="' . esc_url( $loading_gif ) . '" /></div></div></span>';
        ?>
        <script type="text/javascript">
        jQuery(function($){
            $(document).ready(function(){
                $("#tooltip_blocks_to_show").append('<?php echo esc_js( $loading_html ); ?>');
            });
        });
        </script>
        <?php
    }
}
