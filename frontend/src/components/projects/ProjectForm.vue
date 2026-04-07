<template>
    <form class="space-y-4" @submit.prevent="$emit('submit')">
      <div>
        <label class="block mb-1">Nom</label>
        <input v-model="form.name" type="text" class="w-full border rounded-lg px-4 py-3" />
      </div>
  
      <div>
        <label class="block mb-1">Description</label>
        <textarea v-model="form.description" class="w-full border rounded-lg px-4 py-3"></textarea>
      </div>
  
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block mb-1">Statut</label>
          <select v-model="form.status" class="w-full border rounded-lg px-4 py-3">
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="on_hold">On hold</option>
          </select>
        </div>
  
        <div>
          <label class="block mb-1">Date début</label>
          <input v-model="form.start_date" type="date" class="w-full border rounded-lg px-4 py-3" />
        </div>
  
        <div>
          <label class="block mb-1">Date fin</label>
          <input v-model="form.end_date" type="date" class="w-full border rounded-lg px-4 py-3" />
        </div>
      </div>
  
      <button class="px-5 py-3 rounded-lg bg-slate-900 text-white">
        Enregistrer
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
  
  defineEmits(['submit'])
  </script>