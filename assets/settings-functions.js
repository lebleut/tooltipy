jQuery(document).ready(function(){

	if(jQuery('.bluet_tooltip').length==0)
	return;

	bleutExcludeKwStyle();	

	//add listener to checkboxes
	jQuery("#bluet_kw_admin_div_terms li input").each(function(ind){
		jQuery(this).change(function(){
			bleutExcludeKwStyle();
		});
	});
	hideIfChecked('bluet_kw_admin_exclude_post_from_matching_id','bluet_kw_admin_div_terms');

	//array contains tabs to show
	var bluet_tab=['bluet_style_tab','bluet_settings_tab','bluet_excluded_tab','bluet_glossary_tab','bluet_advanced_tab'];

	for(var i=0;i<bluet_tab.length;i++){

		//remove active class from all elements
		jQuery('#'+bluet_tab[i]).removeClass('nav-tab-active');
		
		var tabular=document.getElementById(bluet_tab[i]).addEventListener('click',function(e){
			for(var i=0;i<bluet_tab.length;i++){
				jQuery('#'+bluet_tab[i]).removeClass('nav-tab-active');

			}
			
			//tab we want to show
			var tabToShow=e.target.dataset.tab
			
			bluetShowTab(tabToShow);
			
			jQuery(e.target).addClass('nav-tab-active');
		},false);
	}

	//begin by displaying the style div
	bluetShowTab("bluet-section-style");
	jQuery('#bluet_style_tab').addClass('nav-tab-active');
	
	//
	bluet_hide_bg();		
	document.getElementById("bluet_kw_no_background").addEventListener("change",bluet_hide_bg,false);
		
	for(var i=0;i<document.getElementsByClassName('wp-picker-holder').length;i++){
		document.getElementsByClassName('wp-picker-holder')[i].addEventListener('mousemove',function(e){
			
			if(!document.getElementById("bluet_kw_no_background").checked){
				var bluet_keyword_bg=document.getElementsByName('bluet_kw_style[bt_kw_tt_bg_color]')[0].value;
			}else{
				var bluet_keyword_bg='initial';
			}
			
			var bluet_keyword_color=document.getElementsByName('bluet_kw_style[bt_kw_tt_color]')[0].value;
			var bluet_tooltip_bg=document.getElementsByName('bluet_kw_style[bt_kw_desc_bg_color]')[0].value;
			var bluet_tooltip_color=document.getElementsByName('bluet_kw_style[bt_kw_desc_color]')[0].value;
			
			document.getElementsByClassName('bluet_tooltip')[0].style.backgroundColor=bluet_keyword_bg;
			document.getElementsByClassName('bluet_tooltip')[0].style.color=bluet_keyword_color;
			document.getElementsByClassName('bluet_block_container')[0].style.backgroundColor=bluet_tooltip_bg;
			document.getElementsByClassName('bluet_block_container')[0].style.boxShadow="0px 0px 10px "+bluet_tooltip_bg;
			document.getElementsByClassName('bluet_block_container')[0].style.color=bluet_tooltip_color;
		},false);
	}
	
	//fetch mode
	//init
	if(jQuery("#bt_kw_fetch_mode-icon").is(":checked")){
	   jQuery("#tooltip_highlight_fetch_mode").hide();		
	}
	//listeners
	jQuery("#bt_kw_fetch_mode-highlight").change(function(){
	   jQuery("#tooltip_highlight_fetch_mode").show();
	});
	
	jQuery("#bt_kw_fetch_mode-icon").change(function(){
	   jQuery("#tooltip_highlight_fetch_mode").hide();
	});	
});
/**/

//
function bluetShowTab(tabId){
	var mybluet_my_div_settings=document.getElementById('bluet-sections-div');
	var mybluet_children=mybluet_my_div_settings.childNodes;

	for(var i=0;i<mybluet_children.length-1;i++){
		mybluet_children[i].style.display='none';
	}
	
	document.getElementById(tabId).style.display='block';
}

function bluet_hide_bg(){
		elem=document.getElementsByClassName('bluet_tooltip')[0];
		txt_color=elem.style.color;
	if(document.getElementById("bluet_kw_no_background").checked){
		elem.style.backgroundColor='initial';		
		elem.style.borderBottom =txt_color+" 1px dotted";
		elem.style.borderRadius="0px";

		document.getElementById('bluet_kw_bg_hide').style.display='none';
	}else{
		elem.style.backgroundColor=document.getElementsByName('bluet_kw_style[bt_kw_tt_bg_color]')[0].value;
		document.getElementById('bluet_kw_bg_hide').style.display='block';
		elem.style.borderBottom="0px";
	}
}

