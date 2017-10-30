<?php
//exclude
				?>				
					<div id="bluet_kw_excluded_posts">
						<h3><?php _e('Excluded posts','bluet-kw');?></h3>
						<p><?php _e('Posts which are excluded from being matched','bluet-kw');?></p>
						<?php
						$excluded_posts=bluet_kw_fetch_excluded_posts();

						if(empty($excluded_posts)){ 
							echo('<p style="color:red;">');
							_e('No posts or pages are excluded','bluet-kw');
							echo('</p>');
						}else{							
							echo('<ul style="list-style: initial; padding-left: 25px;">');
							foreach($excluded_posts as $k=>$excluded_post){
								// get permalink from post id
								$excluded_post['permalink'] = get_permalink($excluded_post['id']);
								?>
								<li><a href="<?php echo $excluded_post['permalink']; ?>"><?php echo $excluded_post['title']; ?></a></li>
								<?php
							}
							echo("</ul>");
						}