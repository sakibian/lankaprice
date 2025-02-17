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

use App\Http\Controllers\Web\Front\Account\CloseController;
use App\Http\Controllers\Web\Front\Account\DashboardController;
use App\Http\Controllers\Web\Front\Account\MessagesController;
use App\Http\Controllers\Web\Front\Account\PostsController;
use App\Http\Controllers\Web\Front\Account\SavedPostsController;
use App\Http\Controllers\Web\Front\Account\SavedSearchesController;
use App\Http\Controllers\Web\Front\Account\SubscriptionController;
use App\Http\Controllers\Web\Front\Account\TransactionsController;
use App\Http\Controllers\Web\Front\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Front\Auth\LoginController;
use App\Http\Controllers\Web\Front\Auth\RegisterController;
use App\Http\Controllers\Web\Front\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Front\Auth\SocialController;
use App\Http\Controllers\Web\Front\Auth\VerificationController;
use App\Http\Controllers\Web\Front\Browsing\Category\CategoryController as BrowsingCategoryController;
use App\Http\Controllers\Web\Front\Browsing\Location\AutoCompleteController;
use App\Http\Controllers\Web\Front\Browsing\Location\ModalController;
use App\Http\Controllers\Web\Front\Browsing\Location\SelectBoxController;
use App\Http\Controllers\Web\Front\CountriesController;
use App\Http\Controllers\Web\Front\FileController;
use App\Http\Controllers\Web\Front\HomeController;
use App\Http\Controllers\Web\Front\Locale\LocaleController;
use App\Http\Controllers\Web\Front\Page\CmsController;
use App\Http\Controllers\Web\Front\Page\ContactController;
use App\Http\Controllers\Web\Front\Page\PricingController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\FinishController as CreateFinishController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PaymentController as CreatePaymentController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PhotoController as CreatePhotoController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PostController as CreatePostController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\PaymentController as EditPaymentController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\PhotoController as EditPhotoController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\PostController as EditPostController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\SingleStep\CreateController as SingleCreateController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\SingleStep\EditController as SingleEditController;
use App\Http\Controllers\Web\Front\Post\ReportController;
use App\Http\Controllers\Web\Front\Post\Show\ShowController;
use App\Http\Controllers\Web\Front\Search\CategoryController;
use App\Http\Controllers\Web\Front\Search\CityController;
use App\Http\Controllers\Web\Front\Search\SearchController;
use App\Http\Controllers\Web\Front\Search\TagController;
use App\Http\Controllers\Web\Front\Search\UserController;
use App\Http\Controllers\Web\Front\SitemapController;
use App\Http\Controllers\Web\Front\SitemapsController;
use Illuminate\Support\Facades\Route;

$isDomainmappingAvailable = (plugin_exists('domainmapping') && plugin_installed_file_exists('domainmapping'));

// Select Language
Route::namespace('Locale')
	->group(function ($router) {
		Route::get('locale/{code}', [LocaleController::class, 'setLocale']);
	});

// FILES
Route::controller(FileController::class)
	->prefix('common')
	->group(function ($router) {
		Route::get('file', 'watchMediaContent');
		Route::get('js/fileinput/locales/{code}.js', 'bootstrapFileinputLocales');
		Route::get('js/intl-tel-input/countries.js', 'intlTelInputData');
		Route::get('css/style.css', 'cssStyle');
	});

if (!$isDomainmappingAvailable) {
	// SITEMAPS (XML)
	Route::get('sitemaps.xml', [SitemapsController::class, 'getAllCountriesSitemapIndex']);
}

// Impersonate (As admin user, login as another user)
Route::middleware(['auth'])
	->group(function ($router) {
		Route::impersonate();
	});


// HOMEPAGE
if (!doesCountriesPageCanBeHomepage()) {
	Route::get('/', [HomeController::class, 'index']);
	Route::get(dynamicRoute('routes.countries'), CountriesController::class);
} else {
	Route::get('/', CountriesController::class);
}


