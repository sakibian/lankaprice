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

namespace App\Services\Auth\Traits;

use App\Http\Resources\PasswordResetResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\PasswordReset;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use App\Services\Auth\Traits\Verification\EmailVerificationTrait;
use App\Services\Auth\Traits\Verification\PhoneVerificationTrait;
use Illuminate\Http\JsonResponse;

trait VerificationTrait
{
	use EmailVerificationTrait, PhoneVerificationTrait, RecognizedUserActions;
	
	protected array $entitiesMetadata = [
		'users'    => [
			'key'        => 'users',
			'model'      => User::class,
			'scopes'     => [VerifiedScope::class],
			'nameColumn' => 'name',
		],
		'posts'    => [
			'key'        => 'posts',
			'model'      => Post::class,
			'scopes'     => [VerifiedScope::class, ReviewedScope::class],
			'nameColumn' => 'contact_name',
		],
		'password' => [
			'key'        => 'password',
			'model'      => PasswordReset::class,
			'scopes'     => [],
			'nameColumn' => null,
		],
	];
	protected string $metadataNotFoundMessage = "The metadata for the entity '%s' cannot be found.";
	
	/**
	 * Verification
	 *
	 * Verify the user's email address or mobile phone number
	 *
	 * @param string $entityMetadataKey
	 * @param string $field
	 * @param string|null $token
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function verifyCode(string $entityMetadataKey, string $field, string $token = null, array $params = []): JsonResponse
	{
		if (empty($token)) {
			return apiResponse()->error(t('The token or code to verify is empty'));
		}
		
		$deviceName = $params['deviceName'] ?? null;
		
		// Get the entity metadata
		$entityMetadata = $this->getEntityMetadata($entityMetadataKey);
		
		if (empty($entityMetadata)) {
			return apiResponse()->notFound(sprintf($this->metadataNotFoundMessage, $entityMetadataKey));
		}
		
		// Get Field Label
		$fieldLabel = ($field == 'phone') ? t('phone_number') : t('email_address');
		
		// Get Model (with its Namespace)
		$model = $entityMetadata['model'];
		
		// Verification (for Forgot Password)
		if ($entityMetadataKey == 'password') {
			return $this->verifyCodeForPassword($model, $fieldLabel, $token, $params);
		}
		
		// Get Entity by Token
		$object = $model::query()
			->withoutGlobalScopes($entityMetadata['scopes'])
			->where($field . '_token', $token)
			->first();
		
		if (empty($object)) {
			return apiResponse()->error(t('Your field verification has failed', ['field' => $fieldLabel]));
		}
		
		$data = [];
		$data['result'] = null;
		
		if (empty($object->{$field . '_verified_at'})) {
			// Verified
			$object->{$field . '_verified_at'} = now();
			$object->save();
			
			$message = t('Your field has been verified', ['name' => $object->{$entityMetadata['nameColumn']}, 'field' => $fieldLabel]);
			
			$data['success'] = true;
			$data['message'] = $message;
		} else {
			$message = t('Your field is already verified', ['field' => $fieldLabel]);
			
			$data['success'] = false;
			$data['message'] = $message;
			
			if ($entityMetadataKey == 'users') {
				$data['result'] = new UserResource($object, $params);
			}
			if ($entityMetadataKey == 'posts') {
				$data['result'] = new PostResource($object, $params);
			}
			
			return apiResponse()->json($data);
		}
		
		// Is It User Entity?
		if ($entityMetadataKey == 'users') {
			$data['result'] = new UserResource($object, $params);
			
			// Match User's Posts (posted as Guest)
			$this->findAndMatchPostsToUser($object);
			
			// Get User creation next URL
			// Login the User
			if (
				isVerifiedUser($object)
				&& $object->blocked != 1
				&& $object->closed != 1
			) {
				$extra = [];
				
				if (isFromApi()) {
					// Create the API access token
					$defaultDeviceName = doesRequestIsFromWebClient() ? 'Website' : 'Other Client';
					$deviceName = $deviceName ?? $defaultDeviceName;
					$token = $object->createToken($deviceName);
					
					// Save extra data
					$extra['authToken'] = $token->plainTextToken;
					$extra['tokenType'] = 'Bearer';
				}
				
				$data['extra'] = $extra;
			}
		}
		
		// Is It Listing Entity?
		if ($entityMetadataKey == 'posts') {
			$data['result'] = new PostResource($object, $params);
			
			// Match User's listings (posted as Guest) & User's data (if missed)
			$this->findAndMatchUserToPost($object);
		}
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Verification (Forgot Password)
	 *
	 * Verify the user's email address or mobile phone number through the 'password_reset' table
	 *
	 * @param $model
	 * @param string $fieldLabel
	 * @param string|null $token
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	private function verifyCodeForPassword($model, string $fieldLabel, string $token = null, array $params = []): JsonResponse
	{
		// Get Entity by Token
		$object = $model::where('token', $token)->first();
		
		if (empty($object)) {
			return apiResponse()->error(t('Your field verification has failed', ['field' => $fieldLabel]));
		}
		
		$message = t('your_field_has_been_verified_token', ['field' => $fieldLabel]);
		
		$data = [
			'success' => true,
			'message' => $message,
			'result'  => new PasswordResetResource($object, $params),
		];
		
		return apiResponse()->json($data);
	}
	
	/**
	 * Get the entity metadata
	 *
	 * @param string|null $metadataKey
	 * @return array|null
	 */
	protected function getEntityMetadata(?string $metadataKey = null): ?array
	{
		if (empty($metadataKey)) {
			$metadataKey = 'undefined';
		}
		
		return $this->entitiesMetadata[$metadataKey] ?? null;
	}
}
