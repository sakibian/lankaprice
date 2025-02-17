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

use Illuminate\Notifications\Messages\VonageMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioMessage;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PhoneVerification extends BaseNotification
{
	protected ?object $object;
	protected ?array $entityMetadata;
	
	public function __construct(?object $object, ?array $entityMetadata)
	{
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
		
		return (empty($this->object->phone_verified_at) && !empty($this->object->phone_token));
	}
	
	protected function determineViaChannels($notifiable): array
	{
		if (config('settings.sms.driver') == 'twilio') {
			return [TwilioChannel::class];
		}
		
		return ['vonage'];
	}
	
	public function toVonage($notifiable): VonageMessage
	{
		return (new VonageMessage())->content($this->getSmsMessage())->unicode();
	}
	
	public function toTwilio($notifiable): TwilioSmsMessage|TwilioMessage
	{
		return (new TwilioSmsMessage())->content($this->getSmsMessage());
	}
	
	// PRIVATE
	
	private function getSmsMessage(): string
	{
		$token = $this->object->phone_token;
		
		$path = 'verify/' . $this->entityMetadata['key'] . '/phone/' . $token;
		$tokenUrl = (config('plugins.domainmapping.installed'))
			? dmUrl($this->object->country_code, $path)
			: url($path);
		
		$msg = trans('sms.phone_verification_content', [
			'appName'  => config('app.name'),
			'token'    => $token,
			'tokenUrl' => $tokenUrl,
		]);
		
		return getAsString($msg);
	}
}
