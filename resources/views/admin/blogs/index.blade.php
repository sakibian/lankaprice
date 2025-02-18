@extends('admin.layouts.master')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manage Blogs</h1>
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">Create New Blog</a>
    </div>
    <div class="card rounded-2 px-4">
        <table class="table mt-3 table-hover">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($blogs as $blog)
                <tr>
                    <td>
                        @if($blog->image)
                            <img src="{{ asset('storage/' . $blog->image) }}" alt="Blog Image" width="80" height="50">
                        @else
                            <span>No Image</span>
                        @endif
                    </td>
                    <td>{{ $blog->title }}</td>
                    <td>
                        <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-warning btn-xs">Edit</a>
                        <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>    
    </div>
</div>
@endsection
