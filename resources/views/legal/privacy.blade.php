@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <x-page-header title="Privacy Policy" eyebrow="Legal" subtitle="How we collect, use, and protect your information." />

  <div class="bg-white rounded shadow p-6 prose prose-sm max-w-none">
    <p>Last Updated: {{ now()->toDateString() }}</p>

    <h2>1. Information We Collect</h2>
    <p>We may collect contact and usage information necessary to provide our services, including name, email, phone, and address data entered in the application.</p>

    <h2>2. How We Use Information</h2>
    <p>We use the collected information to operate, maintain, and improve the application, and to communicate with you about updates, service notifications, and support.</p>

    <h2>3. Sharing</h2>
    <p>We do not sell your personal information. We may share information with service providers that assist us in operating the application, subject to confidentiality obligations.</p>

    <h2>4. Security</h2>
    <p>We implement reasonable safeguards to protect your information. However, no method of transmission or storage is completely secure, and we cannot guarantee absolute security.</p>

    <h2>5. Your Choices</h2>
    <p>You may access, update, or delete certain information within the application. For additional requests, please contact support.</p>

    <h2>6. Contact</h2>
    <p>For questions about this Privacy Policy, contact our support team.</p>
  </div>
</div>
@endsection
