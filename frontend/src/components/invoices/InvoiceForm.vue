<template>
    <form class="space-y-4" @submit.prevent="$emit('submit')">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input v-model="form.client_name" type="text" placeholder="Nom client" class="border rounded-lg px-4 py-3" />
        <input v-model="form.client_email" type="email" placeholder="Email client" class="border rounded-lg px-4 py-3" />
      </div>
  
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input v-model="form.issue_date" type="date" class="border rounded-lg px-4 py-3" />
        <input v-model="form.due_date" type="date" class="border rounded-lg px-4 py-3" />
        <input v-model="form.tax_amount" type="number" step="0.01" placeholder="Taxe" class="border rounded-lg px-4 py-3" />
      </div>
  
      <div class="space-y-3">
        <div
          v-for="(item, index) in form.items"
          :key="index"
          class="grid grid-cols-1 md:grid-cols-3 gap-4"
        >
          <input v-model="item.description" type="text" placeholder="Description" class="border rounded-lg px-4 py-3" />
          <input v-model="item.quantity" type="number" step="0.01" placeholder="Quantité" class="border rounded-lg px-4 py-3" />
          <input v-model="item.unit_price" type="number" step="0.01" placeholder="Prix unitaire" class="border rounded-lg px-4 py-3" />
        </div>
      </div>
  
      <button type="button" class="px-4 py-2 rounded-lg border" @click="$emit('add-item')">
        Ajouter ligne
      </button>
  
      <button class="px-5 py-3 rounded-lg bg-slate-900 text-white">
        Enregistrer la facture
      </button>
    </form>
  </template>
  
  <script setup>
  defineProps({
    form: {
      type: Object,
      required: true,
    },
  })
  
  defineEmits(['submit', 'add-item'])
  </script>