
//for displaying tooltip on images
/*function to filter all images in the document*/
(function bluetImgAltTooltipAll(){
	var all_imgs=document.getElementsByTagName("img");
	for(var i=0;i<all_imgs.length;i++){
		bluetImgAltTooltip(all_imgs[i]);
	}
})()

function bluetImgAltTooltip(imgElem){
	var haveTooltipElem=false;

	if(imgElem.nextElementSibling){
		var classList = imgElem.nextElementSibling.className.split(' ');
		for(i=0;i<classList.length;i++){
			if(classList[i]=="bluet_tooltip_alt"){
				haveTooltipElem=true; break;
			}
		}
	}

	/*add elem if do not exists*/
	if(!haveTooltipElem){
		var elemToAdd='<div class="bluet_tooltip_alt" style="left:0;opacity:0;">rien</div>';

		var div = document.createElement('div');
		div.innerHTML = elemToAdd;
		var newItem = div.childNodes[0];

		imgElem.parentNode.insertBefore(newItem,imgElem.nextSibling);
	}
	
/*process*/
	imgElem.addEventListener("mouseenter",function(){
	  var tooltipElem=imgElem.nextElementSibling;
	  var rectLeft=imgElem.offsetLeft;
	  var wd=imgElem.offsetWidth;

	  tooltipElem.innerHTML =imgElem.alt;
		
	  var newLeft=(rectLeft+(wd/2)-(tooltipElem.offsetWidth)/2);

	  //change style
	  tooltipElem.style.left= newLeft+"px";
	  tooltipElem.style.opacity="1.0";
	  tooltipElem.style.display="block";

	  //disappears when the mouse leaves the img
	  imgElem.addEventListener("mouseleave",function(){tooltipElem.style.display='none';});

	});
  
}