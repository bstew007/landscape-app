<div x-data="{ showPruning: true }" class="border p-4 rounded mb-6">
    <h2 @click="showPruning = !showPruning" class="text-lg font-bold cursor-pointer">
        🌿 Pruning Tasks
    </h2>

    <div x-show="showPruning" class="space-y-6 mt-4">

        {{-- Shearing --}}
        <div>
            <h3 class="font-semibold">Shearing (Small Shrubs)</h3>
            <div class="grid grid-cols-3 gap-4">
                @include('components.input', ['label' => 'Number of Shrubs', 'name' => 'pruning[shearing][count]'])
                @include('components.input', ['label' => 'Rate (shrubs/hr)', 'name' => 'pruning[shearing][rate_per_hour]'])
                @include('components.input', ['label' => 'Labor Rate ($/hr)', 'name' => 'pruning[shearing][labor_rate]'])
            </div>
        </div>

        {{-- Hand Pruning --}}
        <div>
            <h3 class="font-semibold">Hand Pruning (Precision Shrubs)</h3>
            <div class="grid grid-cols-3 gap-4">
                @include('components.input', ['label' => 'Number of Shrubs', 'name' => 'pruning[hand_pruning][count]'])
                @include('components.input', ['label' => 'Rate (shrubs/hr)', 'name' => 'pruning[hand_pruning][rate_per_hour]'])
                @include('components.input', ['label' => 'Labor Rate ($/hr)', 'name' => 'pruning[hand_pruning][labor_rate]'])
            </div>
        </div>

        {{-- Ladder Pruning --}}
        <div>
            <h3 class="font-semibold">Ladder Pruning</h3>
            <div class="grid grid-cols-3 gap-4">
                @include('components.input', ['label' => 'Number of Shrubs', 'name' => 'pruning[ladder_pruning][count]'])
                @include('components.input', ['label' => 'Rate (shrubs/hr)', 'name' => 'pruning[ladder_pruning][rate_per_hour]'])
                @include('components.input', ['label' => 'Labor Rate ($/hr)', 'name' => 'pruning[ladder_pruning][labor_rate]'])
            </div>
        </div>

        {{-- Tree Pruning --}}
        <div>
            <h3 class="font-semibold">Ornamental Tree Pruning</h3>
            <div class="grid grid-cols-3 gap-4">
                @include('components.input', ['label' => 'Number of Trees', 'name' => 'pruning[tree_pruning][count]'])
                @include('components.input', ['label' => 'Rate (trees/hr)', 'name' => 'pruning[tree_pruning][rate_per_hour]'])
                @include('components.input', ['label' => 'Labor Rate ($/hr)', 'name' => 'pruning[tree_pruning][labor_rate]'])
            </div>
        </div>

        {{-- Deadheading Perennials --}}
        <div>
            <h3 class="font-semibold">Deadheading Perennials</h3>
            <div class="grid grid-cols-3 gap-4">
                @include('components.input', ['label' => 'Area (sqft)', 'name' => 'pruning[deadheading][sqft]'])
                @include('components.input', ['label' => 'Rate (sqft/hr)', 'name' => 'pruning[deadheading][rate_sqft_per_hour]'])
                @include('components.input', ['label' => 'Labor Rate ($/hr)', 'name' => 'pruning[deadheading][labor_rate]'])
            </div>
        </div>

        {{-- Cutting Back Grasses --}}
        <div>
            <h3 class="font-semibold">Cutting Back Grasses</h3>
            <div class="grid grid-cols-3 gap-4">
                @include('components.input', ['label' => 'Number of Clumps', 'name' => 'pruning[cut_back_grasses][count]'])
                @include('components.input', ['label' => 'Rate (clumps/hr)', 'name' => 'pruning[cut_back_grasses][rate_per_hour]'])
                @include('components.input', ['label' => 'Labor Rate ($/hr)', 'name' => 'pruning[cut_back_grasses][labor_rate]'])
            </div>
        </div>

        {{-- Cutting Back Annuals --}}
        <div>
            <h3 class="font-semibold">Cutting Back Annuals</h3>
            <div class="grid grid-cols-3 gap-4">
                @include('components.input', ['label' => 'Number of Plants', 'name' => 'pruning[cut_back_annuals][plants]'])
                @include('components.input', ['label' => 'Rate (plants/hr)', 'name' => 'pruning[cut_back_annuals][rate_per_hour]'])
                @include('components.input', ['label' => 'Labor Rate ($/hr)', 'name' => 'pruning[cut_back_annuals][labor_rate]'])
            </div>
        </div>

        {{-- Cleanup --}}
        <div>
            <h3 class="font-semibold">Cleanup of Pruning Debris</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cleanup Method</label>
                    <select name="pruning[cleanup][method]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="auto">Auto (as %)</option>
                        <option value="manual">Manual (hours)</option>
                    </select>
                </div>
                @include('components.input', ['label' => 'Percent Time (if auto)', 'name' => 'pruning[cleanup][percent_additional_time]'])
                @include('components.input', ['label' => 'Manual Hours (if manual)', 'name' => 'pruning[cleanup][manual_hours]'])
            </div>
        </div>

    </div>
</div>

