# Family Tree - Vertical Layout Update

## Date: October 7, 2025

## Overview
Updated the family tree visualization from horizontal to vertical layout with expand/collapse functionality and color-coded alive vs deceased members.

---

## Changes Made

### 1. Layout Transformation: Horizontal → Vertical

**Previous**: Horizontal tree layout with members spreading left-to-right
**New**: Vertical tree layout with members arranged top-to-bottom

**CSS Changes** (`family-tree-report.blade.php`):
- Tree structure now uses vertical connecting lines
- Parent-child relationships flow downward
- Left-side vertical lines connect to horizontal branches
- More compact and easier to follow lineage

### 2. Expand/Collapse Functionality

**Toggle Buttons**:
- **−** button: Collapse children (hide descendants)
- **+** button: Expand children (show descendants)
- Only appears on members who have children
- 16x16px black buttons with white text

**Control Buttons**:
- **⊕ Expand All**: Expand entire tree (show all descendants)
- **⊖ Collapse All**: Collapse entire tree (show only root members)
- Located in actions bar at bottom

**JavaScript Features**:
```javascript
- Click toggle button to collapse/expand individual branches
- Collapse/Expand all buttons affect entire tree
- Visual feedback: button text changes (− ↔ +)
- Children container gets 'collapsed' class when hidden
```

### 3. Color Coding System

**Alive Members** (Green Tint):
- Background: `#f0fff4` (light green)
- Border: `#86efac` (green)
- Indicates living family members

**Deceased Members** (Gray):
- Background: `#e5e5e5` (light gray)
- Border: `#999` (medium gray)
- Text color: `#666` (dark gray)
- Shows † (dagger) symbol before name
- Tooltip shows "(Deceased)" on hover

**Legend**:
- Added visual legend at top of tree section
- Shows color boxes with explanations
- Explains toggle button functionality
- Helps users understand the visual system

---

## File Changes

### `/resources/views/family-tree-report.blade.php`

**Updated CSS**:
```css
/* Vertical tree structure */
.tree li::before - horizontal connector from parent
.tree li::after - vertical line down to children
.tree ul - nested children with left padding

/* Toggle buttons */
.toggle-btn - 16x16px clickable button
.children-container - wrapper for collapsible children
.children-container.collapsed - hidden state

/* Color coding */
.member-card.alive - green tint for living members
.member-card.deceased - gray for deceased members
```

**Updated JavaScript**:
```javascript
- Event listeners for toggle buttons
- collapseAll() function
- expandAll() function
- Proper DOM traversal to find children containers
```

**Updated HTML**:
- Added legend section with color explanations
- Updated action buttons to include Expand/Collapse All
- Simplified button text for space efficiency

### `/resources/views/partials/family-tree-node.blade.php`

**Key Changes**:
1. Added `$hasChildren` variable to check for descendants
2. Added toggle button (only shown if member has children)
3. Wrapped member card in `.node-wrapper` for proper alignment
4. Changed CSS class from just `deceased` to either `alive` or `deceased`
5. Added status in tooltip: "(Alive)" or "(Deceased)"
6. Changed children wrapper from `<ul>` to `<ul class="children-container">`

**Structure**:
```blade
<div class="node-wrapper">
    @if($hasChildren)
        <span class="toggle-btn">−</span>
    @endif
    <div class="member-card {{ $isDeceased ? 'deceased' : 'alive' }}">
        <!-- Member info -->
    </div>
</div>
@if($hasChildren)
    <ul class="children-container">
        <!-- Recursive children -->
    </ul>
@endif
```

---

## User Interface

### Visual Hierarchy
```
Root Member (Green/Gray)
├── [−] Child 1 (Green/Gray)
│   ├── [−] Grandchild 1
│   │   └── Great-grandchild
│   └── Grandchild 2
├── [−] Child 2
└── Child 3 (no children, no button)
```

### Controls
- **Individual Control**: Click − button next to any member
- **Global Control**: Use "Expand All" / "Collapse All" buttons
- **Print**: Expands all automatically for complete printout
- **Copy**: Copies text including all expanded branches

---

## Benefits

1. **Better Readability**: Vertical flow matches how people read genealogy
2. **Space Efficient**: Doesn't overflow horizontally on large families
3. **Interactive**: Users can focus on specific branches
4. **Visual Status**: Instant recognition of alive vs deceased members
5. **Print Friendly**: Can collapse unnecessary branches before printing
6. **Mobile Compatible**: Vertical layout works better on narrow screens

---

## Testing Checklist

✅ Tree displays vertically with proper connecting lines
✅ Toggle buttons appear only on members with children
✅ Clicking toggle button collapses/expands children
✅ "Expand All" button shows entire tree
✅ "Collapse All" button hides all children
✅ Green color shows for alive members
✅ Gray color shows for deceased members
✅ † symbol displays for deceased members
✅ Legend explains color system
✅ Print functionality works
✅ Responsive on mobile devices

---

## Technical Notes

### CSS Classes
- `.tree` - Main tree container
- `.tree ul` - List of children (nested)
- `.tree li` - Individual member node
- `.tree li::before` - Horizontal connecting line
- `.tree li::after` - Vertical connecting line
- `.node-wrapper` - Wrapper for toggle + card
- `.toggle-btn` - Expand/collapse button
- `.member-card` - Member information card
- `.member-card.alive` - Green tint
- `.member-card.deceased` - Gray tint
- `.children-container` - Collapsible children wrapper
- `.children-container.collapsed` - Hidden state

### JavaScript Functions
- `collapseAll()` - Hide all descendant branches
- `expandAll()` - Show all descendant branches
- Toggle button click handler - Toggle individual branch
- DOM traversal uses `closest()` and `:scope >` for precision

### Data Structure
No changes to backend - still uses recursive tree building
Color coding determined by `reg_number == 'Late'` check
All original statistics and data remain unchanged

---

## Future Enhancements

1. **Search/Filter**: Find specific members and highlight their path
2. **Generations View**: Toggle between full tree and generation levels
3. **Export Options**: Export as image or PDF
4. **Relationship Lines**: Show marriage connections horizontally
5. **Photos**: Optional member photos on expand
6. **Statistics per Branch**: Show descendant counts on toggle buttons
7. **Zoom Controls**: Zoom in/out for large trees
8. **Save State**: Remember expanded/collapsed state in session

---

## Compatibility

- **Browsers**: Chrome, Firefox, Safari, Edge (ES6+ required)
- **Mobile**: Responsive design, touch-friendly buttons
- **Print**: Automatically expands all for complete printout
- **Accessibility**: Keyboard navigation supported, semantic HTML

---

## Notes

- Default state: All branches expanded
- Toggle button text: − (expanded), + (collapsed)
- Colors are subtle to maintain professional document style
- Connecting lines are 1px gray (#999) for minimal visual weight
- All original functionality preserved (print, copy, statistics)
