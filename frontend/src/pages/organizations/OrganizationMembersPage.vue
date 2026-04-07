<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="mb-6">
          <h2 class="text-3xl font-bold text-slate-900">Membres de l’organisation</h2>
          <p class="mt-2 text-slate-500">
            Ajoute et gère les membres de ton espace de travail.
          </p>
        </div>

        <form class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8" @submit.prevent="submit">
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
            <input
              v-model="email"
              type="email"
              placeholder="Email du membre"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Rôle</label>
            <select
              v-model="role_id"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
            >
              <option :value="1">Admin</option>
              <option :value="2">Developer</option>
              <option :value="3">Client</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              type="submit"
              class="w-full rounded-2xl bg-slate-900 py-3.5 text-white font-semibold transition hover:bg-slate-700"
            >
              Ajouter
            </button>
          </div>
        </form>

        <div
          v-if="successMessage"
          class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
        >
          {{ successMessage }}
        </div>

        <div
          v-if="errorMessage"
          class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
        >
          {{ errorMessage }}
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[600px]">
            <thead>
              <tr class="border-b border-slate-200 text-left">
                <th class="px-4 py-3 text-sm font-semibold text-slate-700">Nom</th>
                <th class="px-4 py-3 text-sm font-semibold text-slate-700">Email</th>
                <th class="px-4 py-3 text-sm font-semibold text-slate-700">Rôle</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="member in members"
                :key="member.id"
                class="border-b border-slate-100 hover:bg-slate-50 transition"
              >
                <td class="px-4 py-4 text-slate-800 font-medium">
                  {{ member.name }}
                </td>
                <td class="px-4 py-4 text-slate-600">
                  {{ member.email }}
                </td>
                <td class="px-4 py-4">
                  <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-sm font-medium text-sky-700">
                    {{ member.role_name || formatRole(member.role_id) }}
                  </span>
                </td>
              </tr>

              <tr v-if="members.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                  Aucun membre pour le moment.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { getMembers, addMember } from '../../api/members'

const members = ref([])
const email = ref('')
const role_id = ref(1)
const errorMessage = ref('')
const successMessage = ref('')

const loadMembers = async () => {
  try {
    const res = await getMembers()
    members.value = res.data.members || []
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Erreur de chargement des membres.'
  }
}

const submit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await addMember({
      email: email.value,
      role_id: role_id.value,
    })

    email.value = ''
    role_id.value = 1
    successMessage.value = 'Membre ajouté avec succès.'

    await loadMembers()
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Erreur lors de l’ajout.'
  }
}

const formatRole = (roleId) => {
  const id = Number(roleId)

  if (id === 1) return 'Admin'
  if (id === 2) return 'Developer'
  if (id === 3) return 'Client'

  return 'Inconnu'
}

onMounted(loadMembers)
</script>