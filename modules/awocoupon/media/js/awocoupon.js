

function customFormSubmit(action,elems) {
    var newForm = jQuery('<form>', {
		'action': action
		,'method': 'post'
		//,'target': '_top'
    }).appendTo('body');
	
	for(i=0; i<elems.length; i++) {
		newForm.append(jQuery('<input>', {
			'name': elems[i].name,
			'value': elems[i].value,
			'type': 'hidden'
			})
		);
	}
    newForm.submit();
}
