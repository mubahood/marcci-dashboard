<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sacco;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;

class FamilyTreeReportController extends Controller
{
    /**
     * Generate comprehensive family tree visualization
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        $user = Admin::user();
        
        // Get SACCO based on user or request parameter
        if ($user->sacco_id) {
            $sacco_id = $user->sacco_id;
        } else {
            $sacco_id = $request->get('sacco_id', null);
        }
        
        if (!$sacco_id) {
            $sacco = null;
        } else {
            $sacco = Sacco::find($sacco_id);
        }
        
        // =================================================================
        // FETCH ALL MEMBERS
        // =================================================================
        
        $members_query = User::query();
        if ($sacco_id) {
            $members_query->where('sacco_id', $sacco_id);
        }
        
        $all_members = $members_query->orderBy('id', 'ASC')->get();
        
        // =================================================================
        // BUILD FAMILY TREE STRUCTURE
        // =================================================================
        
        // Find root members (those with website='Root' or no parent)
        $root_members = $all_members->filter(function($member) {
            return $member->website == 'Root' || 
                   $member->website == 'None' || 
                   empty($member->linkedin);
        });
        
        // Build parent-children relationships
        $tree_data = [];
        
        foreach ($root_members as $root) {
            $tree_data[] = $this->buildFamilyNode($root, $all_members);
        }
        
        // =================================================================
        // FAMILY STATISTICS
        // =================================================================
        
        $total_members = $all_members->count();
        $male_count = $all_members->where('sex', 'Male')->count();
        $female_count = $all_members->where('sex', 'Female')->count();
        $alive_count = $all_members->where('reg_number', 'Alive')->count();
        $deceased_count = $all_members->where('reg_number', 'Late')->count();
        
        // Generation statistics
        $generations = $this->calculateGenerations($tree_data);
        $max_generation = $generations['max'];
        $generation_distribution = $generations['distribution'];
        
        // Root members (founders)
        $founders_count = $root_members->count();
        
        // Calculate average children per family
        $families_with_children = 0;
        $total_children = 0;
        
        foreach ($all_members as $member) {
            $children_count = $all_members->where('linkedin', $member->id)->count();
            if ($children_count > 0) {
                $families_with_children++;
                $total_children += $children_count;
            }
        }
        
        $avg_children = $families_with_children > 0 ? 
            round($total_children / $families_with_children, 1) : 0;
        
        // Age distribution
        $age_groups = [
            'Children (0-17)' => 0,
            'Young Adults (18-35)' => 0,
            'Adults (36-60)' => 0,
            'Seniors (61+)' => 0,
        ];
        
        foreach ($all_members as $member) {
            if ($member->dob && $member->dob != '0000-00-00' && $member->dob != 'null') {
                try {
                    $age = \Carbon\Carbon::parse($member->dob)->age;
                    if ($age < 18) $age_groups['Children (0-17)']++;
                    elseif ($age < 36) $age_groups['Young Adults (18-35)']++;
                    elseif ($age < 61) $age_groups['Adults (36-60)']++;
                    else $age_groups['Seniors (61+)']++;
                } catch (\Exception $e) {
                    // Skip invalid dates
                }
            }
        }
        
        // =================================================================
        // FAMILY INSIGHTS
        // =================================================================
        
        // Largest family (most descendants)
        $largest_family = null;
        $max_descendants = 0;
        
        foreach ($root_members as $root) {
            $descendants = $this->countDescendants($root, $all_members);
            if ($descendants > $max_descendants) {
                $max_descendants = $descendants;
                $largest_family = $root;
            }
        }
        
        // Active contributors
        $active_contributors = $all_members->where('language', 'Compulsory')->count();
        $optional_contributors = $all_members->where('language', 'Optional')->count();
        
        // Admins
        $admin_count = $all_members->where('is_admin', 'Yes')->count();
        
        // =================================================================
        // RETURN VIEW WITH DATA
        // =================================================================
        
        return view('family-tree-report', compact(
            'sacco',
            'tree_data',
            'all_members',
            'total_members',
            'male_count',
            'female_count',
            'alive_count',
            'deceased_count',
            'max_generation',
            'generation_distribution',
            'founders_count',
            'avg_children',
            'age_groups',
            'largest_family',
            'max_descendants',
            'active_contributors',
            'optional_contributors',
            'admin_count'
        ));
    }
    
    /**
     * Build a family node with all its children recursively
     */
    private function buildFamilyNode($member, $all_members)
    {
        $children = $all_members->filter(function($m) use ($member) {
            return $m->linkedin == $member->id && $m->website != 'Root';
        })->values();
        
        $node = [
            'member' => $member,
            'children' => []
        ];
        
        foreach ($children as $child) {
            $node['children'][] = $this->buildFamilyNode($child, $all_members);
        }
        
        return $node;
    }
    
    /**
     * Calculate generation levels
     */
    private function calculateGenerations($tree_data, $current_level = 1)
    {
        $max_level = $current_level;
        $distribution = [$current_level => count($tree_data)];
        
        foreach ($tree_data as $node) {
            if (!empty($node['children'])) {
                $child_stats = $this->calculateGenerations($node['children'], $current_level + 1);
                $max_level = max($max_level, $child_stats['max']);
                
                foreach ($child_stats['distribution'] as $level => $count) {
                    if (!isset($distribution[$level])) {
                        $distribution[$level] = 0;
                    }
                    $distribution[$level] += $count;
                }
            }
        }
        
        return [
            'max' => $max_level,
            'distribution' => $distribution
        ];
    }
    
    /**
     * Count all descendants of a member
     */
    private function countDescendants($member, $all_members)
    {
        $children = $all_members->where('linkedin', $member->id)->where('website', '!=', 'Root');
        $count = $children->count();
        
        foreach ($children as $child) {
            $count += $this->countDescendants($child, $all_members);
        }
        
        return $count;
    }
}
