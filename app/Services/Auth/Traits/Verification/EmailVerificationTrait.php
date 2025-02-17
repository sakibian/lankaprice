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

use App\Notifications\EmailVerification;
use Illuminate\Http\JsonResponse;
use Throwable;

trait EmailVerificationTrait
{
	/**
	 * Email: Re-send link
	 *
	 * Re-send email verification link to the user
	 *
	 * @param string $entityMetadataKey
	 * @param int|string $entityId
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function reSendEmailVerification(string $entityMetadataKey, int|string $entityId, array $params = []): JsonResponse
	{
		$data = [];
		$data['success'] = true;
		
		$extra = [];
		$extra['emailVerificationSent'] = true;
		
		// Get the entity metadata
		$entityMetadata = $this->getEntityMetadata($entityMetadataKey);
		
		if (empty($entityMetadata)) {
			return apiResponse()->notFound(sprintf($this->metadataNotFoundMessage, $entityMetadataKey));
		}
		
		// Get Entity by ID
		$model = $entityMetadata['model'];
		$object = $model::query()->withoutGlobalScopes($entityMetadata['scopes'])->where('id', $entityId)->first();
		
		if (empty($object)) {
			return apiResponse()->notFound(t('Entity ID not found'));
		}
		
		// Check if the Email is already verified
		if (!empty($object->email_verified_at)) {
			
			$data['success'] = false;
			$data['message'] = t('Your field is already verified', ['field' => t('email_address')]);
			
			// Remove Notification Trigger
			$extra['emailVerificationSent'] = false;
			
		} else {
			
			// Re-Send the confirmation
			$data = $this->sendEmailVerification($entityMetadataKey, $object, false, $params);
			
			if (data_get($data, 'success')) {
				if (isAdminPanel()) {
					$message = t('The activation link has been sent to the user to verify his email address');
				} else {
					$message = t('The activation link has been sent to you to verify your email address');
				}
				
				$data['message'] = $message;
				
				// Remove Notification Trigger
				$extra['emailVerificationSent'] = false;
			}
			
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Email: Send link (It's not an endpoint)
	 * Send email verification link to the user
	 *
	 * @param string $entityMetadataKey
	 * @param $object
	 * @param bool $displayFlashMessage
	 * @param array $params
	 * @return array
	 */
	protected function sendEmailVerification(string $entityMetadataKey, $object, bool $displayFlashMessage = true, array $params = []): array
	{
		$languageCode = $params['languageCode'] ?? null;
		$languageCode = $params['locale'] ?? $languageCode;
		$languageCode = (!empty($languageCode) && array_key_exists($languageCode, getSupportedLanguages()))
			? $languageCode
			: null;
		
		$data = []; // No $extra here.
		
		$data['success'] = true;
		$data['emailVerificationSent'] = false;
		
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
				$object->notify((new EmailVerification($object, $entityMetadata))->locale($languageCode));
			} else {
				$object->notify(new EmailVerification($object, $entityMetadata));
			}
			
			if ($displayFlashMessage) {
				$message = t('An activation link has been sent to you to verify your email address');
				
				$data['success'] = true;
				$data['message'] = $message;
			}
			
			$data['emailVerificationSent'] = true;
			
			return $data;
		} catch (Throwable $e) {
			$message = replaceNewlinesWithSpace($e->getMessage());
			
			$data['success'] = false;
			$data['message'] = $message;
			
			return $data;
		}
	}
}
