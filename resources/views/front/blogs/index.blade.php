@extends('front.layouts.main')

@section('content')
<div class="container my-5">
    <h1 class="mb-4 text-center">Latest Blog Posts</h1>

    <div class="row">
        @foreach($blogs as $blog)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0">
                @if($blog->image)
                    <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" class="card-img-top rounded-top" style="height: 200px; object-fit: cover;">
                @else
                    <img src="{{ asset('images/default-blog.jpg') }}" alt="No Image" class="card-img-top rounded-top" style="height: 200px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="{{ route('blog.show', $blog->slug) }}" class="text-dark text-decoration-none">
                            {{ Str::limit($blog->title, 50) }}
                        </a>
                    </h5>
                    <p class="text-muted small">By Admin • {{ $blog->created_at->format('M d, Y') }}</p>
                    <p class="card-text">{{ Str::limit($blog->content, 100) }}</p>
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