// AUTH
Route::namespace('Auth')
	->middleware(['guest', 'no.http.cache'])
	->group(function ($router) {
		// Registration Routes...
		Route::controller(RegisterController::class)
			->group(function ($router) {
				Route::get(dynamicRoute('routes.register'), 'showRegistrationForm');
				Route::post(dynamicRoute('routes.register'), 'register');
				Route::get('register/finish', 'finish');
			});
		
		// Authentication Routes...
		Route::controller(LoginController::class)
			->group(function ($router) {
				Route::get(dynamicRoute('routes.login'), 'showLoginForm');
				Route::post(dynamicRoute('routes.login'), 'login');
			});
		
		// Forgot Password Routes...
		Route::controller(ForgotPasswordController::class)
			->group(function ($router) {
				Route::get('password/reset', 'showLinkRequestForm');
				Route::post('password/email', 'sendResetLink');
			});
		
		Route::controller(ResetPasswordController::class)
			->group(function ($router) {
				// Reset Password using Token
				Route::get('password/token', 'showTokenRequestForm');
				Route::post('password/token', 'sendResetToken');
				
				// Reset Password using Link (Core Routes...)
				Route::get('password/reset/{token}', 'showResetForm');
				Route::post('password/reset', 'reset');
			});
		
		// Social Authentication
		Route::controller(SocialController::class)
			->group(function ($router) {
				$router->pattern('provider', 'facebook|linkedin|twitter-oauth-2|twitter|google');
				Route::get('auth/{provider}', 'redirectToProvider');
				Route::get('auth/{provider}/callback', 'handleProviderCallback');
			});
	});

Route::namespace('Auth')
	->group(function ($router) {
		// User Logout
		Route::get(dynamicRoute('routes.logout'), [LoginController::class, 'logout']);
	});


// VERIFICATION
Route::controller(VerificationController::class)
	->prefix('verify')
	->group(function ($router) {
		// Email Address or Phone Number verification
		// ---
		// Important: Make sure that the 'entityMetadataKey' possible values match with
		// $entitiesMetadata key in the 'app/Services/Auth/Traits/VerificationTrait.php' file
		$router->pattern('entityMetadataKey', 'users|posts|password');
		$router->pattern('entityMetadataKeyForReSend', 'users|posts');
		$router->pattern('field', 'email|phone');
		$router->pattern('token', '.*');
		$router->pattern('entityId', '[0-9]+');
		
		Route::get('{entityMetadataKeyForReSend}/{entityId}/resend/email', 'reSendEmailVerification');
		Route::get('{entityMetadataKeyForReSend}/{entityId}/resend/sms', 'reSendPhoneVerification');
		Route::get('{entityMetadataKey}/{field}/{token?}', 'showVerificationForm');
		Route::post('{entityMetadataKey}/{field}/{token?}', 'postVerificationForm');
	});


