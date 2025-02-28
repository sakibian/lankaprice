@extends('front.layouts.main')

@section('content')
<div class="container my-5">
    <!-- Blog Hero Section -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm mb-4">
                @if($blog->image)
                    <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" class="card-img-top w-100" style="max-height: 400px; object-fit: cover;">
                @else
                    <img src="{{ asset('images/default-blog.jpg') }}" alt="No Image" class="card-img-top w-100" style="max-height: 400px; object-fit: cover;">
                @endif
                <div class="card-body text-center">
                    <h1 class="card-title">{{ $blog->title }}</h1>
                    <p class="text-muted">By Admin • {{ $blog->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Blog Content -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0 p-4">
                <div class="card-body">
                    <p class="card-text" style="line-height: 0.9; font-size: 22px;">
                        {{-- {!! nl2br(e($blog->content)) !!} --}}
                        {{-- {!! strip_tags($blog->content) !!} --}}
                        {!! $blog->content !!}
                    </p>
                </div>
            </div>
        </div>
        <!-- Show Tags -->
        <div class="col-lg-8 mx-auto mt-3">
            <strong>Tags:</strong>
            @foreach($blog->tags as $tag)
                <span class="badge bg-primary">{{ $tag->name }}</span>
            @endforeach
        </div>
    </div>


    <!-- Back to Blog List -->
    <div class="text-center mt-4">
        <a href="{{ route('blog.index') }}" class="btn btn-outline-primary">← Back to Blogs</a>
    </div>
</div>
@endsection
