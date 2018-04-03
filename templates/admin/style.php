<?php
										
	//tooltip settings
	echo('<div id="tooltip_settings_sections">');
		do_settings_sections( 'my_keywords_style' );	
	echo('</div>');

	//tooltip highlight fetch mode settings
	echo('<div id="tooltip_highlight_fetch_mode">');
		do_settings_sections( 'my_highlight_fetch_mode' );	
	echo('</div>');
	?>										
		<div id="bluet_kw_preview" style="display:none; background-color: rgb(211, 211, 211);  width: 75%;  padding: 15px;  border-radius: 10px;">
			<h3 style="margin-bottom: 12px;  margin-top: 0px;"><?php _e('Preview','bluet-kw'); ?> :</h3>
			<?php _e('Pass your mouse over the word','bluet-kw'); ?>
			<span class="bluet_tooltip" data-tooltip="111">KTTG</span> <?php _e('to test the tooltip layout.','bluet-kw'); ?>
		</div>				
