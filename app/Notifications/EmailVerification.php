<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class EmailVerification extends BaseNotification
{
	protected ?object $object;
	protected ?array $entityMetadata;
	
	public function __construct(object|int|string $object, ?array $entityMetadata)
	{
		$model = $entityMetadata['model'] ?? null;
		$scopes = $entityMetadata['scopes'] ?? [];
		
		if (!is_object($object)) {
			$object = !empty($model)
				? $model::query()->withoutGlobalScopes($scopes)->find($object)
				: null;
		}
		
		$this->object = $object;
		$this->entityMetadata = $entityMetadata;
	}
	
	protected function shouldSendNotificationWhen($notifiable): bool
	{
		if (empty($this->object) || empty($this->entityMetadata)) {
			return false;
		}
		
		if (!isset($this->entityMetadata['nameColumn'])) {
			return false;
		}
		
		return (empty($this->object->email_verified_at) && !empty($this->object->email_token));
	}
	
	protected function determineViaChannels($notifiable): array
	{
		return ['mail'];
	}
	
	public function toMail($notifiable): MailMessage
	{
		$token = $this->object->email_token;
		
		$path = 'verify/' . $this->entityMetadata['key'] . '/email/' . $token;
		$verificationUrl = (config('plugins.domainmapping.installed'))
			? dmUrl($this->object->country_code, $path)
			: url($path);
		
		return (new MailMessage)
			->subject(trans('mail.email_verification_title'))
			->greeting(trans('mail.email_verification_content_1', ['userName' => $this->object->{$this->entityMetadata['nameColumn']}]))
			->line(trans('mail.email_verification_content_2'))
			->action(trans('mail.email_verification_action'), $verificationUrl)
			->line(trans('mail.email_verification_content_3', ['appName' => config('app.name')]))
			->salutation(trans('mail.footer_salutation', ['appName' => config('app.name')]));
	}
}
