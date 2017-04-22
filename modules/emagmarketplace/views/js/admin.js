// JavaScript Document

function testEmagMarketplaceConnection(token)
{
	doAdminAjax({
		"action": "TestConnection",
		"token": token,
		"controller": "AdminEmagMarketplaceConfig",
		"ajax": 1,
		"EMAGMP_API_URL": $('#configuration_form [name="EMAGMP_API_URL"]').val(),
		"EMAGMP_VENDORCODE": $('#configuration_form [name="EMAGMP_VENDORCODE"]').val(),
		"EMAGMP_VENDORUSERNAME": $('#configuration_form [name="EMAGMP_VENDORUSERNAME"]').val(),
		"EMAGMP_VENDORPASSWORD": $('#configuration_form [name="EMAGMP_VENDORPASSWORD"]').val()
	});
}

function downloadEmagMarketplaceLocalities(token)
{
	if (!confirm('This might be a lenghty operation, so please wait until it is completed!'))
		return;
		
	doAdminAjax({
		"action": "DownloadEmagLocalities",
		"token": token,
		"controller": "AdminEmagMarketplaceConfig",
		"ajax": 1
	}, function(data)
	{
		data = $.parseJSON(data);
		if (data.confirmations.length != 0)
		{
			showSuccessMessage(data.confirmations, 900000);
			alert('Operation completed successfully! Please choose your AWB sender locality now!');
		}
		else
			showErrorMessage(data.error, 900000);
	});
}

function downloadEmagMarketplaceCategories(token)
{
	if (!confirm('This might be a lenghty operation, so please wait until it is completed!'))
		return;
		
	doAdminAjax({
		"action": "DownloadEmagCategories",
		"token": token,
		"controller": "AdminEmagMarketplaceCategories",
		"ajax": 1
	}, function(data)
	{
		data = $.parseJSON(data);
		if (data.confirmations.length != 0)
		{
			showSuccessMessage(data.confirmations, 900000);
			alert('Operation completed successfully! This page will now reload and you can start mapping your existing categories.');
			this.location.href = this.location.href;
		}
		else
			showErrorMessage(data.error, 900000);
	});
}

function updateEmagMarketplaceCategory(id_category, token)
{
	doAdminAjax({
		"action": "UpdateEmagCategoryID",
		"token": token,
		"controller": "AdminEmagMarketplaceCategories",
		"ajax": 1,
		"id_category": id_category,
		"emag_category_label": $('[name="emag_category_id\\['+id_category+'\\]"]').val()
	}, function(data) {
		data = $.parseJSON(data);
		if(data.confirmations.length != 0)
		{
			showSuccessMessage(data.confirmations);
			if (data.content)
			{
				parent = $('[name="emag_family_type_id\\['+id_category+'\\]"]').parent();
				parent[0].innerHTML = data.content;
			}
		}
		else
			showErrorMessage(data.error);
	});
}

function updateEmagMarketplaceFamilyType(id_category, token)
{
	doAdminAjax({
		"action": "UpdateEmagFamilyTypeID",
		"token": token,
		"controller": "AdminEmagMarketplaceCategories",
		"ajax": 1,
		"id_category": id_category,
		"emag_family_type_id": $('[name="emag_family_type_id\\['+id_category+'\\]"]').val()
	});
}

function updateEmagMarketplaceCommission(id_category, token)
{
	doAdminAjax({
		"action": "UpdateEmagCommission",
		"token": token,
		"controller": "AdminEmagMarketplaceCategories",
		"ajax": 1,
		"id_category": id_category,
		"commission": $('[name="commission\\['+id_category+'\\]"]').val()
	});
}

function updateEmagMarketplaceFeatureID(emag_characteristic_id, emag_category_id, token)
{
	doAdminAjax({
		"action": "UpdateEmagFeatureID",
		"token": token,
		"controller": "AdminEmagMarketplaceCharacteristics",
		"ajax": 1,
		"emag_characteristic_id": emag_characteristic_id,
		"emag_category_id": emag_category_id,
		"id_feature": $('[name="id_feature\\['+emag_characteristic_id+'\\]\\['+emag_category_id+'\\]"]').val()
	});
}

function updateEmagMarketplaceAttributeGroupID(emag_characteristic_id, emag_category_id, token)
{
	doAdminAjax({
		"action": "UpdateEmagAttributeGroupID",
		"token": token,
		"controller": "AdminEmagMarketplaceCharacteristics",
		"ajax": 1,
		"emag_characteristic_id": emag_characteristic_id,
		"emag_category_id": emag_category_id,
		"id_attribute_group": $('[name="id_attribute_group\\['+emag_characteristic_id+'\\]\\['+emag_category_id+'\\]"]').val()
	});
}

function uploadEmagMarketplaceProducts(token, url, source)
{
	if (source == 'wizard')
	{
		confirm_message = 'This is the final step of your configuration wizard. All products and offers, from the categories you have mapped, will be uploaded to eMAG Marketplace. Do you want to start uploading now?';
		success_message = 'Your products and offers have been added to an upload queue! They will be uploaded one by one, in the background. The next page will show you the progress of the upload queue!';
	}
	else
	{
		confirm_message = 'All products and offers, from the categories you have mapped, will be re-uploaded to eMAG Marketplace. Do you want to start uploading now?'
		success_message = 'Your products and offers have been added to the upload queue! They will be uploaded one by one, in the background. This page will reload, to show you the progress of the upload queue!';
	}
		
	if (!confirm(confirm_message))
		return;
		
	doAdminAjax({
		"action": "UploadProducts",
		"token": token,
		"controller": "AdminEmagMarketplaceMain",
		"ajax": 1
	}, function(data)
	{
		data = $.parseJSON(data);
		if (data.confirmations.length != 0)
		{
			showSuccessMessage(data.confirmations, 900000);
			alert(success_message);
			this.location.href = url;
		}
		else
			showErrorMessage(data.error, 900000);
	});
}