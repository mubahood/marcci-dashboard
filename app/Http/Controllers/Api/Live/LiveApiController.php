<?php

namespace App\Http\Controllers\Api\Live;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Sacco;
use App\Models\Transaction;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\ShareRecord;
use App\Models\ContributionProgram;
use App\Models\ContributionProgramRecord;
use App\Models\Cycle;
use Carbon\Carbon;

/**
 * ============================================================================
 * LIVE API ENDPOINTS - ADVANCED DATA FETCHING WITH FILTERING & PAGINATION
 * ============================================================================
 * 
 * These endpoints provide real-time data access with comprehensive filtering,
 * searching, sorting, and pagination capabilities.
 * 
 * Base URL: /api/live/*
 * 
 * Features:
 * - Advanced filtering by multiple fields
 * - Full-text search
 * - Date range filtering
 * - Amount range filtering  
 * - Sorting by any field
 * - Pagination with metadata
 * - Relationship eager loading
 * - Performance optimized queries
 * - Comprehensive error handling
 * 
 * Authentication: All endpoints require Bearer token
 * 
 * Created: October 4, 2025
 * ============================================================================
 */
class LiveApiController extends Controller
{
    /**
     * ========================================================================
     * HELPER METHODS
     * ========================================================================
     */

    /**
     * Get authenticated user
     */
    private function getAuthUser()
    {
        $user = auth('api')->user();
        if (!$user) {
            return null;
        }
        return $user;
    }

    /**
     * Success response helper
     */
    private function success($data, $message = 'Success', $meta = [])
    {
        return response()->json([
            'success' => true,
            'code' => 1,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'timestamp' => now()->toISOString()
        ], 200);
    }

