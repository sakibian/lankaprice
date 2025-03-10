@php
	$widget ??= [];
	$posts = (array)data_get($widget, 'posts');
	$totalPosts = (int)data_get($widget, 'totalPosts', 0);
	
	$sectionOptions ??= [];
	$hideOnMobile = (data_get($sectionOptions, 'hide_on_mobile') == '1') ? ' hidden-sm' : '';
	
	$isFromHome ??= false;
@endphp
@if ($totalPosts > 0)
	@if ($isFromHome)
		@include('front.sections.spacer', ['hideOnMobile' => $hideOnMobile])
	@endif
	<div class="container{{ $isFromHome ? '' : ' my-3' }}{{ $hideOnMobile }}">
		<div class="col-xl-12 content-box layout-section">
			<div class="row row-featured row-featured-category">
				
				<div class="col-xl-12 box-title no-border">
					<div class="inner">
						<h2>
							<span class="title-3">{!! data_get($widget, 'title') !!}</span>
							<a href="{{ data_get($widget, 'link') }}" class="sell-your-item">
								{{ t('View more') }} <i class="fa-solid fa-bars"></i>
							</a>
						</h2>
					</div>
				</div>
				
				<div class="col-12">
					<div class="category-list {{ config('settings.listings_list.display_mode', 'make-grid') }} noSideBar">
						<div id="postsList" class="category-list-wrapper posts-wrapper row no-margin">
							@if (config('settings.listings_list.display_mode') == 'make-list')
								@include('front.search.inc.posts.template.list')
							@elseif (config('settings.listings_list.display_mode') == 'make-compact')
								@include('front.search.inc.posts.template.compact')
							@else
								@include('front.search.inc.posts.template.grid')
							@endif
							
							<div style="clear: both"></div>
							
							@if (data_get($sectionOptions, 'show_view_more_btn') == '1')
								<div class="mb20 text-center">
									<a href="{{ urlGen()->searchWithoutQuery() }}" class="btn btn-default mt10">
										<i class="bi bi-box-arrow-in-right"></i> {{ t('View more') }}
									</a>
								</div>
							@endif
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
@endif

@section('after_scripts')
    @parent
@endsection
