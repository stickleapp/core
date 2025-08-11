<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    @if($heading)
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">{{ $heading }}</h3>
        @if($description)
        <p class="text-sm text-gray-600 mt-1">{{ $description }}</p>
        @endif
    </div>
    @endif
    
    <div id="model-summary-content" class="p-4">
        @if($model)
        <!-- Model data provided -->
        <div class="space-y-4">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                        {{ substr($model['name'] ?? 'M', 0, 1) }}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-lg font-medium text-gray-900">{{ $model['name'] ?? 'Unknown Model' }}</h4>
                    <p class="text-sm text-gray-500">{{ $model['type'] ?? 'Model Type' }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ $model['id'] ?? '---' }}</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">ID</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $model['status'] ?? 'Active' }}</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Status</div>
                </div>
            </div>
            
            @if(isset($model['attributes']) && is_array($model['attributes']))
            <div class="pt-4 border-t border-gray-100">
                <h5 class="text-sm font-medium text-gray-900 mb-3">Attributes</h5>
                <div class="space-y-2">
                    @foreach($model['attributes'] as $key => $value)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ ucfirst($key) }}:</span>
                        <span class="text-gray-900 font-medium">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @else
        <!-- No model selected -->
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-gray-100 rounded-lg mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h4 class="text-sm font-medium text-gray-900 mb-2">No Model Selected</h4>
            <p class="text-sm text-gray-500">Select a user or model to view detailed information</p>
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modelSummaryContent = document.getElementById('model-summary-content');
        
        // Function to update model summary
        window.updateModelSummary = function(modelData) {
            if (!modelData) {
                modelSummaryContent.innerHTML = `
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">No Model Selected</h4>
                        <p class="text-sm text-gray-500">Select a user or model to view detailed information</p>
                    </div>
                `;
                return;
            }
            
            let attributesHtml = '';
            if (modelData.attributes && typeof modelData.attributes === 'object') {
                attributesHtml = `
                    <div class="pt-4 border-t border-gray-100">
                        <h5 class="text-sm font-medium text-gray-900 mb-3">Attributes</h5>
                        <div class="space-y-2">
                            ${Object.entries(modelData.attributes).map(([key, value]) => `
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">${key.charAt(0).toUpperCase() + key.slice(1)}:</span>
                                    <span class="text-gray-900 font-medium">${value}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            modelSummaryContent.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                                ${(modelData.name || 'M').charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-lg font-medium text-gray-900">${modelData.name || 'Unknown Model'}</h4>
                            <p class="text-sm text-gray-500">${modelData.type || 'Model Type'}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-indigo-600">${modelData.id || '---'}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">ID</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">${modelData.status || 'Active'}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Status</div>
                        </div>
                    </div>
                    
                    ${attributesHtml}
                </div>
            `;
        };
        
        console.log('Model summary component loaded - use window.updateModelSummary(data) to update content');
    });
</script>