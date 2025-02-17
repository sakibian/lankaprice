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

use Illuminate\Http\Request;

/**
 * Generate a Token for API calls
 *
 * @return string
 */
function generateApiToken(): string
{
	return base64_encode(createRandomString(32));
}

/**
 * Check if the current request is from the API
 *
 * @param \Illuminate\Http\Request|null $request
 * @return bool
 */
function isFromApi(?Request $request = null): bool
{
	if (!$request instanceof Request) {
		$request = request();
	}
	
	return (
		str_starts_with($request->path(), 'api/')
		|| $request->is('api/*')
		|| $request->segment(1) == 'api'
		|| ($request->hasHeader('X-API-CALLED') && $request->header('X-API-CALLED'))
	);
}

/**
 * Get the auth guard
 *
 * @return string|null
 */
function getAuthGuard(): ?string
{
	$guard = isFromApi() ? 'sanctum' : config('larapen.core.web.guard');
	
	return getAsStringOrNull($guard);
}

/**
 * Does the (current) request is from a Web Application?
 * Check if the current request is made from the official(s) web version(s) of the app
 *
 * Info: This function allows applying web features during API code execution
 * Note: This assumes the "X-AppType=web" header is sent from the web application
 *
 * @param \Illuminate\Http\Request|null $request
 * @return bool
 */
function doesRequestIsFromWebClient(?Request $request = null): bool
{
	if (!$request instanceof Request) {
		$request = request();
	}
	
	if (!isFromApi($request)) return true;
	
	return (isFromApi($request) && $request->header('X-AppType') == 'web');
}

/**
 * @return string|null
 */
function getApiAuthToken(): ?string
{
	$token = null;
	
	if (request()->hasHeader('Authorization')) {
		$authorization = request()->header('Authorization');
		if (str_contains($authorization, 'Bearer')) {
			$token = str_replace('Bearer ', '', $authorization);
		}
	}
	
	return is_string($token) ? $token : null;
}

/**
 * @param $paginatedCollection
 * @return mixed
 */
function setPaginationBaseUrl($paginatedCollection)
{
	// If the request is made from the app's Web environment,
	// use the Web URL as the pagination's base URL
	if (doesRequestIsFromWebClient()) {
		if (request()->hasHeader('X-WEB-REQUEST-URL')) {
			if (method_exists($paginatedCollection, 'setPath')) {
				$paginatedCollection->setPath(request()->header('X-WEB-REQUEST-URL'));
			}
		}
	}
	
	return $paginatedCollection;
}

/**
 * Log out the user on a web client (Browser)
 *
 * @param string|null $message
 * @param bool $withNotification
 * @return string|null
 */
function logoutSession(?string $message = null, bool $withNotification = true): ?string
{
	if (isFromApi()) return null;
	
	$guard = getAuthGuard();
	
	if (!auth($guard)->check()) return $message;
	
	// Save some important session data (temporary)
	if (session()->has('countryCode')) {
		$countryCode = session('countryCode');
	}
	if (session()->has('allowMeFromReferrer')) {
		$allowMeFromReferrer = session('allowMeFromReferrer');
	}
	if (session()->has('browserLangCode')) {
		$browserLangCode = session('browserLangCode');
	}
	
	// Remove all session vars
	auth($guard)->logout();
	request()->session()->flush();
	request()->session()->regenerate();
	
	// Retrieve the session data saved (temporary)
	if (!empty($countryCode)) {
		session()->put('countryCode', $countryCode);
	}
	if (!empty($allowMeFromReferrer)) {
		session()->put('allowMeFromReferrer', $allowMeFromReferrer);
	}
	if (!empty($browserLangCode)) {
		session()->put('browserLangCode', $browserLangCode);
	}
	
	if (!$withNotification) return null;
	
	// Unintentional disconnection
	if (empty($message)) {
		$message = t('unintentional_logout');
		notification($message, 'error');
		
		return $message;
	}
	
	// Intentional disconnection
	notification($message, 'success');
	
	return $message;
}

/**
 * @return bool
 */
function isPostCreationRequest(): bool
{
	if (isFromApi()) {
		$isPostCreationRequest = (str_contains(currentRouteAction(), 'Api\PostController@store'));
	} else {
		$isNewEntryUri = (
			(isMultipleStepsFormEnabled() && request()->segment(2) == 'create')
			|| (isSingleStepFormEnabled() && request()->segment(1) == 'create')
		);
		
		$isPostCreationRequest = (
			$isNewEntryUri
			|| str_contains(currentRouteAction(), 'Post\CreateOrEdit\MultiSteps\Create')
			|| str_contains(currentRouteAction(), 'Post\CreateOrEdit\SingleStep\CreateController')
		);
	}
	
	return $isPostCreationRequest;
}
