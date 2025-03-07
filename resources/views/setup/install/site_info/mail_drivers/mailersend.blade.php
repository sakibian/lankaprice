<div class="row row-cols-2 mailersend">
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('messages.mailersend_api_key'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailersend_api_key]',
			'value'    => data_get($siteInfo, 'settings.mail.mailersend_api_key'),
			'required' => true,
		])
	</div>
	<div class="col">
		@include('setup.install.helpers.form_control', [
			'label'    => trans('admin.mail_email_sender_label'),
			'type'     => 'text',
			'name'     => 'settings[mail][mailersend_email_sender]',
			'value'    => data_get($siteInfo, 'settings.mail.mailersend_email_sender', data_get($siteInfo, 'user.email')),
			'hint'     => trans('admin.mail_email_sender_hint'),
			'required' => false,
		])
	</div>
</div>
