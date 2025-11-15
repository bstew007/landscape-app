@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <x-page-header title="Terms of Service" eyebrow="Legal" subtitle="Your agreement to use this application." />

  <div class="bg-white rounded shadow p-6 prose prose-sm max-w-none">
    <p>Last Updated: {{ now()->toDateString() }}</p>

    <h2>1. Acceptance of Terms</h2>
    <p>By using this application, you agree to be bound by these Terms of Service and all applicable laws and regulations.</p>

    <h2>2. Accounts</h2>
    <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities under your account.</p>

    <h2>3. Acceptable Use</h2>
    <p>You agree not to misuse the application, including attempting unauthorized access or interfering with its operation.</p>

    <h2>4. Modifications</h2>
    <p>We may update these terms from time to time. Continued use of the application constitutes acceptance of the revised terms.</p>

    <h2>5. Contact</h2>
    <p>For questions about these terms, please contact our support team.</p>
  </div>
</div>
@endsection
