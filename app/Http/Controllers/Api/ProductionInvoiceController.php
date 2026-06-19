<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionInvoice;
use App\Models\ProductionInvoiceItem;
use App\Models\ProductionInvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductionInvoiceResource;

class ProductionInvoiceController extends Controller
{
    public function index()
    {
        return ProductionInvoiceResource::collection(
            ProductionInvoice::with('shoot')
                ->latest()
                ->paginate(20)
        );
    }

    public function byShoot($shootId)
    {
        return ProductionInvoice::with([
            'items',
            'payments'
        ])
            ->where('shoot_id', $shootId)
            ->latest()
            ->get();
    }

    public function show(ProductionInvoice $invoice)
    {
        return new ProductionInvoiceResource(
            $invoice->load([
                'shoot',
                'items',
                'payments'
            ])
        );
    }

    public function items(ProductionInvoice $invoice)
    {
        return $invoice->items;
    }

    public function payments(ProductionInvoice $invoice)
    {
        return $invoice->payments;
    }

    public function store(Request $request)
    {
        $request->validate([
            'shoot_id' => 'required|exists:shoots,id',
            'assigned_to' =>'nullable|exists:users,id',
            'title' => 'nullable|string|max:255',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'tax_percentage' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $subtotal = 0;

            foreach ($request->items as $item) {

                $subtotal +=
                    ($item['quantity'] *
                        $item['unit_price']);
            }

            $taxPercentage =
                $request->tax_percentage ?? 0;

            $discount =
                $request->discount_amount ?? 0;

            $taxAmount =
                ($subtotal * $taxPercentage) / 100;

            $total =
                $subtotal +
                $taxAmount -
                $discount;

            $invoice = ProductionInvoice::create([
                'shoot_id' => $request->shoot_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'title' => $request->title,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'balance_due' => $total,
                'notes' => $request->notes,
                'created_by' => Auth::user()->id,
            ]);

            foreach ($request->items as $item) {

                ProductionInvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' =>
                    $item['quantity']
                        *
                        $item['unit_price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => $invoice->load('items')
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(
        Request $request,
        ProductionInvoice $invoice
    ) {
        $invoice->update(
            $request->only([
                'title',
                'issue_date',
                'due_date',
                'notes',
                'status'
            ])
        );

        return response()->json([
            'message' => 'Invoice updated successfully'
        ]);
    }

    public function destroy(
        ProductionInvoice $invoice
    ) {
        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully'
        ]);
    }

    public function addPayment(
        Request $request,
        ProductionInvoice $invoice
    ) {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        ProductionInvoicePayment::create([
            'invoice_id' => $invoice->id,
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
            'created_by' => Auth::user()->id,
        ]);

        $paid =
            $invoice->payments()
            ->sum('amount');

        $balance =
            $invoice->total_amount - $paid;

        $status = 'sent';

        if (
            $paid > 0 &&
            $paid < $invoice->total_amount
        ) {

            $status = 'partially_paid';
        }

        if ($paid >= $invoice->total_amount) {

            $status = 'paid';
        }

        $invoice->update([
            'paid_amount' => $paid,
            'balance_due' => $balance,
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Payment added successfully',
        ]);
    }

    private function generateInvoiceNumber()
    {
        $year = now()->year;

        $lastInvoice =
            ProductionInvoice::whereYear(
                'created_at',
                $year
            )
            ->latest('id')
            ->first();

        $next = 1;

        if ($lastInvoice) {

            $next =
                ((int) substr(
                    $lastInvoice->invoice_number,
                    -4
                )) + 1;
        }

        return 'INV-' .
            $year .
            '-' .
            str_pad(
                $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }

    public function invoiceSummary($shootId)
    {
        $invoices = ProductionInvoice::where(
            'shoot_id',
            $shootId
        )->get();

        return [

            'invoice_count' =>
            $invoices->count(),

            'invoiced' =>
            $invoices->sum('total_amount'),

            'collected' =>
            $invoices->sum('paid_amount'),

            'outstanding' =>
            $invoices->sum('balance_due'),
        ];
    }
}
