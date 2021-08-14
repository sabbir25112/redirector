@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Add Redirect</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('redirect.store') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="incoming" class="col-md-4 col-form-label text-md-right">Incoming Domain</label>

                                <div class="col-md-6">
                                    <input id="incoming" type="text"
                                           class="form-control @error('incoming') is-invalid @enderror"
                                           name="incoming"
                                           value="{{ old('incoming') }}"
                                           required
                                           placeholder="getneflixtoday.com (without http/https)"
                                    >

                                    @error('incoming')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="outgoing" class="col-md-4 col-form-label text-md-right">Destination URL</label>

                                <div class="col-md-6">
                                    <input id="outgoing" type="text"
                                           class="form-control @error('outgoing') is-invalid @enderror"
                                           name="outgoing"
                                           value="{{ old('outgoing') }}"
                                           required
                                           placeholder="enter destination URL with http/https"
                                    >

                                    @error('outgoing')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Add Redirect
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
