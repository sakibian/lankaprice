@extends('front.layouts.main')

@section('content')
<div class="container my-5">
    <h1 class="mb-4 text-center">Latest Blog Posts</h1>

    <div class="row">
        @foreach($blogs as $blog)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0">
                <a href="{{ route('blog.show', $blog->slug) }}">
                    <img src="{{ $blog->image ? asset('storage/' . $blog->image) : asset('images/default-blog.jpg') }}" 
                         alt="{{ $blog->title }}" 
                         class="card-img-top rounded-top" 
                         style="height: 200px; object-fit: cover;">
                </a>
                <div class="card-body">
                    <h3 class="card-title">
                        <a href="{{ route('blog.show', $blog->slug) }}" class="text-dark text-decoration-none">
                            {{ Str::limit($blog->title, 50) }}
                        </a>
                    </h3>
                    <p class="text-muted small">By Admin • {{ $blog->created_at->format('M d, Y') }}</p>
                    <p class="card-text">{!! Str::limit(strip_tags($blog->content), 100) !!}</p>
                    
                     <!-- Show Tags -->
                     <p class="mt-2">
                        @foreach($blog->tags as $tag)
                            <span class="badge bg-primary">{{ $tag->name }}</span>
                        @endforeach
                    </p>

                    
                    <a href="{{ route('blog.show', $blog->slug) }}" class="btn btn-primary btn-sm">Read More</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $blogs->links() }}
    </div>
</div>
@endsection
