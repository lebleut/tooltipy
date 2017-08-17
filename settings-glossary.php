<?php
defined('ABSPATH') or die("No script kiddies please!");

function tooltipy_glossary_admin_init_func(){

	// section
	add_settings_section(
		'glossary_section',					// The ID to use for this section in attribute tags
		__('Glossary settings :','bluet-kw'),					// The title of the section rendered to the screen
		'bluet_kw_glossary_display',		// The function used to render the options for this section
		'my_keywords_glossary_settings'				// The ID of the page on which this section is rendered
	);
	
	/* glossari fields*/
	add_settings_field( 
		'kttg_kws_per_page', 					// The ID (or the name) of the field
		__('Keywords per page','bluet-kw'), 			// The text used to label the field
		'kttg_glossary_kws_per_page_display', 		// The callback function used to render the field
		'my_keywords_glossary_settings',				// The page on which we'll be rendering this field
		'glossary_section'					// The section to which we're adding the setting
	);
	
	add_settings_field( 
		'kttg_glossary_text', 					// The ID (or the name) of the field
		__('Glossary page labels','bluet-kw'), 			// The text used to label the field
		'kttg_glossary_text_display', 		// The callback function used to render the field
		'my_keywords_glossary_settings',				// The page on which we'll be rendering this field
		'glossary_section'					// The section to which we're adding the setting
	);
		
	// Define view glossary thumbnail
    add_settings_field( 
        'bluet_kttg_glossary_show_thumb',                    
        __('Thumbnails','bluet-kw'),            
        'bt_kw_show_glossary_show_thumb',         
        'my_keywords_glossary_settings',                
        'glossary_section'
    );      
 
         // Define view glossary page field
    add_settings_field( 
        'bluet_kttg_show_glossary_link',                    
        __('Glossary link page','bluet-kw'),            
        'bt_kw_show_glossary_link_display',         
        'my_keywords_glossary_settings',                // The page on which we'll be rendering this field
        'glossary_section'                  // The section to which we're adding the setting                    
    );

	/*for glossary options*/
	register_setting(
		'settings_group',					// The name of the group of settings
		'bluet_glossary_options'					// The name of the actual option (or setting)
	);	
}

add_action( 'admin_init','tooltipy_glossary_admin_init_func');

function kttg_glossary_kws_per_page_display(){
	$options = get_option( 'bluet_glossary_options' );
	?>
	<input id="bt_kw_glossary_kpp" type="number" min="1" max="900" name="bluet_glossary_options[kttg_kws_per_page]" value="<?php echo $options['kttg_kws_per_page']; ?>" placeholder="<?php _e('ALL','bluet-kw');?>"> Keywords Per Page (leave blank for unlimited keywords per page)<?php
}
function kttg_glossary_text_display(){
	$options = get_option( 'bluet_glossary_options' );
	_e('<b>ALL</b> label','bluet-kw'); ?> : <input  type="text" name="bluet_glossary_options[kttg_glossary_text][kttg_glossary_text_all]" value="<?php echo $options['kttg_glossary_text']['kttg_glossary_text_all']; ?>" placeholder="<?php _e('ALL','bluet-kw');?>"><br>
	<?php
	_e('<b>Previous</b> label','bluet-kw'); ?> : <input  type="text" name="bluet_glossary_options[kttg_glossary_text][kttg_glossary_text_previous]" value="<?php echo $options['kttg_glossary_text']['kttg_glossary_text_previous']; ?>" placeholder="<?php _e('Previous','bluet-kw');?>"><br>
	<?php
	_e('<b>Next</b> label','bluet-kw'); ?> : <input  type="text" name="bluet_glossary_options[kttg_glossary_text][kttg_glossary_text_next]" value="<?php echo $options['kttg_glossary_text']['kttg_glossary_text_next']; ?>" placeholder="<?php _e('Next','bluet-kw');?>"><br>
	<?php
	
}

function bluet_kw_glossary_display(){
	_e('Choose settings for your glossary.','bluet-kw');
}

function bt_kw_show_glossary_link_display(){
    //$options = get_option( 'bluet_kw_settings' );
    $glossary_options = get_option( 'bluet_glossary_options' );
 
    ?>
    <div>
        <label for="bt_kw_show_glossary_link_id"><?php _e('Add glossary link page in the tooltips footer','bluet-kw'); ?></label>
        <input type="checkbox"     id="bt_kw_show_glossary_link_id"  name="bluet_glossary_options[bluet_kttg_show_glossary_link]" <?php if(!empty($glossary_options['bluet_kttg_show_glossary_link']) and $glossary_options['bluet_kttg_show_glossary_link']=='on') echo 'checked'; ?> />
 
    </div>
    
    <div>
            <label for="bt_kw_glossary_page_link"><?php _e('Glossary page link','bluet-kw'); ?></label>
            <input  type="text" id="bt_kw_glossary_page_link" name="bluet_glossary_options[kttg_link_glossary_page_link]" value="<?php echo $glossary_options['kttg_link_glossary_page_link']; ?>" placeholder="http://...">
    </div>
    
    <div>
        <label for="bt_kw_glossary_link_label_id"><?php _e('Glossary link label','bluet-kw'); ?></label>        
        <input  type="text" id="bt_kw_glossary_link_label_id" name="bluet_glossary_options[kttg_link_glossary_label]" value="<?php echo $glossary_options['kttg_link_glossary_label']; ?>" placeholder="<?php _e('View glossary','bluet-kw');?>">
 
    </div>
 
    
    <?php
}
 
function bt_kw_show_glossary_show_thumb(){
    $glossary_options = get_option('bluet_glossary_options');
    ?>
    <div>
        <label for="bt_kw_show_glossary_thumb_id"><?php _e('Show thumbnails on the glossary page','bluet-kw'); ?></label>
        <input type="checkbox"     id="bt_kw_show_glossary_thumb_id"  name="bluet_glossary_options[bluet_kttg_glossary_show_thumb]" <?php if(!empty($glossary_options['bluet_kttg_glossary_show_thumb']) and $glossary_options['bluet_kttg_glossary_show_thumb']=='on') echo 'checked'; ?> />
 
    </div>
 
 
    <?php
}
