@php
	$searchFormOptions = $searchFormOptions ?? [];
	$locationsOptions = $locationsOptions ?? [];
@endphp
<style>
/* === Homepage: Search Form Area === */
@if (!empty($searchFormOptions['height']))
	<?php $searchFormOptions['height'] = forceToInt($searchFormOptions['height']) . 'px'; ?>
	#homepage .intro:not(.only-search-bar) {
		height: {{ $searchFormOptions['height'] }};
		max-height: {{ $searchFormOptions['height'] }};
	}
@endif
@if (!empty($searchFormOptions['background_color']))
	#homepage .intro:not(.only-search-bar) {
		background: {{ $searchFormOptions['background_color'] }};
	}
@endif
@php
	$bgImgFound = false;
	$bgImgDarken = data_get($searchFormOptions, 'background_image_darken', 0.0);
@endphp
@if (!empty(config('country.background_image_url')))
	#homepage .intro:not(.only-search-bar) {
		background-image: linear-gradient(rgba(0, 0, 0, {{ $bgImgDarken }}),rgba(0, 0, 0, {{ $bgImgDarken }})),url({{ config('country.background_image_url') }});
		background-size: cover;
	}
	@php
		$bgImgFound = true;
	@endphp
@endif
@if (!$bgImgFound)
	@if (!empty($searchFormOptions['background_image_url']))
		#homepage .intro:not(.only-search-bar) {
			background-image: linear-gradient(rgba(0, 0, 0, {{ $bgImgDarken }}),rgba(0, 0, 0, {{ $bgImgDarken }})),url({{ $searchFormOptions['background_image_url'] }});
			background-size: cover;
		}
	@endif
@endif
@if (!empty($searchFormOptions['big_title_color']))
	#homepage .intro:not(.only-search-bar) h1 {
		color: {{ $searchFormOptions['big_title_color'] }};
	}
@endif
@if (!empty($searchFormOptions['sub_title_color']))
	#homepage .intro:not(.only-search-bar) p {
		color: {{ $searchFormOptions['sub_title_color'] }};
	}
@endif
@if (!empty($searchFormOptions['form_border_width']))
	<?php $searchFormOptions['form_border_width'] = forceToInt($searchFormOptions['form_border_width']) . 'px'; ?>
	#homepage .search-row .search-col:first-child .search-col-inner,
	#homepage .search-row .search-col .search-col-inner,
	#homepage .search-row .search-col .search-btn-border {
		border-width: {{ $searchFormOptions['form_border_width'] }};
	}
	
	@media (max-width: 767px) {
		.search-row .search-col:first-child .search-col-inner,
		.search-row .search-col .search-col-inner,
		.search-row .search-col .search-btn-border {
			border-width: {{ $searchFormOptions['form_border_width'] }};
		}
	}
@endif
<?php
if (!empty($searchFormOptions['form_border_radius'])) {
	$formBorderRadius = forceToInt($searchFormOptions['form_border_radius']);
	
	// Based on default radius
	$fieldsBorderRadius = (int)round((($formBorderRadius * 18) / 24));
	
	// Based on the default radius & default border width
	if (!empty($searchFormOptions['form_border_width'])) {
		$formBorderWidth = forceToInt($searchFormOptions['form_border_width']);
		
		// Get the difference between the default wrapper & the fields radius, based on the default border width
		$borderRadiusDiff = (24 - 18) / 5;
		
		// Apply the diff. obtained above to the customized wrapper radius to get the fields radius
		$fieldsBorderRadius = (int)round(($formBorderRadius - $borderRadiusDiff));
	}
} else {
	$formBorderRadius = 24;
	$fieldsBorderRadius = 24;
}

$formBorderRadiusOut = getFormBorderRadiusCSS($formBorderRadius, $fieldsBorderRadius);
?>

{!! $formBorderRadiusOut !!}

@if (!empty($searchFormOptions['form_border_color']))
	#homepage .search-row .search-col:first-child .search-col-inner,
	#homepage .search-row .search-col .search-col-inner,
	#homepage .search-row .search-col .search-btn-border {
		border-color: {{ $searchFormOptions['form_border_color'] }};
	}
	
	@media (max-width: 767px) {
		#homepage .search-row .search-col:first-child .search-col-inner,
		#homepage .search-row .search-col .search-col-inner,
		#homepage .search-row .search-col .search-btn-border {
			border-color: {{ $searchFormOptions['form_border_color'] }};
		}
	}
@endif
@if (!empty($searchFormOptions['form_btn_background_color']))
	.skin #homepage button.btn-search {
		background-color: {{ $searchFormOptions['form_btn_background_color'] }};
		border-color: {{ $searchFormOptions['form_btn_background_color'] }};
	}
@endif
@if (!empty($searchFormOptions['form_btn_text_color']))
	.skin #homepage button.btn-search {
		color: {{ $searchFormOptions['form_btn_text_color'] }};
	}
@endif
@if (!empty(config('settings.style.page_width')))
	<?php $pageWidth = forceToInt(config('settings.style.page_width')) . 'px'; ?>
	@media (min-width: 1200px) {
		#homepage .intro.only-search-bar .container {
			max-width: {{ $pageWidth }};
		}
	}
@endif

/* === Homepage: Locations & SVG Map === */
@if (!empty($locationsOptions['background_color']))
	#homepage .inner-box {
		background: {{ $locationsOptions['background_color'] }};
	}
@endif
@if (!empty($locationsOptions['border_width']))
	<?php $locationsOptions['border_width'] = forceToInt($locationsOptions['border_width']) . 'px'; ?>
	#homepage .inner-box {
		border-width: {{ $locationsOptions['border_width'] }};
	}
@endif
@if (!empty($locationsOptions['border_color']))
	#homepage .inner-box {
		border-color: {{ $locationsOptions['border_color'] }};
	}
@endif
@if (!empty($locationsOptions['text_color']))
	#homepage .inner-box,
	#homepage .inner-box p,
	#homepage .inner-box h1,
	#homepage .inner-box h2,
	#homepage .inner-box h3,
	#homepage .inner-box h4,
	#homepage .inner-box h5 {
		color: {{ $locationsOptions['text_color'] }};
	}
@endif
@if (!empty($locationsOptions['link_color']))
	#homepage .inner-box a {
		color: {{ $locationsOptions['link_color'] }};
	}
@endif
@if (!empty($locationsOptions['link_color_hover']))
	#homepage .inner-box a:hover,
	#homepage .inner-box a:focus {
		color: {{ $locationsOptions['link_color_hover'] }};
	}
@endif
</style>
