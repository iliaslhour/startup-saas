<?php

namespace App\Http\Controllers\Api\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceStatusRequest;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->current_organization_id) {
            return response()->json(['message' => 'Aucune organisation active sélectionnée.'], 422);
        }

        $invoices = Invoice::query()
            ->where('organization_id', $user->current_organization_id)
            ->with(['creator', 'project', 'items'])
            ->latest()
            ->get();

        return response()->json([
            'invoices' => InvoiceResource::collection($invoices),
        ]);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->current_organization_id) {
            return response()->json(['message' => 'Aucune organisation active sélectionnée.'], 422);
        }

        $role = $user->getRoleInOrganization($user->current_organization_id);

        if (! $role || $role->slug !== 'admin') {
            return response()->json(['message' => 'Seul un administrateur peut créer une facture.'], 403);
        }

        $validated = $request->validated();

        if (! empty($validated['project_id'])) {
            $project = Project::find($validated['project_id']);

            if (! $project || $project->organization_id !== $user->current_organization_id) {
                return response()->json([
                    'message' => 'Le projet sélectionné n’appartient pas à l’organisation active.',
                ], 422);
            }
        }

        $invoice = DB::transaction(function () use ($validated, $user) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $subtotal += ((float) $item['quantity']) * ((float) $item['unit_price']);
            }

            $taxAmount = (float) ($validated['tax_amount'] ?? 0);
            $totalAmount = $subtotal + $taxAmount;

            $invoice = Invoice::create([
                'organization_id' => $user->current_organization_id,
                'project_id' => $validated['project_id'] ?? null,
                'created_by' => $user->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => $validated['status'] ?? 'pending',
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = ((float) $item['quantity']) * ((float) $item['unit_price']);

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                ]);
            }

            return $invoice->load(['creator', 'project', 'items']);
        });

        return response()->json([
            'message' => 'Facture créée avec succès.',
            'invoice' => new InvoiceResource($invoice),
        ], 201);
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($invoice->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette facture.'], 403);
        }

        $invoice->load(['creator', 'project', 'items']);

        return response()->json([
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($invoice->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette facture.'], 403);
        }

        $role = $user->getRoleInOrganization($invoice->organization_id);

        if (! $role || $role->slug !== 'admin') {
            return response()->json(['message' => 'Seul un administrateur peut modifier une facture.'], 403);
        }

        $validated = $request->validated();

        if (! empty($validated['project_id'])) {
            $project = Project::find($validated['project_id']);

            if (! $project || $project->organization_id !== $invoice->organization_id) {
                return response()->json([
                    'message' => 'Le projet sélectionné n’appartient pas à l’organisation de la facture.',
                ], 422);
            }
        }

        $invoice = DB::transaction(function () use ($validated, $invoice) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $subtotal += ((float) $item['quantity']) * ((float) $item['unit_price']);
            }

            $taxAmount = (float) ($validated['tax_amount'] ?? 0);
            $totalAmount = $subtotal + $taxAmount;

            $invoice->update([
                'project_id' => $validated['project_id'] ?? null,
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $invoice->items()->delete();

            foreach ($validated['items'] as $item) {
                $lineTotal = ((float) $item['quantity']) * ((float) $item['unit_price']);

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                ]);
            }

            return $invoice->load(['creator', 'project', 'items']);
        });

        return response()->json([
            'message' => 'Facture mise à jour avec succès.',
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($invoice->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette facture.'], 403);
        }

        $role = $user->getRoleInOrganization($invoice->organization_id);

        if (! $role || $role->slug !== 'admin') {
            return response()->json(['message' => 'Seul un administrateur peut supprimer une facture.'], 403);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Facture supprimée avec succès.',
        ]);
    }

    public function updateStatus(UpdateInvoiceStatusRequest $request, Invoice $invoice): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($invoice->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette facture.'], 403);
        }

        $role = $user->getRoleInOrganization($invoice->organization_id);

        if (! $role || $role->slug !== 'admin') {
            return response()->json(['message' => 'Seul un administrateur peut changer le statut d’une facture.'], 403);
        }

        $invoice->update([
            'status' => $request->string('status')->toString(),
        ]);

        $invoice->load(['creator', 'project', 'items']);

        return response()->json([
            'message' => 'Statut de la facture mis à jour avec succès.',
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    public function archive(Request $request, Invoice $invoice): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        if (! $user->belongsToOrganization($invoice->organization_id)) {
            return response()->json(['message' => 'Accès non autorisé à cette facture.'], 403);
        }

        $role = $user->getRoleInOrganization($invoice->organization_id);

        if (! $role || $role->slug !== 'admin') {
            return response()->json(['message' => 'Seul un administrateur peut archiver une facture.'], 403);
        }

        $invoice->update([
            'is_archived' => true,
        ]);

        $invoice->load(['creator', 'project', 'items']);

        return response()->json([
            'message' => 'Facture archivée avec succès.',
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd');
        $countToday = Invoice::whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix.'-'.str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);
    }
}