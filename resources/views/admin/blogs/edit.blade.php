@extends('admin.layouts.master')

@section('content')
<div class="container">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Edit Blog</h1>
        <div>
            <a href="{{ route('admin.blogs') }}" class="btn btn-secondary">Back</a>
            <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
    
    <div class="card rounded-2 p-4">
        <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
    
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="{{ $blog->title }}" required>
            
            <label>Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ $blog->slug }}" required>
            
            <label>Content</label>
            <textarea id="contentEditor" name="content" class="form-control" required>{{ $blog->content }}</textarea>
    
            <label>Image</label>
            <input type="file" name="image" class="form-control">
    
            <button type="submit" class="btn btn-success mt-3">Update</button>
        </form>
    </div>
</div>


<!-- Include TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#contentEditor',
        height: 400,
        plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
    });
</script>
@endsection
