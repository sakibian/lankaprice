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

namespace App\Services\Auth;

use App\Services\Auth\Helpers\AuthenticatesUsers;
use App\Services\Auth\Social\SaveProviderData;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialService extends BaseService
{
	use AuthenticatesUsers, SaveProviderData;
	
	// Supported Providers
	// Stateless authentication is not available for OAuth 1.0 APIs.
	private array $network = [
		'facebook'        => 'facebook',
		'linkedin'        => 'linkedin-openid',
		'twitter-oauth-2' => 'twitter-oauth-2',
		'google'          => 'google',
	];
	private array $networkChecker;
	
	private string $serviceNotFound = 'The social network "%s" is not available.';
	private string $serviceNotEnabled = 'The social network "%s" is not enabled.';
	private string $serviceError = "Unknown error. The service does not work.";
	
	public function __construct()
	{
		parent::__construct();
		
		// Providers Checker
		$this->networkChecker = [
			'facebook'        => (
				config('settings.social_auth.facebook_client_id')
				&& config('settings.social_auth.facebook_client_secret')
			),
			'linkedin'        => (
				config('settings.social_auth.linkedin_client_id')
				&& config('settings.social_auth.linkedin_client_secret')
			),
			'twitter-oauth-2' => (
				config('settings.social_auth.twitter_oauth_2_client_id')
				&& config('settings.social_auth.twitter_oauth_2_client_secret')
			),
			'twitter'         => (
				config('settings.social_auth.twitter_client_id')
				&& config('settings.social_auth.twitter_client_secret')
			),
			'google'          => (
				config('settings.social_auth.google_client_id')
				&& config('settings.social_auth.google_client_secret')
			),
		];
	}
	
	/**
	 * Get target URL
	 *
	 * @param string $provider
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getProviderTargetUrl(string $provider): JsonResponse
	{
		// Get the Provider and verify that if it's supported
		$serviceKey = $this->network[$provider] ?? null;
		if (empty($serviceKey)) {
			$message = sprintf($this->serviceNotFound, $provider);
			
			return apiResponse()->notFound($message);
		}
		
		// Check if the Provider is enabled
		$providerIsEnabled = (array_key_exists($provider, $this->networkChecker) && $this->networkChecker[$provider]);
		if (!$providerIsEnabled) {
			$message = sprintf($this->serviceNotEnabled, $provider);
			
			return apiResponse()->notFound($message);
		}
		
		// Redirect to the Provider's website
		try {
			$socialiteObj = Socialite::driver($serviceKey)->stateless();
			
			return $socialiteObj->redirect()->getTargetUrl();
		} catch (Throwable $e) {
			$message = $e->getMessage();
			if (empty($message)) {
				$message = $this->serviceError;
			}
			
			return apiResponse()->error($message);
		}
	}
	
	/**
	 * Get user info
	 *
	 * @param string $provider
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function handleProviderCallback(string $provider): JsonResponse
	{
		// Get the Provider and verify that if it's supported
		$serviceKey = $this->network[$provider] ?? null;
		if (empty($serviceKey)) {
			$message = sprintf($this->serviceNotFound, $provider);
			
			return apiResponse()->notFound($message);
		}
		
		// API CALL - GET USER FROM PROVIDER
		try {
			// $providerData = Socialite::driver($provider)->stateless()->user();
			$token = request()->input('accessToken');
			$providerData = Socialite::driver($serviceKey)->stateless()->userFromToken($token);
			
			// Data not found
			if (!$providerData) {
				return apiResponse()->error(t('unknown_error_please_try_again'));
			}
			
			// Email not found
			if (!filter_var($providerData->getEmail(), FILTER_VALIDATE_EMAIL)) {
				$message = t('email_not_found_at_provider', ['provider' => str($provider)->headline()]);
				
				return apiResponse()->error($message);
			}
		} catch (Throwable $e) {
			$message = $e->getMessage();
			if (empty($message)) {
				$message = $this->serviceError;
			}
			
			return apiResponse()->error($message);
		}
		
		// DEBUG
		// dd($providerData);
		
		// SAVE USER
		return $this->saveUser($provider, $providerData);
	}
}
