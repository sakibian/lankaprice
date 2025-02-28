@extends('admin.layouts.master')

@section('content')
<div class="container"> 
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Create Blog</h1>
        <a href="{{ route('admin.blogs') }}" class="btn btn-secondary">Back</a>
    </div>

    <!-- Blog Form -->
    <form id="blogForm" action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" id="slug" name="slug" class="form-control" required>
            <small class="text-muted d-block mt-2">URL: <span id="slug-url">{{ url('blog/') }}/</span><span id="slug-preview"></span></small>
        </div>


        <div class="mb-3">
            <label class="form-label">Tags</label>
            <select name="tags[]" class="form-control select2" multiple>
                @foreach($tags as $tag)
                    <option value="{{ $tag->name }}">{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>

        
        
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea id="contentEditor" name="content" class="form-control" required>{{ old('content') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" id="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-success mt-3">Create</button>
    </form>
</div>

<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js') }}"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    // Initialize TinyMCE
    tinymce.init({
        selector: "#contentEditor",
        plugins: "lists link table code",
        toolbar: "undo redo | bold italic underline | forecolor backcolor | bullist numlist blockquote table | link unlink | alignleft aligncenter alignright | outdent indent | fontsizeselect | code",
        height: 400,
        setup: editor => {
            editor.on("change", () => tinymce.triggerSave()); // Ensure content is saved
        }
    });

    // Slug Generation
    const titleInput = document.getElementById("title");
    const slugInput = document.getElementById("slug");
    const slugPreview = document.getElementById("slug-preview");

    const generateSlug = text => text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')  
        .replace(/\s+/g, '-')     
        .replace(/^-+|-+$/g, '');  

    const updateSlug = () => {
        const slug = generateSlug(titleInput.value);
        slugInput.value = slug;
        slugPreview.textContent = slug;
    };

    titleInput.addEventListener("input", updateSlug);
    slugInput.addEventListener("input", () => slugPreview.textContent = slugInput.value);
    updateSlug(); // Initialize on page load

    // Form Submission Validation
    document.getElementById("blogForm").addEventListener("submit", event => {
        tinymce.triggerSave(); // Ensure TinyMCE content is saved
        const content = document.getElementById("contentEditor").value.trim();
        const image = document.getElementById("image");

        if (!content) {
            alert("Content cannot be empty.");
            tinymce.get("contentEditor").focus(); // Focus TinyMCE editor
            event.preventDefault();
            return;
        }

        if (image.files.length) {
            const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
            const maxSize = 2 * 1024 * 1024; // 2MB
            const file = image.files[0];

            if (!allowedTypes.includes(file.type) || file.size > maxSize) {
                alert("Invalid image file. Only JPG, PNG, and GIF up to 2MB are allowed.");
                event.preventDefault();
            }
        }
    });
});

</script>
@endsection
