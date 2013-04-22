
//A very simple JS function to replace slots for demo purposes.
function replaceAttr(replace, attrTypes) {
	if (typeof(attrTypes) === 'undefined') attrTypes = ['src','value','alt','href','title'];
	for(var i in attrTypes) {
		var query = '['+attrTypes[i]+']';
		var elements = document.querySelectorAll(query);
		for(var j=0; j<elements.length; j++) {
			var element = elements.item(j);
			var currentValue = element.getAttribute(attrTypes[i]);
			for(var k in replace) {
				if (currentValue === k) {
					element.setAttribute(attrTypes[i], replace[k]);
					break;			
				}
			}
		}
	}
}
(function(){
	var elements = document.getElementsByTagName('script');
	for(var i=0; i<elements.length; i++) {
		if (elements.item(i).getAttribute('src').indexOf('demo.js')>-1) {
			eval('replaceAttr('+elements.item(i).innerHTML+');');	
		}
	}
})()

