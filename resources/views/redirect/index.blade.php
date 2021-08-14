@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Redirect') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Incoming</th>
                                <th scope="col">Outgoing</th>
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if ($redirects->count())
                                @foreach($redirects as $redirect)
                                    <tr>
                                        <form action="{{ route('redirect-destroy', $redirect->id) }}"
                                              id="delete_redirect_{{$redirect->id}}">
                                            @csrf
                                        </form>
                                        <th scope="row">{{ $redirect->id }}</th>
                                        <td>{{ $redirect->incoming }}</td>
                                        <td>{{ $redirect->outgoing }}</td>
                                        <td>
                                            <a href="{{ route('redirect.edit', $redirect->id) }}" class="btn btn-primary">Edit</a>
                                            <a href="#" onclick="confirmDelete('{{$redirect->id}}')" class="btn btn-danger">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <h5>No Data Found</h5>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                        {{ $redirects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        function confirmDelete(id) {
            if (confirm("Do you really want to delete?")) {
                $('#delete_redirect_' + id).submit();
            }
        }
    </script>
@endpush
