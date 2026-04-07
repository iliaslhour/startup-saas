<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->current_organization_id) {
            return response()->json([
                'invoices' => [],
            ]);
        }

        $invoices = Invoice::where('organization_id', $user->current_organization_id)
            ->latest()
            ->get();

        return response()->json([
            'invoices' => $invoices,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['nullable', 'email'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'tax_amount' => ['nullable', 'numeric'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.unit_price' => ['required', 'numeric'],
        ]);

        $user = $request->user();

        if (! $user || ! $user->current_organization_id) {
            return response()->json([
                'message' => 'Aucune organisation active.',
            ], 400);
        }

        $subtotal = 0;

        foreach ($request->items as $item) {
            $subtotal += ((float) $item['quantity']) * ((float) $item['unit_price']);
        }

        $taxAmount = (float) ($request->tax_amount ?? 0);
        $totalAmount = $subtotal + $taxAmount;

        $invoice = Invoice::create([
            'organization_id' => $user->current_organization_id,
            'created_by' => $user->id,
            'invoice_number' => 'INV-'.date('Y').'-'.strtoupper(substr(uniqid(), -8)),
            'client_name' => $request->client_name,
            'client_email' => $request->client_email,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        $this->notificationService->create(
            $user->id,
            $user->current_organization_id,
            'invoice_created',
            'Facture créée',
            'La facture '.$invoice->invoice_number.' a été créée avec succès.'
        );

        return response()->json([
            'message' => 'Facture créée avec succès.',
            'invoice' => $invoice,
        ], 201);
    }
}