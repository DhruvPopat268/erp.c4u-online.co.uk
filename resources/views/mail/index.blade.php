@extends('layouts.admin')

@section('page-title', 'Send Test Email')

@section('content')

<section class="section">
    <div class="section-header">
        <h1>Send Test Email</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Send Test Email</div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- Success / Error Alerts --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            {{-- SMTP Info Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-server mr-2"></i>Current SMTP Configuration</h4>
                </div>
                <div class="card-body">
                    <div class="row text-sm">
                        <div class="col-sm-6 mb-2">
                            <span class="text-muted">Host:</span>
                            <strong class="ml-1">{{ $smtpInfo['host'] }}</strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <span class="text-muted">Port:</span>
                            <strong class="ml-1">{{ $smtpInfo['port'] }}</strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <span class="text-muted">Encryption:</span>
                            <strong class="ml-1">{{ strtoupper($smtpInfo['encryption']) }}</strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <span class="text-muted">Username:</span>
                            <strong class="ml-1">{{ $smtpInfo['username'] }}</strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <span class="text-muted">From Address:</span>
                            <strong class="ml-1">{{ $smtpInfo['from'] }}</strong>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <span class="text-muted">From Name:</span>
                            <strong class="ml-1">{{ $smtpInfo['from_name'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compose Email Card --}}
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-envelope mr-2"></i>Compose Email</h4>
                </div>
                <form action="{{ route('mail.send') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- To --}}
                        <div class="form-group">
                            <label for="to" class="form-label">To <span class="text-danger">*</span></label>
                            <input type="email"
                                   id="to"
                                   name="to"
                                   class="form-control @error('to') is-invalid @enderror"
                                   placeholder="recipient@example.com"
                                   value="{{ old('to') }}"
                                   required>
                            @error('to')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Subject --}}
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="subject"
                                   name="subject"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   placeholder="e.g. Hello, Welcome to the system!"
                                   value="{{ old('subject', 'Test Email from PTC Transport') }}"
                                   required>
                            @error('subject')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Body --}}
                        <div class="form-group">
                            <label for="body" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea id="body"
                                      name="body"
                                      class="form-control @error('body') is-invalid @enderror"
                                      rows="8"
                                      placeholder="Hello, welcome to the system! This is a test email."
                                      required>{{ old('body', "Hello,\n\nWelcome to the system! This is a test email sent from PTC Transport ERP.\n\nRegards,\nPTC Transport Team") }}</textarea>
                            @error('body')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer text-right">
                        <button type="reset" class="btn btn-secondary mr-2">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i> Send Email
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</section>

@endsection
