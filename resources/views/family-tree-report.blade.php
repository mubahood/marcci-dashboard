<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Tree - {{ $sacco ? $sacco->name : 'All Families' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: Arial, sans-serif; 
            background: #fff;
            padding: 10px;
            color: #333;
            font-size: 11px;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            border: 1px solid #ddd;
        }
        
        /* Header Section */
        .header {
            background: #f5f5f5;
            color: #333;
            padding: 15px;
            text-align: center;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header .subtitle {
            font-size: 12px;
            color: #666;
        }
        
        .header .meta {
            margin-top: 8px;
            font-size: 10px;
            color: #999;
        }
        
        /* Statistics Cards */
        .stats-section {
            padding: 10px;
            background: #fafafa;
            border-bottom: 1px solid #ddd;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .stat-card {
            background: white;
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        
        .stat-card .icon {
            font-size: 14px;
            margin-bottom: 3px;
        }
        
        .stat-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
        }
        
        .stat-card .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        
        /* Generation Distribution */
        .generation-stats {
            background: white;
            padding: 10px;
            border: 1px solid #ddd;
            margin-top: 8px;
        }
        
        .generation-stats h3 {
            margin-bottom: 8px;
            color: #000;
            font-size: 12px;
            font-weight: bold;
        }
        
        .generation-bar {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .generation-bar .gen-label {
            width: 100px;
            font-weight: 600;
            font-size: 10px;
        }
        
        .generation-bar .gen-visual {
            flex: 1;
            height: 18px;
            background: #333;
            position: relative;
            overflow: hidden;
        }
        
        .generation-bar .gen-count {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-weight: bold;
            font-size: 10px;
        }
        
        /* Tree Section */
        .tree-section {
            padding: 15px;
            background: white;
            overflow-x: auto;
        }
        
        .tree-section h2 {
            text-align: center;
            margin-bottom: 15px;
            color: #000;
            font-size: 14px;
            font-weight: bold;
        }
        
        /* Family Tree Structure - Vertical Layout */
        .tree {
            margin: 0;
            padding: 12px 0 0 20px;
            position: relative;
        }
        
        .tree ul {
            padding-left: 25px;
            position: relative;
            list-style: none;
            margin-top: 3px;
        }
        
        .tree li {
            position: relative;
            padding: 2px 0 2px 0;
            list-style: none;
            margin: 2px 0;
        }
        
        .tree li::before {
            content: '';
            position: absolute;
            top: 14px;
            left: -25px;
            border-left: 1px solid #999;
            border-bottom: 1px solid #999;
            width: 20px;
            height: 1px;
        }
        
        .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            left: -25px;
            border-left: 1px solid #999;
            height: 100%;
        }
        
        .tree li:last-child::after {
            height: 14px;
        }
        
        .tree li:last-child::before {
            border-radius: 0 0 0 3px;
        }
        
        /* Root node - no lines */
        .tree > li::before,
        .tree > li::after {
            display: none;
        }
        
        /* Expand/Collapse Toggle */
        .toggle-btn {
            display: inline-block;
            width: 16px;
            height: 16px;
            line-height: 14px;
            text-align: center;
            background: #333;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            cursor: pointer;
            margin-right: 3px;
            border: 1px solid #333;
            user-select: none;
            vertical-align: middle;
        }
        
        .toggle-btn:hover {
            background: #555;
        }
        
        .children-count {
            display: inline-block;
            font-size: 9px;
            color: #666;
            margin-right: 5px;
            vertical-align: middle;
            font-weight: bold;
        }
        
        .children-container {
            display: block;
        }
        
        .children-container.collapsed {
            display: none;
        }
        
        .node-wrapper {
            display: inline-block;
            vertical-align: middle;
        }
        
        /* Member Card */
        .member-card {
            display: inline-block;
            border: 1px solid #999;
            padding: 5px 8px;
            text-align: left;
            min-width: 120px;
            max-width: 180px;
            position: relative;
            vertical-align: middle;
        }
        
        /* Alive member - green tint */
        .member-card.alive {
            background: #f0fff4;
            border-color: #86efac;
        }
        
        /* Deceased member - gray */
        .member-card.deceased {
            border-color: #999;
            background: #e5e5e5;
            color: #666;
        }
        
        .member-card .name {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
            color: #000;
        }
        
        .member-card .info {
            font-size: 9px;
            color: #666;
            margin: 1px 0;
        }
        
        /* Actions */
        .actions {
            padding: 10px;
            text-align: center;
            background: #f5f5f5;
            border-top: 1px solid #ddd;
        }
        
        .btn {
            display: inline-block;
            padding: 6px 15px;
            margin: 0 3px;
            background: #333;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 11px;
        }
        
        .btn:hover {
            background: #555;
        }
        
        .btn.secondary {
            background: white;
            color: #333;
            border: 1px solid #333;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .member-card {
                min-width: 90px;
                max-width: 120px;
                padding: 4px 6px;
            }
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .actions {
                display: none;
            }
            
            .container {
                border: none;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🌳 {{ $sacco ? $sacco->name : 'All Families' }}</h1>
            <div class="subtitle">Family Tree Visualization</div>
            <div class="meta">
                Generated on: {{ date('F j, Y \a\t g:i A') }} | 
                Generated by: {{ Admin::user()->name }}
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon">👨‍👩‍👧‍👦</div>
                    <div class="value">{{ number_format($total_members) }}</div>
                    <div class="label">Total Members</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">🌱</div>
                    <div class="value">{{ $founders_count }}</div>
                    <div class="label">Root Founders</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">🎯</div>
                    <div class="value">{{ $max_generation }}</div>
                    <div class="label">Generations</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">👶</div>
                    <div class="value">{{ $avg_children }}</div>
                    <div class="label">Avg Children/Family</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">👨</div>
                    <div class="value">{{ number_format($male_count) }}</div>
                    <div class="label">Male Members</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">👩</div>
                    <div class="value">{{ number_format($female_count) }}</div>
                    <div class="label">Female Members</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">💚</div>
                    <div class="value">{{ number_format($alive_count) }}</div>
                    <div class="label">Alive</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">🕊️</div>
                    <div class="value">{{ number_format($deceased_count) }}</div>
                    <div class="label">Deceased</div>
                </div>
            </div>

            <!-- Generation Distribution -->
            <div class="generation-stats">
                <h3>📊 Generation Distribution</h3>
                @foreach($generation_distribution as $gen => $count)
                <div class="generation-bar">
                    <div class="gen-label">Generation {{ $gen }}:</div>
                    <div class="gen-visual" style="width: {{ ($count / $total_members) * 100 }}%">
                        <span class="gen-count">{{ $count }} members</span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Age Distribution -->
            <div class="generation-stats" style="margin-top: 20px;">
                <h3>👥 Age Distribution</h3>
                @foreach($age_groups as $group => $count)
                @if($count > 0)
                <div class="generation-bar">
                    <div class="gen-label">{{ $group }}:</div>
                    <div class="gen-visual" style="width: {{ ($count / $total_members) * 100 }}%">
                        <span class="gen-count">{{ $count }} members</span>
                    </div>
                </div>
                @endif
                @endforeach
            </div>

            <!-- Insights -->
            @if($largest_family)
            <div class="generation-stats" style="margin-top: 20px;">
                <h3>💡 Family Insights</h3>
                <p style="margin-bottom: 10px;"><strong>Largest Family Tree:</strong> {{ $largest_family->first_name }} {{ $largest_family->last_name }} ({{ $max_descendants }} descendants)</p>
                <p style="margin-bottom: 10px;"><strong>Active Contributors:</strong> {{ number_format($active_contributors) }} members ({{ round(($active_contributors / $total_members) * 100, 1) }}%)</p>
                <p style="margin-bottom: 10px;"><strong>Optional Contributors:</strong> {{ number_format($optional_contributors) }} members</p>
                <p><strong>System Admins:</strong> {{ number_format($admin_count) }} members</p>
            </div>
            @endif
        </div>

        <!-- Family Tree Visualization -->
        <div class="tree-section">
            <h2>🌳 Family Tree Structure</h2>
            
            <!-- Controls and Legend -->
            <div style="margin-bottom: 10px; padding: 8px; background: #f9f9f9; border: 1px solid #ddd; font-size: 10px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Legend:</strong> 
                    <span style="display: inline-block; margin-left: 10px;">
                        <span style="display: inline-block; width: 12px; height: 12px; background: #f0fff4; border: 1px solid #86efac; vertical-align: middle;"></span>
                        <span style="margin-left: 3px;">Alive</span>
                    </span>
                    <span style="display: inline-block; margin-left: 10px;">
                        <span style="display: inline-block; width: 12px; height: 12px; background: #e5e5e5; border: 1px solid #999; vertical-align: middle;"></span>
                        <span style="margin-left: 3px;">Deceased (†)</span>
                    </span>
                    <span style="display: inline-block; margin-left: 10px;">
                        <span style="margin-left: 3px;">(n) = number of children</span>
                    </span>
                </div>
                <div>
                    <button onclick="expandAll()" style="padding: 4px 10px; background: #333; color: #fff; border: 1px solid #333; cursor: pointer; font-size: 10px; margin-right: 5px;">⊕ Expand All</button>
                    <button onclick="collapseAll()" style="padding: 4px 10px; background: #333; color: #fff; border: 1px solid #333; cursor: pointer; font-size: 10px;">⊖ Collapse All</button>
                </div>
            </div>
            
            @if(count($tree_data) > 0)
                <div class="tree">
                    <ul>
                        @foreach($tree_data as $root_node)
                            <li>
                                @include('partials.family-tree-node', ['node' => $root_node])
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p style="text-align: center; color: #999; padding: 40px;">No family members found in this SACCO.</p>
            @endif
        </div>

        <!-- Actions -->
        <div class="actions">
            <button class="btn" onclick="window.print()">🖨️ Print</button>
            <button class="btn" onclick="copyReport()">📋 Copy</button>
            <a href="javascript:history.back()" class="btn secondary">← Back</a>
        </div>
    </div>

    <script>
        function copyReport() {
            const reportContent = document.body.innerText;
            navigator.clipboard.writeText(reportContent).then(() => {
                alert('Family tree data copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }

        // Expand/Collapse functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event to all toggle buttons
            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Find the parent node-wrapper, then go up to li, then find children-container
                    const nodeWrapper = this.closest('.node-wrapper');
                    const li = nodeWrapper.closest('li');
                    const childrenContainer = li.querySelector(':scope > .children-container');
                    if (childrenContainer) {
                        childrenContainer.classList.toggle('collapsed');
                        // Toggle button text
                        if (childrenContainer.classList.contains('collapsed')) {
                            this.textContent = '+';
                        } else {
                            this.textContent = '−';
                        }
                    }
                });
            });
        });
        
        // Collapse All
        function collapseAll() {
            document.querySelectorAll('.children-container').forEach(container => {
                container.classList.add('collapsed');
            });
            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.textContent = '+';
            });
        }
        
        // Expand All
        function expandAll() {
            document.querySelectorAll('.children-container').forEach(container => {
                container.classList.remove('collapsed');
            });
            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.textContent = '−';
            });
        }

        // Add tooltips on hover
        document.querySelectorAll('.member-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '100';
            });
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '';
            });
        });
    </script>

</body>
</html>
