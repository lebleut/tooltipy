(function(){
    tinymce.create('tinymce.plugins.BleutKttgForImages', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            ed.addCommand('bluet_kttg_img', function() {
                var selected_text = ed.selection.getContent();

				if(jQuery(selected_text).prop("tagName")=="IMG"){
					selected_text="<p>"+selected_text+"</p>"; //i added p tag as a parent so that the .html() function works as I expect
					var img_obj=jQuery(selected_text);
					
					var editor_doc=ed.dom.doc.children;

					var old_kw=img_obj.children().first().attr("alt").split("KTTG: ")[1];
					var newKw=prompt("type the KeyWord for which you want to create a toltip :",old_kw);
					if(newKw!=null){ //if not cancel
						if(newKw!=""){
							img_obj.children().first().attr("alt","KTTG: "+newKw);
							
							//eliminates old and new elems first
							jQuery(editor_doc).find("#bluet_kw_img_tt_"+newKw).first().remove();
							jQuery(editor_doc).find("#bluet_kw_img_tt_"+old_kw).first().remove();
							
							img_obj.children().first().parent().append( "<span id='bluet_kw_img_tt_"+newKw+"' style='display:none;'>"+newKw+"</span>");
						}else{
							jQuery(editor_doc).find("#bluet_kw_img_tt_"+old_kw).first().remove();
							img_obj.children().first().attr("alt","");
						}
					}

					
					var return_text=img_obj.html();
					ed.execCommand('mceInsertContent', 0, return_text);
				}else{
					alert("Please select a picture so you can associate a Keyword tooltip.");
				}
            });
            
            ed.addButton('bluet_kttg_img', {
                title : 'Associate tooltip to image',
                cmd : 'bluet_kttg_img',
                image : url + '/ico_16x16.png'
            });

        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                    longname : 'Bleut Kttg For Images',
                    author : 'lebleut',
                    authorurl : '#',
                    infourl : '#',
                    version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('bluetKFI', tinymce.plugins.BleutKttgForImages);
})();
