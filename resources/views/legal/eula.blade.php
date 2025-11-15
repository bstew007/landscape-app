@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <x-page-header title="End-User License Agreement" eyebrow="Legal" subtitle="Please read this End-User License Agreement (EULA) carefully." />

  <div class="bg-white rounded shadow p-6 prose prose-sm max-w-none">
    <p>Last Updated: {{ now()->toDateString() }}</p>

    <h2>1. License Grant</h2>
    <p>We grant you a limited, non-exclusive, non-transferable, revocable license to access and use this application solely for your internal business purposes, subject to this EULA.</p>

    <h2>2. Restrictions</h2>
    <p>You agree not to copy, modify, distribute, sell, lease, reverse engineer, or otherwise attempt to extract the source code of the application, unless laws prohibit those restrictions or you have our written permission.</p>

    <h2>3. Data & Privacy</h2>
    <p>Your use of the application is also governed by our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>. By using the application, you consent to the collection and use of information as described there.</p>

    <h2>4. Disclaimer</h2>
    <p>The application is provided on an "AS IS" and "AS AVAILABLE" basis. We disclaim all warranties, express or implied, to the maximum extent permitted by law, including implied warranties of merchantability, fitness for a particular purpose, and non-infringement.</p>

    <h2>5. Limitation of Liability</h2>
    <p>To the maximum extent permitted by law, we shall not be liable for any indirect, incidental, special, consequential, or exemplary damages arising from your use of the application.</p>

    <h2>6. Termination</h2>
    <p>We may suspend or terminate your access to the application at any time for any reason, including violation of this EULA. Upon termination, your right to use the application will immediately cease.</p>

    <h2>7. Governing Law</h2>
    <p>This EULA is governed by the laws of your company’s operating jurisdiction, without regard to its conflict of law principles.</p>

    <h2>8. Contact</h2>
    <p>If you have any questions about this EULA, please contact us at your designated support email.</p>
  </div>
</div>
@endsection
