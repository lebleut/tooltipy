<p style="color:red;"><?php _e("Available only on the pro version.","bluet-kw"); ?></p>
<div class="bluet-section" id="" name="" style="display: block; opacity: 0.75;">
<h3>Advance settings for KTTG :</h3>
<div id="">Advanced space.</div><table class="form-table"><tbody><tr><th scope="row">Cover areas</th><td>	<div class="easy_tags">		
		<div class="easy_tags-content" onclick="jQuery('#bluet_cover_areas_id').focus()"> <!-- content -->
			<div class="easy_tags-list tagchecklist" id="">	<!-- list before field -->
			</div>
			
			<input disabled class="easy_tags-field" type="text" style="max-width:250px;" id="" placeholder="class ..."> <!-- field -->
				<input disabled class="easy_tags-to_send" type="hidden" name="" id="" value=""> <!-- hidden text to send -->
		</div>
		<input disabled class="easy_tags-add button tagadd" type="button" value="Add" id=""> <!-- add button -->
	</div>
	
	<p style="color:green;">Choose CSS classes to cover with tooltips</p>

	<p><b>NB : </b>
		<i>
		Please avoid overlapped classes !<br> 
		If you leave it blank the whole page will be affected	</i></p>
	

	</td></tr><tr><th scope="row">Exclude areas</th><td>	<div class="easy_tags">
		<div class="easy_tags-content" onclick="jQuery('#bluet_exclude_areas_id').focus()">
			<div class="easy_tags-list tagchecklist" id="">
			</div>
			
			<input disabled class="easy_tags-field" type="text" style="max-width:250px;" id="" placeholder="class ...">
				<input disabled class="easy_tags-to_send" type="hidden" name="" id="" value="">
		</div>
		<input disabled class="easy_tags-add button tagadd" type="button" value="Add" id="">
	</div>

	<p style="color:red;">Choose CSS classes to exclude</p>

	</td></tr><tr><th scope="row">Exclude links ?</th><td>	<input disabled id="" type="checkbox" name="" checked="">
	<label for="bluet_exclude_anchor_tags">Links</label>
	</td></tr><tr><th scope="row">Exclude Headings ?</th><td><div id="kttg_exclude_headings_zone">	
		<input disabled id="bluet_exclude_heading_H1" type="checkbox" name="">
		<label for="bluet_exclude_heading_H1"><h1>H1</h1></label>
		<br>
		
		<input disabled id="bluet_exclude_heading_H2" type="checkbox" name="" checked>
		<label for="bluet_exclude_heading_H2"><h2>H2</h2></label>
		<br>
		
		<input disabled id="bluet_exclude_heading_H3" type="checkbox" name="">
		<label for="bluet_exclude_heading_H3"><h3>H3</h3></label>
		<br>
		
		<input disabled id="bluet_exclude_heading_H4" type="checkbox" name="">
		<label for="bluet_exclude_heading_H4"><h4>H4</h4></label>
		<br>
		
		<input disabled id="bluet_exclude_heading_H5" type="checkbox" name="">
		<label for="bluet_exclude_heading_H5"><h5>H5</h5></label>
		<br>
		
		<input disabled id="bluet_exclude_heading_H6" type="checkbox" name="">
		<label for="bluet_exclude_heading_H6"><h6>H6</h6></label>
		<br>
	</div></td></tr><tr><th scope="row">Advanced Style</th><td>	<input disabled id="bluet_apply_custom_style_sheet" type="checkbox" name="">
	<label for="bluet_apply_custom_style_sheet">Apply custom style sheet</label>
	<br><input disabled style="min-width:250px;" id="bluet_custom_style_sheet" type="text" name="" value="" placeholder="CSS URL Here">
	</td></tr><tr><th scope="row">Load all keywords</th><td>        <input disabled id="bluet_fetch_all_keywords" type="checkbox" name="">
        <label for="bluet_fetch_all_keywords">(use only if needed to load all keywords per page)</label>
    </td></tr><tr><th scope="row">Events to fetch</th><td>		<input disabled type="text" id="kttg_custom_events_id" style="min-width:300px;" placeholder="Events names saparated with ','" name="" value="">
     </td></tr></tbody></table>
	 </div>