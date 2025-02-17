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

namespace App\Services\Auth\Traits\Verification;

use App\Notifications\PhoneVerification;
use Illuminate\Http\JsonResponse;
use Throwable;

trait PhoneVerificationTrait
{
	/**
	 * SMS: Re-send code
	 *
	 * Re-send mobile phone verification token by SMS
	 *
	 * @param string $entityMetadataKey
	 * @param int|string $entityId
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function reSendPhoneVerification(string $entityMetadataKey, int|string $entityId, array $params = []): JsonResponse
	{
		$data = [];
		$data['success'] = true;
		
		$extra = [];
		$extra['phoneVerificationSent'] = true;
		
		// Get the entity metadata
		$entityMetadata = $this->getEntityMetadata($entityMetadataKey);
		
		if (empty($entityMetadata)) {
			return apiResponse()->notFound(sprintf($this->metadataNotFoundMessage, $entityMetadataKey));
		}
		
		// Get entity by ID
		$model = $entityMetadata['model'];
		$object = $model::query()->withoutGlobalScopes($entityMetadata['scopes'])->where('id', $entityId)->first();
		
		if (empty($object)) {
			return apiResponse()->notFound(t('Entity ID not found'));
		}
		
		// Check if the Phone is already verified
		if (!empty($object->phone_verified_at)) {
			
			$data['success'] = false;
			$data['message'] = t('Your field is already verified', ['field' => t('phone_number')]);
			
			// Remove Notification Trigger
			$extra['phoneVerificationSent'] = false;
			
		} else {
			
			// Re-Send the confirmation
			$data = $this->sendPhoneVerification($entityMetadataKey, $object, false, $params);
			
			if (data_get($data, 'success')) {
				if (isAdminPanel()) {
					$message = t('The activation code has been sent to the user to verify his phone number');
				} else {
					$message = t('The activation code has been sent to you to verify your phone number');
				}
				
				$data['message'] = $message;
				
				// Remove Notification Trigger
				$extra['phoneVerificationSent'] = false;
			}
			
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
	
	/**
	 * SMS: Send code (It's not an endpoint)
	 * Send mobile phone verification token by SMS
	 *
	 * @param string $entityMetadataKey
	 * @param $object
	 * @param bool $displayFlashMessage
	 * @param array $params
	 * @return array
	 */
	protected function sendPhoneVerification(string $entityMetadataKey, $object, bool $displayFlashMessage = true, array $params = []): array
	{
		$languageCode = $params['languageCode'] ?? null;
		$languageCode = $params['locale'] ?? $languageCode;
		$languageCode = (!empty($languageCode) && array_key_exists($languageCode, getSupportedLanguages()))
			? $languageCode
			: null;
		
		$data = []; // No $extra here.
		
		$data['success'] = true;
		$data['phoneVerificationSent'] = false;
		
		// Get the entity metadata
		$entityMetadata = $this->getEntityMetadata($entityMetadataKey);
		
		if (empty($entityMetadata) || empty($object)) {
			$message = empty($entityMetadata)
				? sprintf($this->metadataNotFoundMessage, $entityMetadataKey)
				: t('Entity ID not found');
			
			$data['success'] = false;
			$data['message'] = $message;
			
			return $data;
		}
		
		// Send Confirmation Email
		try {
			if (!empty($languageCode)) {
				$object->notify((new PhoneVerification($object, $entityMetadata))->locale($languageCode));
			} else {
				$object->notify(new PhoneVerification($object, $entityMetadata));
			}
			
			if ($displayFlashMessage) {
				$message = t('An activation code has been sent to you to verify your phone number');
				
				$data['success'] = true;
				$data['message'] = $message;
			}
			
			$data['phoneVerificationSent'] = true;
			
			return $data;
		} catch (Throwable $e) {
			$message = replaceNewlinesWithSpace($e->getMessage());
			
			$data['success'] = false;
			$data['message'] = $message;
			
			return $data;
		}
	}
}
