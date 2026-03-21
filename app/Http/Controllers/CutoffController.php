<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CutoffController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizePermission('cutoff.view');

        $selectedMonth = (string) $request->input('month', now()->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // $expenses = Expenses::query()
        //     ->where(function ($query) use ($monthEnd) {
        //         $query->whereNull('date_end')
        //             ->orWhere('date_end', '>=', $monthEnd);
        //     })
        //     ->orderBy('pay_in')
        //     ->orderBy('name')
        //     ->get();

        $expenses = Expenses::query()
            ->where('created_by', Auth::id())
            ->where('date_start', '<=', $monthEnd)
            ->where(function ($query) use ($monthStart) {
                $query->whereNull('date_end')
                    ->orWhere('date_end', '>=', $monthStart);
            })
            ->orderBy('pay_in')
            ->orderBy('name')
            ->get();

        $cutoffs = collect(['first', 'second'])
            ->mapWithKeys(function (string $payIn) use ($expenses): array {
                $items = $expenses
                    ->where('pay_in', $payIn)
                    ->values();

                return [
                    $payIn => [
                        'key' => $payIn,
                        'label' => $payIn === 'first' ? 'First Cutoff' : 'Second Cutoff',
                        'count' => $items->count(),
                        'total_amount' => (float) $items->sum('total_amount'),
                        'total_paid' => (float) $items->sum('paid_amount'),
                        'items' => $items->map(function (Expenses $expense): array {
                            return [
                                'id' => $expense->id,
                                'name' => $expense->name,
                                'type' => $expense->type,
                                'category' => $expense->category,
                                'reference_no' => $expense->reference_no,
                                'date_start' => $expense->date_start,
                                'date_end' => $expense->date_end,
                                'payment_due' => $expense->payment_due,
                                'total_amount' => (float) $expense->total_amount,
                                'paid_amount' => (float) $expense->paid_amount,
                                'pay_in' => $expense->pay_in,
                            ];
                        })->all(),
                    ],
                ];
            });

        return Inertia::render('cutoff/Index', [
            'filters' => [
                'month' => $selectedMonth,
            ],
            'summary' => [
                'month' => $monthStart->format('F Y'),
                'count' => $expenses->count(),
                'total_amount' => (float) $expenses->sum('total_amount'),
                'total_paid' => (float) $expenses->sum('paid_amount'),
            ],
            'cutoffs' => $cutoffs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {}
}