    /**
     * Error response helper
     */
    private function error($message, $code = 0, $status = 400)
    {
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => null,
            'timestamp' => now()->toISOString()
        ], $status);
    }

    /**
     * Apply common filters to query
     */
    private function applyFilters($query, Request $request, $searchFields = [])
    {
        // Search across multiple fields
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                }
            });
        }

        // Date range filtering
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        // Status filtering
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * Get pagination parameters
     */
    private function getPaginationParams(Request $request)
    {
        $perPage = min((int)$request->input('per_page', 20), 100); // Max 100
        $page = (int)$request->input('page', 1);
        
        return [
            'per_page' => $perPage,
            'page' => $page
        ];
    }

    /**
     * Format pagination metadata
     */
    private function formatPaginationMeta($paginator)
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more' => $paginator->hasMorePages()
        ];
    }

    /**
     * ========================================================================
     * TRANSACTIONS ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/transactions
     * 
     * Fetch user's transaction history with advanced filtering
     * 
     * Query Parameters:
     * - search: Search by description (optional)
     * - type: Filter by transaction type (optional)
     *   Values: DEPOSIT, WITHDRAW, LOAN, LOAN_REPAYMENT, SHARE, CONTRIBUTION, etc.
     * - date_from: Start date (Y-m-d) (optional)
     * - date_to: End date (Y-m-d) (optional)
     * - amount_min: Minimum amount (optional)
     * - amount_max: Maximum amount (optional)
     * - cycle_id: Filter by cycle (optional)
     * - sort_by: Field to sort by (default: created_at)
     * - sort_order: asc or desc (default: desc)
     * - per_page: Items per page (default: 20, max: 100)
     * - page: Page number (default: 1)
     * 
     * Response includes:
     * - User details
     * - Cycle information
     * - Balance calculations
     * - Pagination metadata
     */
    public function transactions(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        $params = $this->getPaginationParams($request);
        
        // Build query with eager loading
        $query = Transaction::with(['user', 'cycle'])
            ->where('sacco_id', $user->sacco_id);

        // Apply search
        $query = $this->applyFilters($query, $request, [
            'description',
            'details',
            'type',
            'payment_method'
        ]);

        // Type filter
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Amount range
        if ($request->has('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->has('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // Cycle filter
        if ($request->has('cycle_id')) {
            $query->where('cycle_id', $request->cycle_id);
        }

        // User filter (for admin viewing all)
        if ($request->has('user_id') && $user->user_type == 'Admin') {
            $query->where('user_id', $request->user_id);
        } else {
            // Regular users see only their transactions
            $query->where('user_id', $user->id);
        }

        // Execute query with pagination
        $transactions = $query->paginate($params['per_page']);

        // Calculate summary statistics
        $summary = [
            'total_deposits' => Transaction::where('sacco_id', $user->sacco_id)
                ->where('user_id', $user->id)
                ->where('amount', '>', 0)
                ->sum('amount'),
            'total_withdrawals' => Transaction::where('sacco_id', $user->sacco_id)
                ->where('user_id', $user->id)
                ->where('amount', '<', 0)
                ->sum('amount'),
            'current_balance' => Transaction::where('sacco_id', $user->sacco_id)
                ->where('user_id', $user->id)
                ->sum('amount'),
            'transaction_count' => $transactions->total()
        ];

        return $this->success(
            $transactions->items(),
            'Transactions fetched successfully',
            [
                'pagination' => $this->formatPaginationMeta($transactions),
                'summary' => $summary,
                'filters_applied' => $request->except(['page', 'per_page'])
            ]
        );
    }

    /**
     * ========================================================================
     * LOANS ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/loans
     * 
     * Fetch loans with comprehensive filtering and balance calculations
     * 
     * Query Parameters:
     * - search: Search by loan details (optional)
     * - status: Filter by status (Pending, Approved, Rejected, Active, Completed)
     * - date_from: Start date (optional)
     * - date_to: End date (optional)
     * - amount_min: Minimum loan amount (optional)
     * - amount_max: Maximum loan amount (optional)
     * - scheme_id: Filter by loan scheme (optional)
     * - overdue: Filter overdue loans (true/false) (optional)
     * - sort_by: Field to sort by (default: created_at)
     * - sort_order: asc or desc (default: desc)
     * - per_page: Items per page (default: 20, max: 100)
     * - page: Page number (default: 1)
     * 
     * Response includes:
     * - Loan details
     * - Borrower information
     * - Loan scheme details
     * - Balance and payment status
     * - Interest calculations
     */
    public function loans(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        $params = $this->getPaginationParams($request);
        
        // Build query
        $query = Loan::with(['user', 'loan_scheem', 'cycle'])
            ->where('sacco_id', $user->sacco_id);

        // Apply search
        $query = $this->applyFilters($query, $request, [
            'description',
            'details',
            'loan_reference'
        ]);

        // Amount range
        if ($request->has('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->has('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // Scheme filter
        if ($request->has('scheme_id')) {
            $query->where('loan_scheem_id', $request->scheme_id);
        }

        // Overdue filter
        if ($request->has('overdue') && $request->overdue == 'true') {
            $query->where('due_date', '<', now())
                ->where('is_fully_paid', '!=', 'Yes');
        }

        // User filter
        if ($request->has('user_id') && $user->user_type == 'Admin') {
            $query->where('user_id', $request->user_id);
        } else {
            $query->where('user_id', $user->id);
        }

        // Execute query
        $loans = $query->paginate($params['per_page']);

        // Enhance data with balance calculations
        $loansData = $loans->items();
        foreach ($loansData as $loan) {
            $loan->balance_calculated = LoanTransaction::where('loan_id', $loan->id)
                ->sum('amount');
            $loan->is_overdue = $loan->due_date && 
                Carbon::parse($loan->due_date)->isPast() && 
                $loan->is_fully_paid !== 'Yes';
        }

        // Summary statistics
        $summary = [
            'total_loans' => Loan::where('sacco_id', $user->sacco_id)
                ->where('user_id', $user->id)
                ->count(),
            'active_loans' => Loan::where('sacco_id', $user->sacco_id)
                ->where('user_id', $user->id)
                ->where('is_fully_paid', '!=', 'Yes')
                ->count(),
            'total_borrowed' => Loan::where('sacco_id', $user->sacco_id)
                ->where('user_id', $user->id)
                ->sum('amount'),
            'total_balance' => DB::table('loan_transactions')
                ->join('loans', 'loans.id', '=', 'loan_transactions.loan_id')
                ->where('loans.user_id', $user->id)
                ->where('loans.sacco_id', $user->sacco_id)
                ->sum('loan_transactions.amount')
        ];

        return $this->success(
            $loansData,
            'Loans fetched successfully',
            [
                'pagination' => $this->formatPaginationMeta($loans),
                'summary' => $summary,
                'filters_applied' => $request->except(['page', 'per_page'])
            ]
        );
    }

    /**
     * ========================================================================
     * CONTRIBUTION PROGRAMS ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/contribution-programs
     * 
     * Fetch contribution programs with member participation data
     * 
     * Query Parameters:
     * - search: Search by name (optional)
     * - contribution_type: Periodic or Open (optional)
     * - periodic_type: Monthly, Weekly, Daily (optional)
     * - status: Active, Inactive, Completed (optional)
     * - date_from: Start date (optional)
     * - date_to: End date (optional)
     * - sort_by: Field to sort by (default: created_at)
     * - sort_order: asc or desc (default: desc)
     * - per_page: Items per page (default: 20, max: 100)
     * - page: Page number (default: 1)
     * 
     * Response includes:
     * - Program details
     * - Member participation stats
     * - Balance calculations
     * - Payment completion rates
     */
    public function contributionPrograms(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        $params = $this->getPaginationParams($request);
        
        // Build query
        $query = ContributionProgram::where('sacco_id', $user->sacco_id);

        // Apply search
        $query = $this->applyFilters($query, $request, ['name', 'details']);

        // Contribution type filter
        if ($request->has('contribution_type')) {
            $query->where('contribution_type', $request->contribution_type);
        }

        // Periodic type filter
        if ($request->has('periodic_type')) {
            $query->where('periodic_type', $request->periodic_type);
        }

        // Execute query
        $programs = $query->paginate($params['per_page']);

        // Enhance with statistics
        $programsData = $programs->items();
        foreach ($programsData as $program) {
            $program->total_records = ContributionProgramRecord::where('contribution_program_id', $program->id)->count();
            $program->paid_records = ContributionProgramRecord::where('contribution_program_id', $program->id)
                ->where('is_paid', 'Yes')
                ->count();
            $program->payment_rate = $program->total_records > 0 
                ? round(($program->paid_records / $program->total_records) * 100, 2) 
                : 0;
            $program->user_is_member = is_array($program->members) 
                ? in_array($user->id, $program->members)
                : (strpos($program->members, (string)$user->id) !== false);
        }

        return $this->success(
            $programsData,
            'Contribution programs fetched successfully',
            [
                'pagination' => $this->formatPaginationMeta($programs),
                'filters_applied' => $request->except(['page', 'per_page'])
            ]
        );
    }

    /**
     * ========================================================================
     * CONTRIBUTION RECORDS ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/contribution-records
     * 
     * Fetch contribution payment records with filtering
     * 
     * Query Parameters:
     * - program_id: Filter by contribution program (required for members)
     * - member_id: Filter by member (admin only) (optional)
     * - is_paid: Yes or No (optional)
     * - date_from: Payment date from (optional)
     * - date_to: Payment date to (optional)
     * - period_name: Filter by period (optional)
     * - sort_by: Field to sort by (default: created_at)
     * - sort_order: asc or desc (default: desc)
     * - per_page: Items per page (default: 20, max: 100)
     * - page: Page number (default: 1)
     */
    public function contributionRecords(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        $params = $this->getPaginationParams($request);
        
        // Build query with relationships
        $query = ContributionProgramRecord::with(['member', 'program', 'treasurer'])
            ->where('sacco_id', $user->sacco_id);

        // Program filter
        if ($request->has('program_id')) {
            $query->where('contribution_program_id', $request->program_id);
        }

        // Payment status filter
        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->is_paid);
        }

        // Period filter
        if ($request->has('period_name')) {
            $query->where('period_name', 'LIKE', "%{$request->period_name}%");
        }

        // Payment date range
        if ($request->has('payment_date_from')) {
            $query->where('payment_date', '>=', $request->payment_date_from);
        }
        if ($request->has('payment_date_to')) {
            $query->where('payment_date', '<=', $request->payment_date_to);
        }

        // Member filter
        if ($request->has('member_id') && $user->user_type == 'Admin') {
            $query->where('member_id', $request->member_id);
        } else {
            // Regular users see only their records
            $query->where('member_id', $user->id);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Execute query
        $records = $query->paginate($params['per_page']);

        // Summary statistics
        $summary = [
            'total_expected' => ContributionProgramRecord::where('sacco_id', $user->sacco_id)
                ->where('member_id', $user->id)
                ->sum('amount'),
            'total_paid' => ContributionProgramRecord::where('sacco_id', $user->sacco_id)
                ->where('member_id', $user->id)
                ->where('is_paid', 'Yes')
                ->sum('paid_amount'),
            'total_balance' => ContributionProgramRecord::where('sacco_id', $user->sacco_id)
                ->where('member_id', $user->id)
                ->sum('amount') - 
                ContributionProgramRecord::where('sacco_id', $user->sacco_id)
                ->where('member_id', $user->id)
                ->sum('paid_amount'),
            'paid_count' => ContributionProgramRecord::where('sacco_id', $user->sacco_id)
                ->where('member_id', $user->id)
                ->where('is_paid', 'Yes')
                ->count(),
            'unpaid_count' => ContributionProgramRecord::where('sacco_id', $user->sacco_id)
                ->where('member_id', $user->id)
                ->where('is_paid', 'No')
                ->count()
        ];

        return $this->success(
            $records->items(),
            'Contribution records fetched successfully',
            [
                'pagination' => $this->formatPaginationMeta($records),
                'summary' => $summary,
                'filters_applied' => $request->except(['page', 'per_page'])
            ]
        );
    }

    /**
     * ========================================================================
     * SHARE RECORDS ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/share-records
     * 
     * Fetch share purchase/sale records
     * 
     * Query Parameters:
     * - search: Search by details (optional)
     * - type: Filter by type (optional)
     * - date_from: Start date (optional)
     * - date_to: End date (optional)
     * - amount_min: Minimum amount (optional)
     * - amount_max: Maximum amount (optional)
     * - sort_by: Field to sort by (default: created_at)
     * - sort_order: asc or desc (default: desc)
     * - per_page: Items per page (default: 20, max: 100)
     * - page: Page number (default: 1)
     */
    public function shareRecords(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        $params = $this->getPaginationParams($request);
        
        // Build query
        $query = ShareRecord::with(['user'])
            ->where('sacco_id', $user->sacco_id);

        // Apply search
        $query = $this->applyFilters($query, $request, ['description', 'details']);

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Amount range
        if ($request->has('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->has('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // User filter
        if ($request->has('user_id') && $user->user_type == 'Admin') {
            $query->where('user_id', $request->user_id);
        } else {
            $query->where('user_id', $user->id);
        }

        // Execute query
        $shares = $query->paginate($params['per_page']);

        // Summary
        $summary = [
            'total_shares' => ShareRecord::where('sacco_id', $user->sacco_id)
                ->where('user_id', $user->id)
                ->sum('number_of_shares'),
            'total_amount' => ShareRecord::where('sacco_id', $user->sacco_id)
                ->where('user_id', $user->id)
                ->sum('amount'),
            'share_price' => Sacco::find($user->sacco_id)->share_price ?? 0
        ];

        return $this->success(
            $shares->items(),
            'Share records fetched successfully',
            [
                'pagination' => $this->formatPaginationMeta($shares),
                'summary' => $summary,
                'filters_applied' => $request->except(['page', 'per_page'])
            ]
        );
    }

    /**
     * ========================================================================
     * SACCO MEMBERS ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/members
     * 
     * Fetch SACCO members with filtering (Admin only)
     * 
     * Query Parameters:
     * - search: Search by name, email, phone (optional)
     * - user_type: Admin, Member, Treasurer (optional)
     * - status: Active status (optional)
     * - sacco_join_status: Approved, Pending, Rejected (optional)
     * - sort_by: Field to sort by (default: created_at)
     * - sort_order: asc or desc (default: desc)
     * - per_page: Items per page (default: 20, max: 100)
     * - page: Page number (default: 1)
     */
    public function members(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        $params = $this->getPaginationParams($request);
        
        // Build query
        $query = User::where('sacco_id', $user->sacco_id);

        // Apply search
        $query = $this->applyFilters($query, $request, [
            'name',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'username'
        ]);

        // User type filter
        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Join status filter
        if ($request->has('sacco_join_status')) {
            $query->where('sacco_join_status', $request->sacco_join_status);
        }

        // Active status
        if ($request->has('active_status')) {
            $query->where('status', $request->active_status);
        }

        // Execute query
        $members = $query->paginate($params['per_page']);

        // Summary
        $summary = [
            'total_members' => User::where('sacco_id', $user->sacco_id)->count(),
            'active_members' => User::where('sacco_id', $user->sacco_id)
                ->where('status', 1)
                ->count(),
            'admins' => User::where('sacco_id', $user->sacco_id)
                ->where('user_type', 'Admin')
                ->count(),
            'pending_approvals' => User::where('sacco_id', $user->sacco_id)
                ->where('sacco_join_status', 'Pending')
                ->count()
        ];

        return $this->success(
            $members->items(),
            'Members fetched successfully',
            [
                'pagination' => $this->formatPaginationMeta($members),
                'summary' => $summary,
                'filters_applied' => $request->except(['page', 'per_page'])
            ]
        );
    }

    /**
     * ========================================================================
     * CYCLES ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/cycles
     * 
     * Fetch SACCO cycles with statistics
     * 
     * Query Parameters:
     * - status: Active, Inactive, Completed (optional)
     * - date_from: Start date (optional)
     * - date_to: End date (optional)
     * - sort_by: Field to sort by (default: created_at)
     * - sort_order: asc or desc (default: desc)
     * - per_page: Items per page (default: 20, max: 100)
     * - page: Page number (default: 1)
     */
    public function cycles(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        $params = $this->getPaginationParams($request);
        
        // Build query
        $query = Cycle::where('sacco_id', $user->sacco_id);

        // Apply filters
        $query = $this->applyFilters($query, $request, ['name', 'description']);

        // Execute query
        $cycles = $query->paginate($params['per_page']);

        // Enhance with statistics
        $cyclesData = $cycles->items();
        foreach ($cyclesData as $cycle) {
            $cycle->total_transactions = Transaction::where('cycle_id', $cycle->id)->count();
            $cycle->total_loans = Loan::where('cycle_id', $cycle->id)->count();
            $cycle->total_contributions = ContributionProgramRecord::where('sacco_id', $user->sacco_id)
                ->whereHas('program', function($q) use ($cycle) {
                    $q->whereBetween('start_date', [$cycle->start_date, $cycle->end_date]);
                })
                ->count();
        }

        return $this->success(
            $cyclesData,
            'Cycles fetched successfully',
            [
                'pagination' => $this->formatPaginationMeta($cycles),
                'filters_applied' => $request->except(['page', 'per_page'])
            ]
        );
    }

    /**
     * ========================================================================
     * DASHBOARD SUMMARY ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/dashboard
     * 
     * Get comprehensive dashboard data for the user
     * 
     * Returns:
     * - Account balance
     * - Active loans summary
     * - Contribution summary
     * - Share holdings
     * - Recent transactions
     * - Upcoming payments
     */
    public function dashboard(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        // Account balance
        $accountBalance = Transaction::where('user_id', $user->id)
            ->where('sacco_id', $user->sacco_id)
            ->sum('amount');

        // Active loans
        $activeLoans = Loan::where('user_id', $user->id)
            ->where('sacco_id', $user->sacco_id)
            ->where('status', 'Active')
            ->with('loan_scheem')
            ->get();

        $loansSummary = [
            'count' => $activeLoans->count(),
            'total_borrowed' => $activeLoans->sum('amount'),
            'total_balance' => 0,
            'loans' => $activeLoans
        ];

        // Calculate loan balances
        foreach ($activeLoans as $loan) {
            $balance = LoanTransaction::where('loan_id', $loan->id)->sum('amount');
            $loansSummary['total_balance'] += $balance;
            $loan->current_balance = $balance;
        }

        // Contributions
        $contributionSummary = [
            'total_expected' => ContributionProgramRecord::where('member_id', $user->id)
                ->where('sacco_id', $user->sacco_id)
                ->sum('amount'),
            'total_paid' => ContributionProgramRecord::where('member_id', $user->id)
                ->where('sacco_id', $user->sacco_id)
                ->where('is_paid', 'Yes')
                ->sum('paid_amount'),
            'unpaid_count' => ContributionProgramRecord::where('member_id', $user->id)
                ->where('sacco_id', $user->sacco_id)
                ->where('is_paid', 'No')
                ->count(),
            'active_programs' => ContributionProgram::where('sacco_id', $user->sacco_id)
                ->where('status', 'Active')
                ->count()
        ];

        // Shares
        $sharesSummary = [
            'total_shares' => ShareRecord::where('user_id', $user->id)
                ->where('sacco_id', $user->sacco_id)
                ->sum('number_of_shares'),
            'total_value' => ShareRecord::where('user_id', $user->id)
                ->where('sacco_id', $user->sacco_id)
                ->sum('amount')
        ];

        // Recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->where('sacco_id', $user->sacco_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Upcoming payments (unpaid contributions)
        $upcomingPayments = ContributionProgramRecord::with('program')
            ->where('member_id', $user->id)
            ->where('sacco_id', $user->sacco_id)
            ->where('is_paid', 'No')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $dashboard = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'user_type' => $user->user_type
            ],
            'account_balance' => $accountBalance,
            'loans' => $loansSummary,
            'contributions' => $contributionSummary,
            'shares' => $sharesSummary,
            'recent_transactions' => $recentTransactions,
            'upcoming_payments' => $upcomingPayments,
            'timestamp' => now()->toISOString()
        ];

        return $this->success($dashboard, 'Dashboard data fetched successfully');
    }

    /**
     * ========================================================================
     * STATISTICS ENDPOINT
     * ========================================================================
     * 
     * GET /api/live/statistics
     * 
     * Get advanced statistics and analytics
     * 
     * Query Parameters:
     * - period: daily, weekly, monthly, yearly (default: monthly)
     * - date_from: Start date (optional)
     * - date_to: End date (optional)
     */
    public function statistics(Request $request)
    {
        $user = $this->getAuthUser();
        if (!$user) {
            return $this->error('Unauthorized. Please login.', 401, 401);
        }

        $period = $request->input('period', 'monthly');
        $dateFrom = $request->input('date_from', now()->subMonths(6)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Transaction trends
        $transactionTrends = Transaction::where('sacco_id', $user->sacco_id)
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as period,
                SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as deposits,
                SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END) as withdrawals,
                COUNT(*) as transaction_count
            ')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Contribution trends
        $contributionTrends = ContributionProgramRecord::where('sacco_id', $user->sacco_id)
            ->where('member_id', $user->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as period,
                SUM(amount) as expected,
                SUM(CASE WHEN is_paid = "Yes" THEN paid_amount ELSE 0 END) as paid,
                COUNT(*) as record_count
            ')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $statistics = [
            'period' => $period,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ],
            'transaction_trends' => $transactionTrends,
            'contribution_trends' => $contributionTrends,
            'summary' => [
                'total_deposits' => Transaction::where('sacco_id', $user->sacco_id)
                    ->where('user_id', $user->id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('amount', '>', 0)
                    ->sum('amount'),
                'total_withdrawals' => abs(Transaction::where('sacco_id', $user->sacco_id)
                    ->where('user_id', $user->id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('amount', '<', 0)
                    ->sum('amount')),
                'contributions_paid' => ContributionProgramRecord::where('sacco_id', $user->sacco_id)
                    ->where('member_id', $user->id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->where('is_paid', 'Yes')
                    ->sum('paid_amount')
            ]
        ];

        return $this->success($statistics, 'Statistics fetched successfully');
    }
}
