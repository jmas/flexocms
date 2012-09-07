cms.init.add(['page_edit', 'page_add'], function()
{
	
	$('#PFAddButton').click(function()
	{
		var html = '<form class="dialog-form" onsubmit="return false;">'+
		'<p><label>'+__('Field name')+'</label><span><input class="input-text" type="text" name="field_name" /></span></p>'+
		'</form>';
		
		var buttons = {};
		
		buttons[__('Add')] = function()
		{
			var field_name = $(this).find('input[name="field_name"]').val().toLowerCase()
				.replace(/[^a-z0-9\-\_]/g, '_')
				.replace(/ /g,      '_')
				.replace(/_{2,}/g,  '_')
				.replace(/^_/,      '' )
				.replace(/_$/,      '' );
			
			if ( field_name == '' )
			{
				alert(__('Please, enter field name!'));
			}
			else
			{
				var rand_number = Math.ceil(Math.random()*2);
				
				var field_html = '<li>'
								+'<label for="PFField-'+rand_number+'">'+field_name+'</label>'
								+'<span><textarea id="PFField-'+rand_number+'" class="pf-field-textarea" name="pf_fields['+field_name+']"></textarea></span>'
								+'<a class="pf-remove-link" href="javascript:;" title="'+ __('Remove') +'"><img src="images/remove.png" /></a>'
								+'</li>';
				
				$('#PFList').append(field_html);
				
				$(this).dialog('close');
			}
		};
		
		buttons[__('Cancel')] = function()
		{
			$(this).dialog('close');
		};
		
		var $dialog = $(html).dialog({
			width:     235,
			modal:     true,
			buttons:   buttons,
			resizable: false,
			title:     __('Creating field')
		});
		
		$dialog.find('input[name="field_name"]')
			.keyup(function(){
				$(this).val( cms.convertSlug($(this).val()).replace(/[^a-z0-9\-\_]/, '') );
			});
		
		return false;
	});
	
	$('#PFList .pf-remove-link').live('click', function()
	{
		$(this).parent().remove();
		
		return false;
	});
	
});