// POSTS
Route::namespace('Post')
	->group(function ($router) {
		$router->pattern('id', '[0-9]+');
		
		$hidPrefix = config('larapen.core.hashableIdPrefix');
		if (is_string($hidPrefix) && !empty($hidPrefix)) {
			$router->pattern('hashableId', '([0-9]+)?(' . $hidPrefix . '[a-z0-9A-Z]{11})?');
		} else {
			$router->pattern('hashableId', '([0-9]+)?([a-z0-9A-Z]{11})?');
		}
		
		// $router->pattern('slug', '.*');
		$bannedSlugs = regexSimilarRoutesPrefixes();
		if (!empty($bannedSlugs)) {
			/*
			 * NOTE:
			 * '^(?!companies|users)$' : Don't match 'companies' or 'users'
			 * '^(?=.*)$'              : Match any character
			 * '^((?!\/).)*$'          : Match any character, but don't match string with '/'
			 */
			$router->pattern('slug', '^(?!' . implode('|', $bannedSlugs) . ')(?=.*)((?!\/).)*$');
		} else {
			$router->pattern('slug', '^(?=.*)((?!\/).)*$');
		}
		
		// SingleStep Listing creation
		Route::namespace('CreateOrEdit\SingleStep')
			->controller(SingleCreateController::class)
			->group(function ($router) {
				Route::get('create', 'showForm');
				Route::post('create', 'postForm');
				Route::get('create/finish', 'finish');
				
				// Payment Gateway Success & Cancel
				Route::get('create/payment/success', 'paymentConfirmation');
				Route::get('create/payment/cancel', 'paymentCancel');
				Route::post('create/payment/success', 'paymentConfirmation');
			});
		
		// MultiSteps Listing creation
		Route::namespace('CreateOrEdit\MultiSteps')
			->group(function ($router) {
				Route::controller(CreatePostController::class)
					->group(function ($router) {
						Route::get('posts/create', 'showForm');
						Route::post('posts/create', 'postForm');
					});
				
				Route::controller(CreatePhotoController::class)
					->group(function ($router) {
						Route::get('posts/create/photos', 'showForm');
						Route::post('posts/create/photos', 'postForm');
						Route::post('posts/create/photos/{photoId}/delete', 'removePicture');
						Route::post('posts/create/photos/reorder', 'reorderPictures');
					});
				
				Route::controller(CreatePaymentController::class)
					->group(function ($router) {
						Route::get('posts/create/payment', 'showForm');
						Route::post('posts/create/payment', 'postForm');
						
						// Payment Gateway Success & Cancel
						Route::get('posts/create/payment/success', 'paymentConfirmation');
						Route::post('posts/create/payment/success', 'paymentConfirmation');
						Route::get('posts/create/payment/cancel', 'paymentCancel');
					});
				
				Route::post('posts/create/finish', CreateFinishController::class);
				Route::get('posts/create/finish', CreateFinishController::class);
			});
		
		Route::middleware(['auth'])
			->group(function ($router) {
				$router->pattern('id', '[0-9]+');
				
				// SingleStep Listing edition
				Route::namespace('CreateOrEdit\SingleStep')
					->controller(SingleEditController::class)
					->group(function ($router) {
						Route::get('edit/{id}', 'showForm');
						Route::put('edit/{id}', 'postForm');
						
						// Payment Gateway Success & Cancel
						Route::get('edit/{id}/payment/success', 'paymentConfirmation');
						Route::get('edit/{id}/payment/cancel', 'paymentCancel');
						Route::post('edit/{id}/payment/success', 'paymentConfirmation');
					});
				
				// MultiSteps Listing Edition
				Route::namespace('CreateOrEdit\MultiSteps')
					->group(function ($router) {
						Route::controller(EditPostController::class)
							->group(function ($router) {
								Route::get('posts/{id}/details', 'showForm');
								Route::put('posts/{id}/details', 'postForm');
							});
						
						Route::controller(EditPhotoController::class)
							->group(function ($router) {
								Route::get('posts/{id}/photos', 'showForm');
								Route::post('posts/{id}/photos', 'postForm');
								Route::post('posts/{id}/photos/{photoId}/delete', 'delete');
								Route::post('posts/{id}/photos/reorder', 'reorder');
							});
						
						Route::controller(EditPaymentController::class)
							->group(function ($router) {
								Route::get('posts/{id}/payment', 'showForm');
								Route::post('posts/{id}/payment', 'postForm');
								
								// Payment Gateway Success & Cancel
								Route::get('posts/{id}/payment/success', 'paymentConfirmation');
								Route::post('posts/{id}/payment/success', 'paymentConfirmation');
								Route::get('posts/{id}/payment/cancel', 'paymentCancel');
							});
					});
			});
		
		// Post's Details
		Route::controller(ShowController::class)
			->group(function ($router) {
				$router->pattern('id', '[0-9]+');
				Route::get(dynamicRoute('routes.post'), 'index');
				Route::post('posts/{id}/phone', 'getPhone');
			});
		
		// Send report abuse
		Route::controller(ReportController::class)
			->group(function ($router) {
				Route::get('posts/{hashableId}/report', 'showReportForm');
				Route::post('posts/{hashableId}/report', 'sendReport');
			});
	});


