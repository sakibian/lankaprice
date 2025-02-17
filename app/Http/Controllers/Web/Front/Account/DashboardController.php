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

namespace App\Http\Controllers\Web\Front\Account;

use App\Helpers\Common\Cookie;
use App\Helpers\Services\Referrer;
use App\Http\Controllers\Web\Front\Account\Traits\DashboardTrait;
use App\Http\Controllers\Web\Front\Auth\Traits\ShowReSendVerificationCodeButton;
use App\Http\Requests\Front\AvatarRequest;
use App\Http\Requests\Front\UserRequest;
use App\Http\Requests\Front\UserSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class DashboardController extends AccountBaseController
{
	use ShowReSendVerificationCodeButton;
	use DashboardTrait;
	
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index()
	{
		$genders = Referrer::getGenders();
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('my_account') . ' - ' . $appName;
		$description = t('my_account_on', ['appName' => config('settings.app.name')]);
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		
		return view('front.account.dashboard', compact('genders'));
	}
	
	/**
	 * Update the user's details
	 *
	 * @param \App\Http\Requests\Front\UserRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateDetails(UserRequest $request): RedirectResponse
	{
		$authUserId = auth()->user()?->getAuthIdentifier() ?? '-1';
		
		// Update the user's data
		$data = getServiceData($this->userService->update($authUserId, $request));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			return redirect()->back()->withInput($request->except(['photo_path']));
		}
		
		// Get User Resource
		$user = data_get($data, 'result');
		
		// Don't log out the User (See the User model's file)
		if (data_get($data, 'extra.emailOrPhoneChanged')) {
			session()->put('emailOrPhoneChanged', true);
		}
		
		// Get Query String
		$params = [];
		if ($request->filled('panel')) {
			$params['panel'] = $request->input('panel');
		}
		
		// Get the next URL
		$nextUrl = urlQuery(url('account'))->setParameters($params)->toString();
		
		if (
			data_get($data, 'extra.sendEmailVerification.emailVerificationSent')
			|| data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')
		) {
			session()->put('userNextUrl', $nextUrl);
			
			if (data_get($data, 'extra.sendEmailVerification.emailVerificationSent')) {
				session()->put('emailVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationEmailLink($user, 'users');
			}
			
			if (data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')) {
				session()->put('phoneVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationSmsLink($user, 'users');
				
				// Go to Phone Number verification
				$nextUrl = url('verify/users/phone/');
			}
		}
		
		// Mail Notification Message
		if (data_get($data, 'extra.mail.message')) {
			$mailMessage = data_get($data, 'extra.mail.message');
			if (data_get($data, 'extra.mail.success')) {
				flash($mailMessage)->success();
			} else {
				flash($mailMessage)->error();
			}
		}
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * Update the user's settings
	 *
	 * @param \App\Http\Requests\Front\UserSettingsRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateSettings(UserSettingsRequest $request): RedirectResponse
	{
		$authUserId = auth()->user()?->getAuthIdentifier() ?? '-1';
		
		// Update the user's settings
		$data = getServiceData($this->userService->updateSettings($authUserId, $request));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			return redirect()->back()->withInput($request->except(['photo_path']));
		}
		
		// Get Query String
		$params = [];
		if ($request->filled('panel')) {
			$params['panel'] = $request->input('panel');
		}
		
		// Get the next URL
		$nextUrl = urlQuery(url('account'))->setParameters($params)->toString();
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * Update the user's photo
	 *
	 * @param \App\Http\Requests\Front\AvatarRequest $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function updatePhoto(AvatarRequest $request): JsonResponse|RedirectResponse
	{
		$authUserId = auth()->user()?->getAuthIdentifier() ?? '-1';
		
		// Update the user's photo
		$data = getServiceData($this->userService->updatePhoto($authUserId, $request));
		
		// Parsing the API response
		return $this->handlePhotoData($data);
	}
	
	/**
	 * Delete the user's photo
	 *
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
	public function deletePhoto(): JsonResponse|RedirectResponse
	{
		$authUserId = auth()->user()?->getAuthIdentifier() ?? '-1';
		
		// Delete the user's photo
		$data = getServiceData($this->userService->removePhoto($authUserId));
		
		// Parsing the API response
		return $this->handlePhotoData($data);
	}
	
	/**
	 * Set or unset the dark mode for the logged-in user
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function setDarkMode(Request $request): JsonResponse
	{
		$darkMode = $request->integer('dark_mode');
		$userId = $request->input('user_id');
		
		$status = 200;
		$message = null;
		
		if (auth()->check()) {
			// Set the dark mode for the user
			$data = getServiceData($this->userService->setDarkMode($userId, $request));
			
			// Parsing the API response
			$status = (int)data_get($data, 'status');
			$message = data_get($data, 'message');
			
			// Error Found
			if (!data_get($data, 'success')) {
				$message = $message ?? t('unknown_error');
				
				return ajaxResponse()->json(['message' => $message], $status);
			}
			
			// Get entry resource
			$user = data_get($data, 'result');
			$darkMode = (int)data_get($user, 'dark_mode', 0);
		}
		
		// Set or remove dark mode cookie
		if ($darkMode == 1) {
			Cookie::set('darkTheme', 'dark');
			$message = !empty($message) ? $message : t('dark_mode_is_set');
		} else {
			Cookie::forget('darkTheme');
			$message = !empty($message) ? $message : t('dark_mode_is_disabled');
		}
		
		// AJAX response data
		$result = [
			'userId'   => $request->integer('user_id'),
			'darkMode' => $darkMode,
			'message'  => $message,
		];
		
		return ajaxResponse()->json($result, $status);
	}
}
