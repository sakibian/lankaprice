<!-- Simple MDE - Markdown Editor -->
<div @include('admin.panel.inc.field_wrapper_attributes') >
    <label class="form-label fw-bolder">
	    {!! $field['label'] !!}
	    @if (isset($field['required']) && $field['required'])
		    <span class="text-danger">*</span>
	    @endif
    </label>
	@include('admin.panel.fields.inc.translatable_icon')
    <textarea
            id="simplemde_{{ $field['name'] }}"
            name="{{ $field['name'] }}"
            @include('admin.panel.inc.field_attributes', ['default_class' => 'form-control'])
    >{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}</textarea>
    
    {{-- HINT --}}
    @if (isset($field['hint']))
        <div class="form-text">{!! $field['hint'] !!}</div>
    @endif
</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($xPanel->checkIfFieldIsFirstOfItsType($field, $fields))
    
    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/simplemde/1.11.2/simplemde.min.css') }}">
    <style type="text/css">
        .CodeMirror-fullscreen, .editor-toolbar.fullscreen {
            z-index: 9999 !important;
        }
        .CodeMirror{
            min-height: auto !important;
        }
    </style>
    @endpush
    
    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script src="{{ asset('assets/plugins/simplemde/1.11.2/simplemde.min.js') }}"></script>
    @endpush

@endif

@push('crud_fields_scripts')
<script>
	var simplemde_{{ $field['name'] }} = new SimpleMDE({
		element: $("#simplemde_{{ $field['name'] }}")[0],
    @if(isset($field['simplemdeAttributes']))
    @foreach($field['simplemdeAttributes'] as $index => $value)
    {{$index}} : @if(is_bool($value)) {{ ($value?'true':'false') }} @else {!! '"'.$value.'"' !!} @endif,
    @endforeach
    @endif
    {!! isset($field['simplemdeAttributesRaw']) ? $field['simplemdeAttributesRaw'] : "" !!}
	});
	simplemde_{{ $field['name'] }}.options.minHeight = simplemde_{{ $field['name'] }}.options.minHeight || "300px";
	simplemde_{{ $field['name'] }}.codemirror.getScrollerElement().style.minHeight = simplemde_{{ $field['name'] }}.options.minHeight;
	$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
		setTimeout(function() { simplemde_{{ $field['name'] }}.codemirror.refresh(); }, 10);
	});
</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