// ACCOUNT
Route::namespace('Account')
	->prefix('account')
	->group(function ($router) {
		// Messenger
		// Contact Post's Author
		Route::post('messages/posts/{id}', [MessagesController::class, 'store']);
		
		Route::middleware(['auth', 'banned.user', 'no.http.cache'])
			->group(function ($router) {
				$router->pattern('id', '[0-9]+');
				
				// Users
				Route::controller(DashboardController::class)
					->group(function ($router) {
						Route::get('/', 'index');
						Route::middleware(['impersonate.protect'])
							->group(function ($router) {
								Route::put('/', 'updateDetails');
								Route::put('settings', 'updateSettings');
								Route::put('photo', 'updatePhoto');
								Route::put('photo/delete', 'deletePhoto');
								Route::post('dark-mode', 'setDarkMode');
							});
					});
				Route::controller(CloseController::class)
					->group(function ($router) {
						Route::get('close', 'showForm');
						Route::middleware(['impersonate.protect'])
							->group(function ($router) {
								Route::post('close', 'postForm');
							});
					});
				
				// Subscription
				Route::controller(SubscriptionController::class)
					->group(function ($router) {
						Route::get('subscription', 'showForm');
						Route::post('subscription', 'postForm');
						
						// Payment Gateway Success & Cancel
						Route::get('{id}/payment/success', 'paymentConfirmation');
						Route::post('{id}/payment/success', 'paymentConfirmation');
						Route::get('{id}/payment/cancel', 'paymentCancel');
					});
				
				// Posts
				Route::controller(PostsController::class)
					->prefix('posts')
					->group(function ($router) {
						$router->pattern('id', '[0-9]+');
						// Activated Posts
						Route::get('list', 'onlinePosts');
						Route::get('list/{id}/offline', 'takePostOffline');
						Route::get('list/{id}/delete', 'destroy');
						Route::post('list/delete', 'destroy');
						
						// Archived Posts
						Route::get('archived', 'archivedPosts');
						Route::get('archived/{id}/repost', 'repostPost');
						Route::get('archived/{id}/delete', 'destroy');
						Route::post('archived/delete', 'destroy');
						
						// Pending Approval Posts
						Route::get('pending-approval', 'pendingApprovalPosts');
						Route::get('pending-approval/{id}/delete', 'destroy');
						Route::post('pending-approval/delete', 'destroy');
					});
				
				// Saved Posts
				Route::controller(SavedPostsController::class)
					->prefix('saved-posts')
					->group(function ($router) {
						$router->pattern('id', '[0-9]+');
						Route::post('toggle', 'toggle');
						Route::get('/', 'index');
						Route::get('{id}/delete', 'destroy');
						Route::post('delete', 'destroy');
					});
				
				// Saved Searches
				Route::controller(SavedSearchesController::class)
					->prefix('saved-searches')
					->group(function ($router) {
						$router->pattern('id', '[0-9]+');
						Route::post('store', 'store');
						Route::get('/', 'index');
						Route::get('{id}', 'show');
						Route::get('{id}/delete', 'destroy');
						Route::post('delete', 'destroy');
					});
				
				// Messenger
				Route::controller(MessagesController::class)
					->prefix('messages')
					->group(function ($router) {
						$router->pattern('id', '[0-9]+');
						Route::post('check-new', 'checkNew');
						Route::get('/', 'index');
						Route::post('/', 'store');
						Route::get('{id}', 'show');
						Route::put('{id}', 'update');
						Route::get('{id}/actions', 'actions');
						Route::post('actions', 'actions');
						Route::get('{id}/delete', 'destroy');
						Route::post('delete', 'destroy');
					});
				
				// Transactions
				Route::namespace('Transactions')
					->prefix('transactions')
					->group(function ($router) {
						Route::get('promotion', [TransactionsController::class, 'index']);
						Route::get('subscription', [TransactionsController::class, 'index']);
					});
			});
	});


