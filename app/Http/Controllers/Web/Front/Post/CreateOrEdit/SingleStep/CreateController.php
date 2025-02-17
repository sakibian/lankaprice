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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\SingleStep;

use App\Helpers\Services\Referrer;
use App\Http\Controllers\Web\Front\Auth\Traits\ShowReSendVerificationCodeButton;
use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Controllers\Web\Front\Payment\HasPaymentRedirection;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\Traits\PricingPageUrlTrait;
use App\Http\Requests\Front\PostRequest;
use App\Models\Package;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use App\Services\Payment\HasPaymentReferrers;
use App\Services\Payment\HasPaymentTrigger;
use App\Services\Payment\Promotion\SingleStepPayment;
use App\Services\PostService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class CreateController extends FrontController
{
	use ShowReSendVerificationCodeButton;
	use HasPaymentReferrers;
	use HasPaymentTrigger, SingleStepPayment, HasPaymentRedirection;
	use PricingPageUrlTrait;
	
	protected PostService $postService;
	
	// Payment's properties
	public array $msg = [];
	public array $uri = [];
	public ?Package $selectedPackage = null; // See SingleStepPaymentTrait::setPaymentSettingsForPromotion()
	public Collection $packages;
	public Collection $paymentMethods;
	
	/**
	 * @param \App\Services\PostService $postService
	 */
	public function __construct(PostService $postService)
	{
		parent::__construct();
		
		$this->postService = $postService;
		
		$this->commonQueries();
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [];
		
		// Check if guests can post listings
		if (!doesGuestHaveAbilityToCreateListings()) {
			$array[] = 'auth';
		}
		
		return array_merge(parent::middleware(), $array);
	}
	
	/**
	 * @return void
	 */
	public function commonQueries(): void
	{
		$this->getPaymentReferrersData();
		$this->setPaymentSettingsForPromotion();
		
		// References
		$data = [];
		
		if (config('settings.listing_form.show_listing_type')) {
			$data['postTypes'] = Referrer::getPostTypes();
			view()->share('postTypes', $data['postTypes']);
		}
		
		// Save common's data
		$this->data = $data;
	}
	
	/**
	 * New Post's Form.
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function showForm()
	{
		// Check if the 'Pricing Page' must be started first, and make redirection to it.
		$pricingUrl = $this->getPricingPage($this->selectedPackage);
		if (!empty($pricingUrl)) {
			return redirect()->to($pricingUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
		}
		
		// Check if the form type is 'Multi-Step Form' and make redirection to it (permanently).
		if (isMultipleStepsFormEnabled()) {
			$url = urlGen()->addPost();
			if ($url != request()->fullUrl()) {
				return redirect()->to($url, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('create');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		// Create
		return view('front.post.createOrEdit.singleStep.create');
	}
	
	/**
	 * Store a new Post.
	 *
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(PostRequest $request): RedirectResponse
	{
		// Store Post
		$data = getServiceData($this->postService->store($request));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			session()->put('message', $message);
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			$previousUrl = data_get($data, 'extra.previousUrl');
			$previousUrl = !empty($previousUrl) ? $previousUrl : url()->previous();
			
			return redirect()->to($previousUrl)->withInput($request->except('pictures'));
		}
		
		// Get Listing Resource
		$post = data_get($data, 'result');
		
		abort_if(empty($post), 404, t('post_not_found'));
		
		// Get the Next URL
		$nextUrl = url('create/finish');
		
		// Get the listing ID
		$postId = data_get($data, 'result.id');
		
		// Check if the payment process has been triggered
		// NOTE: Payment bypass email or phone verification
		// ===| Make|send payment (if needed) |==============
		
		$postObj = $this->retrievePayableModel($request, $postId);
		if (!empty($postObj)) {
			$payResult = $this->isPaymentRequested($request, $postObj);
			if (data_get($payResult, 'success')) {
				return $this->sendPayment($request, $postObj);
			}
			if (data_get($payResult, 'failure')) {
				flash(data_get($payResult, 'message'))->error();
			}
		}
		
		// ===| If no payment is made (continue) |===========
		
		if (
			data_get($data, 'extra.sendEmailVerification.emailVerificationSent')
			|| data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')
		) {
			$nextUrl = urlQuery($nextUrl)
				->setParameters(request()->only(['packageId']))
				->toString();
			session()->put('itemNextUrl', $nextUrl);
			
			if (data_get($data, 'extra.sendEmailVerification.emailVerificationSent')) {
				session()->put('emailVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationEmailLink($post, 'posts');
			}
			
			if (data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')) {
				session()->put('phoneVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationSmsLink($post, 'posts');
				
				// Phone Number verification
				// Get the token|code verification form page URL
				// The user is supposed to have received this token|code by SMS
				$nextUrl = url('verify/posts/phone/');
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
		
		$nextUrl = urlQuery($nextUrl)
			->setParameters(request()->only(['packageId']))
			->toString();
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * Confirmation
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function finish()
	{
		if (!session()->has('message')) {
			return redirect()->to('/');
		}
		
		// Clear Session
		if (session()->has('itemNextUrl')) {
			session()->forget('itemNextUrl');
		}
		
		$post = null;
		if (session()->has('postId')) {
			// Get the Post
			$post = Post::query()
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->where('id', session('postId'))
				->first();
			
			abort_if(empty($post), 404, t('post_not_found'));
			
			session()->forget('postId');
		}
		
		// Redirect to the Post,
		// - If User is logged
		// - Or if Email and Phone verification option is not activated
		$doesVerificationIsDisabled = (config('settings.mail.email_verification') != 1 && config('settings.sms.phone_verification') != 1);
		if (auth()->check() || $doesVerificationIsDisabled) {
			if (!empty($post)) {
				flash(session('message'))->success();
				
				return redirect()->to(urlGen()->postUri($post));
			}
		}
		
		// Meta Tags
		MetaTag::set('title', session('message'));
		MetaTag::set('description', session('message'));
		
		return view('front.post.createOrEdit.singleStep.finish');
	}
}
