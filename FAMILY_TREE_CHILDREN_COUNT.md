# Family Tree - Children Count & Default Collapsed State

## Date: October 7, 2025

## Changes Made

### 1. Children Count Display
- **Added bracket notation** showing number of children: `(n)`
- Appears next to toggle button for any member with children
- Example: `+ (3)` means this person has 3 children
- Also added to tooltip: hover shows "X children"

### 2. Default Collapsed State
- **All branches now collapsed by default** on initial load
- Only root members visible when page first loads
- Users can expand branches they want to explore
- Reduces visual clutter for large families

### 3. Control Button Position
- **Moved Expand/Collapse All buttons to top** of tree section
- Now in legend bar for better visibility
- Easier to access before exploring tree
- Removed duplicate buttons from bottom actions

### 4. Visual Improvements
- Toggle button: `+` (collapsed) or `−` (expanded)
- Children count in gray: `(n)` 
- Updated legend to explain `(n) = number of children`

---

## Visual Example

```
Root Member + (2) [Green/Gray card]
```
*Click `+` button to expand and see 2 children*

```
Root Member − (2) [Green/Gray card]
├── Child 1 + (3) [Green card]
└── Child 2 [Green card]
```
*Click `−` button to collapse back*

---

## File Changes

### `/resources/views/partials/family-tree-node.blade.php`

**Added:**
- `$childrenCount = count($children);` variable
- Children count display: `<span class="children-count">({{ $childrenCount }})</span>`
- Default collapsed class: `<ul class="children-container collapsed">`
- Initial toggle state: `<span class="toggle-btn">+</span>` (was `−`)
- Enhanced tooltip with children count

**Structure:**
```blade
@if($hasChildren)
    <span class="toggle-btn">+</span>
    <span class="children-count">({{ $childrenCount }})</span>
@endif
```

### `/resources/views/family-tree-report.blade.php`

**CSS Added:**
```css
.children-count {
    font-size: 9px;
    color: #666;
    margin-right: 5px;
    font-weight: bold;
}
```

**Legend Updated:**
- Added flex layout for space-between alignment
- Left side: Legend with color explanations
- Right side: Expand All / Collapse All buttons
- Added explanation: `(n) = number of children`

**JavaScript Updated:**
- Improved DOM traversal for toggle functionality
- Now correctly finds children-container from toggle button
- Maintains proper collapsed/expanded state

**Removed:**
- Duplicate Expand/Collapse buttons from bottom actions bar

---

## User Experience Flow

### Initial State
1. User opens family tree
2. Only root members visible (all collapsed)
3. Each member with children shows: `+ (n)`
4. Legend explains colors and notation

### Exploration
1. Click `+` button to expand a branch
2. Button changes to `−`, children appear
3. Each child shows their own `+ (n)` if they have children
4. Continue expanding branches of interest

### Global Controls
1. **Expand All**: Click button in legend bar
   - All branches open
   - All `+` buttons become `−`
   - Full tree visible
   
2. **Collapse All**: Click button in legend bar
   - All branches close
   - All `−` buttons become `+`
   - Back to root members only

### Print/Copy
1. Manually expand branches you want to print
2. Or click "Expand All" first
3. Then use Print or Copy buttons

---

## Benefits

✅ **Less Overwhelming**: Large families don't flood the screen
✅ **Focused Exploration**: Users explore branches they care about
✅ **Quick Navigation**: Children count helps identify large branches
✅ **Better Performance**: Fewer DOM elements rendered initially
✅ **Cleaner Interface**: Only relevant information visible
✅ **Intuitive Controls**: Industry-standard +/− notation

---

## Technical Details

### Default State Logic
- All `<ul class="children-container">` start with `collapsed` class
- Toggle buttons start with `+` text
- CSS rule: `.children-container.collapsed { display: none; }`

### Children Count Calculation
```php
$childrenCount = count($children);
```
- Counts direct children only (not grandchildren)
- Displayed in gray next to toggle button
- Also shown in hover tooltip

### Button Behavior
- **+**: Children hidden (collapsed)
- **−**: Children visible (expanded)
- Click toggles state and updates button text
- Expand/Collapse All affects all branches simultaneously

### CSS Styling
```css
.children-count {
    font-size: 9px;      /* Smaller than names */
    color: #666;         /* Gray, not prominent */
    font-weight: bold;   /* But readable */
    margin-right: 5px;   /* Space from card */
}
```

---

## Testing Checklist

✅ Tree loads with all branches collapsed
✅ Only root members visible on initial load
✅ Toggle buttons show `+` initially
✅ Children count `(n)` displays correctly
✅ Clicking `+` expands branch and changes to `−`
✅ Clicking `−` collapses branch and changes to `+`
✅ Expand All button opens entire tree
✅ Collapse All button closes entire tree
✅ Children count matches actual number of children
✅ Tooltip shows "X children" on hover
✅ Legend explains `(n)` notation
✅ Print functionality works
✅ Colors still show (green/gray)

---

## Example Output

### Collapsed (Initial State)
```
Mubahood, John + (4)
Nakato, Mary + (2)
```

### Partially Expanded
```
Mubahood, John − (4)
├── Mubahood, Peter + (1)
├── Mubahood, Sarah
├── Mubahood, James + (3)
└── Mubahood, Grace

Nakato, Mary + (2)
```

### Fully Expanded
```
Mubahood, John − (4)
├── Mubahood, Peter − (1)
│   └── Mubahood, Alex
├── Mubahood, Sarah
├── Mubahood, James − (3)
│   ├── Mubahood, Tom
│   ├── Mubahood, Lisa
│   └── Mubahood, Emma
└── Mubahood, Grace

Nakato, Mary − (2)
├── Nakato, David + (2)
└── Nakato, Ruth
```

---

## Notes

- Default collapsed state improves initial load experience
- Children count provides quick insight into family size
- Users can strategically expand branches of interest
- Expand/Collapse All gives full control when needed
- Maintains all color coding (green = alive, gray = deceased)
- Print works with whatever is currently expanded/collapsed
