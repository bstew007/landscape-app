@php
    // Check if estimate already has a job
    $hasJob = $estimate->job()->exists();
    $job = $hasJob ? $estimate->job : null;
    
    // Only show if estimate is approved
    $canConvert = $estimate->status === 'approved' && !$hasJob;
@endphp

@if($hasJob)
    {{-- Show link to existing job --}}
    <a href="{{ route('jobs.show', $job) }}" 
       class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg bg-brand-100 text-brand-800 text-sm font-semibold border border-brand-200 hover:bg-brand-200 transition">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            <path d="M9 10h6M9 14h6"/>
        </svg>
        View Job: {{ $job->job_number }}
    </a>
@elseif($canConvert)
    {{-- Show convert to job button --}}
    <form method="POST" action="{{ route('estimates.create-job', $estimate) }}" class="inline-block" 
          x-data="{ 
              converting: false,
              async submit(e) {
                  e.preventDefault();
                  if (this.converting) return;
                  
                  if (!confirm('Convert this estimate to a job? This will create a new job with all work areas and line items.')) {
                      return;
                  }
                  
                  this.converting = true;
                  const form = e.target;
                  
                  try {
                      const response = await fetch(form.action, {
                          method: 'POST',
                          headers: {
                              'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                              'Accept': 'application/json',
                          },
                      });
                      
                      const data = await response.json();
                      
                      if (response.ok) {
                          if (data.redirect) {
                              window.location.href = data.redirect;
                          } else {
                              window.location.reload();
                          }
                      } else {
                          alert(data.message || 'Failed to create job. Please try again.');
                          this.converting = false;
                      }
                  } catch (error) {
                      console.error('Error creating job:', error);
                      alert('An error occurred. Please try again.');
                      this.converting = false;
                  }
              }
          }"
          @submit="submit">
        @csrf
        <button type="submit" 
                :disabled="converting"
                class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg bg-brand-800 text-white text-sm font-semibold hover:bg-brand-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" x-show="!converting">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                <path d="M12 11v6M9 14h6"/>
            </svg>
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" x-show="converting" x-cloak>
                <path d="M21 12a9 9 0 11-6.219-8.56"/>
            </svg>
            <span x-text="converting ? 'Creating Job...' : 'Convert to Job'"></span>
        </button>
    </form>
@elseif($estimate->status !== 'approved')
    {{-- Show why it can't be converted --}}
    <div class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg bg-gray-100 text-gray-500 text-sm border border-gray-200">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Approve estimate to create job
    </div>
@endif
