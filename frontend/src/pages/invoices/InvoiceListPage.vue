<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-slate-900">Factures</h2>
          <p class="mt-2 text-slate-500">Liste des factures de ton organisation.</p>
        </div>

        <RouterLink
          to="/invoices/create"
          class="rounded-2xl bg-slate-900 px-5 py-3 text-white font-semibold hover:bg-slate-700 transition"
        >
          Nouvelle facture
        </RouterLink>
      </div>

      <div v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-600">
        {{ errorMessage }}
      </div>

      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div v-if="invoices.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500">
          Aucune facture pour le moment.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[900px]">
            <thead>
              <tr class="border-b border-slate-200 text-left">
                <th class="px-4 py-3">Numéro</th>
                <th class="px-4 py-3">Client</th>
                <th class="px-4 py-3">Total</th>
                <th class="px-4 py-3">Statut</th>
                <th class="px-4 py-3">Échéance</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="invoice in invoices" :key="invoice.id" class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-4 font-medium text-slate-800">{{ invoice.invoice_number }}</td>
                <td class="px-4 py-4 text-slate-600">{{ invoice.client_name }}</td>
                <td class="px-4 py-4 text-slate-600">{{ invoice.total_amount }}</td>
                <td class="px-4 py-4">
                  <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="statusClass(invoice.status)">
                    {{ formatStatus(invoice.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-slate-600">{{ invoice.due_date || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { getInvoices } from '../../api/invoices'

const invoices = ref([])
const errorMessage = ref('')

const loadInvoices = async () => {
  try {
    const response = await getInvoices()
    invoices.value = response.data.invoices || []
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Erreur de chargement.'
  }
}

const formatStatus = (status) => {
  if (status === 'paid') return 'Payée'
  if (status === 'pending') return 'En attente'
  return status
}

const statusClass = (status) => {
  if (status === 'paid') return 'bg-emerald-50 text-emerald-700'
  if (status === 'pending') return 'bg-amber-50 text-amber-700'
  return 'bg-slate-100 text-slate-700'
}

onMounted(loadInvoices)
</script>