// BROWSING
Route::namespace('Browsing')
	->prefix('browsing')
	->group(function ($router) {
		// Categories
		Route::controller(BrowsingCategoryController::class)
			->group(function ($router) {
				$router->pattern('id', '[0-9]+');
				Route::post('categories/select', 'getCategoriesHtml');
				Route::post('categories/{id}/fields', 'getCustomFieldsHtml');
			});
		
		// Location
		Route::namespace('Location')
			->group(function ($router) {
				$router->pattern('countryCode', getCountryCodeRoutePattern());
				Route::post('countries/{countryCode}/cities/autocomplete', AutoCompleteController::class);
				Route::controller(SelectBoxController::class)
					->group(function ($router) {
						$router->pattern('id', '[0-9]+');
						Route::get('countries/{countryCode}/admins/{adminType}', 'getAdmins');
						Route::get('countries/{countryCode}/admins/{adminType}/{adminCode}/cities', 'getCities');
						Route::get('countries/{countryCode}/cities/{id}', 'getSelectedCity');
					});
				Route::controller(ModalController::class)
					->group(function ($router) {
						Route::post('locations/{countryCode}/admins/{adminType}', 'getAdmins');
						Route::post('locations/{countryCode}/admins/{adminType}/{adminCode}/cities', 'getCities');
						Route::post('locations/{countryCode}/cities', 'getCities');
					});
			});
	});


// FEEDS
Route::feeds();


if (!$isDomainmappingAvailable) {
	// SITEMAPS (XML)
	Route::controller(SitemapsController::class)
		->group(function ($router) {
			$router->pattern('countryCode', getCountryCodeRoutePattern());
			Route::get('{countryCode}/sitemaps.xml', 'getSitemapIndexByCountry');
			Route::get('{countryCode}/sitemaps/pages.xml', 'getPagesSitemapByCountry');
			Route::get('{countryCode}/sitemaps/categories.xml', 'getCategoriesSitemapByCountry');
			Route::get('{countryCode}/sitemaps/cities.xml', 'getCitiesSitemapByCountry');
			Route::get('{countryCode}/sitemaps/posts.xml', 'getListingsSitemapByCountry');
		});
}


// PAGES
Route::namespace('Page')
	->group(function ($router) {
		Route::get(dynamicRoute('routes.pricing'), [PricingController::class, 'index']);
		Route::get(dynamicRoute('routes.pageBySlug'), [CmsController::class, 'index']);
		Route::controller(ContactController::class)
			->group(function ($router) {
				Route::get(dynamicRoute('routes.contact'), 'showForm');
				Route::post(dynamicRoute('routes.contact'), 'postForm');
			});
	});

// SITEMAP (HTML)
Route::get(dynamicRoute('routes.sitemap'), SitemapController::class);

// SEARCH
Route::namespace('Search')
	->group(function ($router) {
		$router->pattern('id', '[0-9]+');
		$router->pattern('username', '[a-zA-Z0-9]+');
		Route::get(dynamicRoute('routes.search'), [SearchController::class, 'index']);
		Route::get(dynamicRoute('routes.searchPostsByUserId'), [UserController::class, 'index']);
		Route::get(dynamicRoute('routes.searchPostsByUsername'), [UserController::class, 'profile']);
		Route::get(dynamicRoute('routes.searchPostsByTag'), [TagController::class, 'index']);
		Route::get(dynamicRoute('routes.searchPostsByCity'), [CityController::class, 'index']);
		Route::get(dynamicRoute('routes.searchPostsBySubCat'), [CategoryController::class, 'index']);
		Route::get(dynamicRoute('routes.searchPostsByCat'), [CategoryController::class, 'index']);
	});
