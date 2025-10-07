{{-- Recursive partial for rendering family tree nodes --}}
@php
    $member = $node['member'];
    $children = $node['children'] ?? [];
    $isDeceased = $member->reg_number == 'Late';
    $hasChildren = count($children) > 0;
    
    // Calculate age if DOB exists
    $age = null;
    if ($member->dob && $member->dob != '0000-00-00' && $member->dob != 'null') {
        try {
            $age = \Carbon\Carbon::parse($member->dob)->age;
        } catch (\Exception $e) {
            $age = null;
        }
    }
@endphp

<div class="node-wrapper">
    {{-- Toggle button (only if has children) --}}
    @if($hasChildren)
        <span class="toggle-btn">−</span>
    @endif
    
    {{-- Member Card with color coding --}}
    <div class="member-card {{ $isDeceased ? 'deceased' : 'alive' }}" 
         title="{{ $member->first_name }} {{ $member->last_name }}{{ $isDeceased ? ' (Deceased)' : ' (Alive)' }}">
        
        {{-- Name --}}
        <div class="name">
            @if($isDeceased)
                † 
            @endif
            {{ $member->first_name }} {{ $member->last_name }}
        </div>
        
        {{-- Age (if available) --}}
        @if($age)
            <div class="info">
                Age {{ $age }}
            </div>
        @endif
    </div>
</div>

{{-- Render Children if any --}}
@if($hasChildren)
    <ul class="children-container">
        @foreach($children as $child_node)
            <li>
                @include('partials.family-tree-node', ['node' => $child_node])
            </li>
        @endforeach
    </ul>
@endif
