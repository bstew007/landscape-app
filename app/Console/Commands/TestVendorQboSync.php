<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Services\QboVendorService;
use Illuminate\Console\Command;

class TestVendorQboSync extends Command
{
    protected $signature = 'qbo:test-vendor-sync {contact_id?}';
    protected $description = 'Test syncing a vendor contact to QuickBooks';

    public function handle(QboVendorService $vendorService)
    {
        $contactId = $this->argument('contact_id');
        
        if (!$contactId) {
            // Find first vendor contact
            $vendor = Contact::where('contact_type', 'vendor')->first();
            
            if (!$vendor) {
                $this->error('No vendor contacts found. Please create a vendor contact first.');
                return 1;
            }
            
            $contactId = $vendor->id;
            $this->info("No contact_id provided, using first vendor: {$vendor->company_name} (ID: {$contactId})");
        } else {
            $vendor = Contact::find($contactId);
            
            if (!$vendor) {
                $this->error("Contact with ID {$contactId} not found.");
                return 1;
            }
            
            if ($vendor->contact_type !== 'vendor') {
                $this->warn("Warning: Contact {$contactId} is not a vendor (type: {$vendor->contact_type})");
                if (!$this->confirm('Continue anyway?')) {
                    return 1;
                }
            }
        }
        
        $this->info("Syncing vendor: {$vendor->company_name} (ID: {$vendor->id})");
        $this->info("Email: {$vendor->email}");
        $this->info("Phone: {$vendor->phone}");
        $this->info("Current qbo_vendor_id: " . ($vendor->qbo_vendor_id ?? 'null'));
        
        if ($vendor->qbo_vendor_id) {
            $this->warn("This vendor is already linked to QBO Vendor ID: {$vendor->qbo_vendor_id}");
            if (!$this->confirm('Update existing vendor?')) {
                return 0;
            }
        }
        
        $this->line('');
        $this->info('Syncing to QuickBooks...');
        
        try {
            $result = $vendorService->upsert($vendor);
            
            $this->line('');
            $this->info('✓ Success!');
            $this->line('');
            
            $vendor->refresh();
            
            $this->table(
                ['Field', 'Value'],
                [
                    ['qbo_vendor_id', $vendor->qbo_vendor_id],
                    ['qbo_sync_token', $vendor->qbo_sync_token],
                    ['qbo_last_synced_at', $vendor->qbo_last_synced_at?->format('Y-m-d H:i:s')],
                ]
            );
            
            if (isset($result['Vendor'])) {
                $this->line('');
                $this->info('QuickBooks Response:');
                $this->line('DisplayName: ' . ($result['Vendor']['DisplayName'] ?? 'N/A'));
                $this->line('CompanyName: ' . ($result['Vendor']['CompanyName'] ?? 'N/A'));
                $this->line('QBO Id: ' . ($result['Vendor']['Id'] ?? 'N/A'));
            }
            
            return 0;
            
        } catch (\Throwable $e) {
            $this->line('');
            $this->error('✗ Sync failed!');
            $this->error($e->getMessage());
            $this->line('');
            
            if ($this->option('verbose')) {
                $this->line($e->getTraceAsString());
            }
            
            return 1;
        }
    }
}
