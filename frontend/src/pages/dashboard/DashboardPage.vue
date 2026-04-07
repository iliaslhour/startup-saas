<template>
  <DashboardLayout>
    <div class="space-y-8">
      <div>
        <h2 class="text-3xl font-bold text-slate-900">Vue générale</h2>
        <p class="mt-2 text-slate-500">Résumé global de ton organisation active.</p>
      </div>

      <div v-if="loading" class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-500 shadow-sm">
        Chargement du dashboard...
      </div>

      <div v-else-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-600">
        {{ errorMessage }}
      </div>

      <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <p class="text-sm font-medium text-slate-500">Projets actifs</p>
          <p class="mt-4 text-4xl font-bold text-slate-900">{{ summary.projects_active }}</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <p class="text-sm font-medium text-slate-500">Membres</p>
          <p class="mt-4 text-4xl font-bold text-slate-900">{{ summary.members_count }}</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <p class="text-sm font-medium text-slate-500">Tâches totales</p>
          <p class="mt-4 text-4xl font-bold text-slate-900">{{ summary.tasks_total }}</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <p class="text-sm font-medium text-slate-500">Notifications non lues</p>
          <p class="mt-4 text-4xl font-bold text-slate-900">{{ summary.notifications_unread }}</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <p class="text-sm font-medium text-slate-500">Tâches terminées</p>
          <p class="mt-4 text-4xl font-bold text-emerald-600">{{ summary.tasks_done }}</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <p class="text-sm font-medium text-slate-500">Tâches en cours</p>
          <p class="mt-4 text-4xl font-bold text-amber-600">{{ summary.tasks_in_progress }}</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <p class="text-sm font-medium text-slate-500">Tâches en retard</p>
          <p class="mt-4 text-4xl font-bold text-red-600">{{ summary.tasks_overdue }}</p>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <p class="text-sm font-medium text-slate-500">Taux de complétion</p>
          <p class="mt-4 text-4xl font-bold text-sky-600">{{ summary.completion_rate }}%</p>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <h3 class="text-xl font-bold text-slate-900">Résumé financier</h3>
          <p class="mt-2 text-slate-500">Aperçu des factures de l’organisation.</p>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
              <p class="text-sm text-slate-500">Nombre de factures</p>
              <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary.invoices_count }}</p>
            </div>

            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100">
              <p class="text-sm text-slate-500">Montant total</p>
              <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary.invoices_total_amount }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
          <h3 class="text-xl font-bold text-slate-900">État de progression</h3>
          <p class="mt-2 text-slate-500">Lecture rapide de l’avancement global.</p>

          <div class="mt-6 space-y-4">
            <div>
              <div class="mb-2 flex items-center justify-between text-sm">
                <span class="text-slate-600">Tâches terminées</span>
                <span class="font-medium text-slate-800">{{ summary.completion_rate }}%</span>
              </div>
              <div class="h-3 rounded-full bg-slate-200 overflow-hidden">
                <div
                  class="h-full rounded-full bg-sky-500"
                  :style="{ width: `${summary.completion_rate}%` }"
                ></div>
              </div>
            </div>

            <div class="text-sm text-slate-500">
              {{ summary.tasks_done }} tâche(s) terminée(s) sur {{ summary.tasks_total }}.
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { getDashboardSummary } from '../../api/dashboard'

const loading = ref(true)
const errorMessage = ref('')

const summary = reactive({
  projects_active: 0,
  tasks_total: 0,
  tasks_done: 0,
  tasks_in_progress: 0,
  tasks_overdue: 0,
  completion_rate: 0,
  members_count: 0,
  invoices_count: 0,
  notifications_unread: 0,
  invoices_total_amount: 0,
})

const loadDashboard = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await getDashboardSummary()
    Object.assign(summary, response.data.summary)
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Impossible de charger le dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>