//for the edit page 

function hideIfChecked(myId,idToDeal){
	if(jQuery("#"+myId).attr('checked')){
		jQuery("#"+idToDeal).hide();
	}else{
		jQuery("#"+idToDeal).show();
	}
}

function bleutExcludeKwStyle(){
	//if no checkbox is checked exit
	checked_ones=0;
	jQuery("#bluet_kw_admin_div_terms li input").each(function(ind){
		if(jQuery(this).attr("checked"))
			checked_ones++;
	});
	
	if(checked_ones < 1){
		jQuery("#bluet_kw_admin_div_terms li").css("text-decoration","initial");
		return;
	}

   jQuery("#bluet_kw_admin_div_terms li").css("text-decoration","line-through")
   
   jQuery("#bluet_kw_admin_div_terms li").each(function(ind){
		if(jQuery("#bluet_kw_admin_div_terms li input").eq(ind).attr("checked")){
			jQuery("#bluet_kw_admin_div_terms li").eq(ind).css("text-decoration","initial");
		}
   })
}


var easy_tags={
/*Easy_tags an object contains function to perform easy tags (dinamic add tags)*/

	delimiter:" ", //dilimiteur par defaut

	construct:function(deli){
		this.delimiter=deli;
		return this;
	},

	add_to_send:function(element){
		var field=jQuery(element).find(".easy_tags-field");
		var to_send=jQuery(element).find(".easy_tags-to_send");
		var add=jQuery(element).find(".easy_tags-add");
		var list=jQuery(element).find(".easy_tags-list");

		var res="";

		list.find('.elem_class').each(function(index){			
			res+=jQuery(this).find('.class_val').html()+easy_tags.delimiter;
		});

		to_send.val(res);
	},


	init:function(element_class){
		var element=jQuery(element_class);

		element.each(function(index){
			var field=jQuery(this).find(".easy_tags-field");
			var to_send=jQuery(this).find(".easy_tags-to_send");
			var add=jQuery(this).find(".easy_tags-add");
			var list=jQuery(this).find(".easy_tags-list");

			var tab_tmp=to_send.val().split(easy_tags.delimiter);
			for(var i=0;i<tab_tmp.length;i++){
				if(tab_tmp[i]!=""){
					elem=document.createElement("span");
					elem.className="elem_class";
					elem.innerHTML="<a class='ntdelbutton' onclick='sup_elem=jQuery(this).parent().parent().parent().get(); jQuery(this).parent().remove(); easy_tags.add_to_send(sup_elem,\""+easy_tags.delimiter+"\");'>X</a> <span class='class_val'>"+tab_tmp[i]+"</span>";
					
					list.append(elem);  
				}
			}
		});

		//delete last classes if field empty and delete
		jQuery(".easy_tags-field").keydown(function(e){
		  if(jQuery(this).val()=="" && e.keyCode==8){//backspace keyCode : 8
		    jQuery(this).parent().parent().parent().find('.elem_class').last().remove();
		    easy_tags.add_to_send(jQuery(this).parent().get(),easy_tags.delimiter);
		  }
		});
	},

	fill_classes:function(element_class){
		var element=jQuery(element_class);

		element.each(function(index){
			var field=jQuery(this).find(".easy_tags-field");
			var to_send=jQuery(this).find(".easy_tags-to_send");
			var add=jQuery(this).find(".easy_tags-add");
			var list=jQuery(this).find(".easy_tags-list");

				add.click(function(){
				   // user has pressed space
				  if(field.val().trim()!=""){
					elem=document.createElement("span");
					elem.className="elem_class";
					elem.innerHTML="<a class='ntdelbutton' onclick='sup_elem=jQuery(this).parent().parent().parent().get(); jQuery(this).parent().remove(); easy_tags.add_to_send(sup_elem,\""+easy_tags.delimiter+"\");'>X</a> <span class='class_val'>"+field.val().trim()+"</span>";
				
					list.append(elem);        
				  }

				  field.val("");
				  easy_tags.add_to_send(jQuery(this).parent().get(),easy_tags.delimiter);
				  field.focus();
				});
		});
		
	}
};
/**/