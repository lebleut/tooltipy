<?php
/*
Element Description: VC Info Box
*/
 
// Element Class 
class vcTooltipyGlossary extends WPBakeryShortCode {
     
    // Element Init
    function __construct() {
        add_action( 'init', array( $this, 'vc_tooltipy_glossary_mapping' ) );
        add_shortcode( 'vc_tooltipy_glossary', array( $this, 'vc_tooltipy_glossary_html' ) );
    }
     
    // Element Mapping
    public function vc_tooltipy_glossary_mapping() {
         
        // Stop all if VC is not enabled
        if ( !defined( 'WPB_VC_VERSION' ) ) {
            return;
        }

        // Map the block with vc_map()
        vc_map( 
            array(
                'name' => __('Tooltipy Glossary', 'bluet-kw'),
                'base' => 'vc_tooltipy_glossary',
                'description' => __('The Tooltipy glossary page', 'bluet-kw'), 
                'category' => __('Tooltipy', 'bluet-kw'),   
                'icon' => TOOLTIPY_URL.'visual-composer/img/icon.jpg',
            )
        );                                
        
    }
     
     
    // Element HTML
    public function vc_tooltipy_glossary_html( $atts ) {
        // Fill $html var with data
        $html = do_shortcode( '[tooltipy_glossary]' );
         
        return $html;
         
    }
     
} // End Element Class
 
 
// Element Class Init
new vcTooltipyGlossary();   