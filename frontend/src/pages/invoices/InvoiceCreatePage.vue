<template>
    <DashboardLayout>
      <div class="max-w-4xl bg-white rounded-2xl shadow p-6">
        <h2 class="text-2xl font-bold mb-2">Créer une facture</h2>
        <p class="text-slate-500 mb-6">Ajoute une nouvelle facture</p>
  
        <div v-if="errorMessage" class="text-red-600 mb-4">{{ errorMessage }}</div>
  
        <InvoiceForm :form="form" @submit="submitForm" @add-item="addItem" />
      </div>
    </DashboardLayout>
  </template>
  
  <script setup>
  import { reactive, ref } from 'vue'
  import { useRouter } from 'vue-router'
  import DashboardLayout from '../../layouts/DashboardLayout.vue'
  import InvoiceForm from '../../components/invoices/InvoiceForm.vue'
  import { createInvoice } from '../../api/invoices'
  
  const router = useRouter()
  const errorMessage = ref('')
  
  const form = reactive({
    project_id: null,
    client_name: '',
    client_email: '',
    tax_amount: 0,
    status: 'pending',
    issue_date: '',
    due_date: '',
    notes: '',
    items: [
      {
        description: '',
        quantity: 1,
        unit_price: 0,
      },
    ],
  })
  
  const addItem = () => {
    form.items.push({
      description: '',
      quantity: 1,
      unit_price: 0,
    })
  }
  
  const submitForm = async () => {
    errorMessage.value = ''
  
    try {
      await createInvoice(form)
      router.push('/invoices')
    } catch (error) {
      errorMessage.value = error.response?.data?.message || 'Erreur lors de la création.'
    }
  }
  </script>