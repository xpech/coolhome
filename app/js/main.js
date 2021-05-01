

jQuery(function(){
	$('button.btn-danger').click(function(evt){
		var msg = $(this).attr('messsage');
		if (msg === undefined) msg = 'Attention : suppression définitive ?';

		if (confirm(msg))
		{
			return true;

		} else {
			evt.stopPropagation();
			return false;
		}

	})


})