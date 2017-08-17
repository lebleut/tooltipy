<?php
					//the tooltip display
						//tooltip content vars
						$test_name='Keywords ToolTip Generator';
						$test_dfn= __('this plugin allows you easely create tooltips for your technical keywords.','bluet-kw')
									.'<br>'
									.__('Click','bluet-kw')
									.'<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/bluet-keywords-tooltip-generator">'
									.__('Here','bluet-kw')
									.'</a>'
									.__('to rate our plugin if you appreciate it','bluet-kw')
									.';) ..';
						$test_img='<img height="auto !important" width="300" src="http://plugins.svn.wordpress.org/bluet-keywords-tooltip-generator/assets/banner-772x250.png" class="attachment-medium wp-post-image" alt="wp-hooks-guide">';
						$test_id=111;
						
						//displaying the tooltip
						echo('<div id="tooltip_blocks_to_show">');
							echo(bluet_kttg_tooltip_layout($test_name,$test_dfn,$test_img,$test_id));
						echo('</div>');
						?>
							<script>
							jQuery(document).ready(function(){
								jQuery.event.trigger("keywordsLoaded");
							});								
							</script>
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
									
							<div id="bluet_kw_preview" style="background-color: rgb(211, 211, 211);  width: 75%;  padding: 15px;  border-radius: 10px; display:none;">
								<h3 style="margin-bottom: 12px;  margin-top: 0px;"><?php _e('Preview','bluet-kw'); ?> :</h3>
								<?php _e('Pass your mouse over the word','bluet-kw'); ?>
								<span class="bluet_tooltip" data-tooltip-id="111">KTTG</span> <?php _e('to test the tooltip layout.','bluet-kw'); ?>
							</div>				
