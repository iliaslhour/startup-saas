<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h2 class="text-3xl font-bold text-slate-900">Notifications</h2>
          <p class="mt-2 text-slate-500">
            Suis les événements importants de ton organisation.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <span
            class="inline-flex rounded-full bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700"
          >
            {{ unreadCount }} non lue(s)
          </span>

          <button
            class="rounded-2xl bg-slate-900 px-5 py-3 text-white font-semibold hover:bg-slate-700 transition disabled:opacity-50"
            :disabled="notifications.length === 0"
            @click="handleMarkAllAsRead"
          >
            Tout marquer comme lu
          </button>
        </div>
      </div>

      <div
        v-if="successMessage"
        class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
      >
        {{ successMessage }}
      </div>

      <div
        v-if="errorMessage"
        class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
      >
        {{ errorMessage }}
      </div>

      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div
          v-if="loading"
          class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center text-slate-500"
        >
          Chargement des notifications...
        </div>

        <div
          v-else-if="notifications.length === 0"
          class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500"
        >
          Aucune notification pour le moment.
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="rounded-2xl border p-5 transition"
            :class="notification.is_read
              ? 'border-slate-200 bg-white'
              : 'border-sky-200 bg-sky-50/40'"
          >
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
              <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3">
                  <h3 class="text-lg font-bold text-slate-900">
                    {{ notification.title }}
                  </h3>

                  <span
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                    :class="notification.is_read
                      ? 'bg-slate-100 text-slate-600'
                      : 'bg-sky-100 text-sky-700'"
                  >
                    {{ notification.is_read ? 'Lue' : 'Non lue' }}
                  </span>

                  <span
                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                    :class="typeClass(notification.type)"
                  >
                    {{ formatType(notification.type) }}
                  </span>
                </div>

                <p class="mt-3 text-slate-600">
                  {{ notification.message }}
                </p>

                <p class="mt-3 text-sm text-slate-400">
                  Créée le : {{ formatDate(notification.created_at) }}
                </p>
              </div>

              <div class="flex items-center gap-2">
                <button
                  v-if="!notification.is_read"
                  class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
                  @click="handleMarkAsRead(notification.id)"
                >
                  Marquer comme lue
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import {
  getNotifications,
  getUnreadCount,
  markNotificationAsRead,
  markAllNotificationsAsRead,
} from '../../api/notifications'

const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(true)
const errorMessage = ref('')
const successMessage = ref('')

const loadNotifications = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [notificationsResponse, unreadResponse] = await Promise.all([
      getNotifications(),
      getUnreadCount(),
    ])

    notifications.value = notificationsResponse.data.notifications || []
    unreadCount.value = unreadResponse.data.unread_count || 0
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Erreur de chargement des notifications.'
  } finally {
    loading.value = false
  }
}

const handleMarkAsRead = async (id) => {
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await markNotificationAsRead(id)
    successMessage.value = 'Notification marquée comme lue.'
    await loadNotifications()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Erreur lors de la mise à jour.'
  }
}

const handleMarkAllAsRead = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await markAllNotificationsAsRead()
    successMessage.value = 'Toutes les notifications ont été marquées comme lues.'
    await loadNotifications()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Erreur lors de la mise à jour.'
  }
}

const formatType = (type) => {
  if (type === 'member_added') return 'Membre'
  if (type === 'project_created') return 'Projet'
  if (type === 'invoice_created') return 'Facture'
  if (type === 'task_assigned') return 'Tâche'
  return 'Notification'
}

const typeClass = (type) => {
  if (type === 'member_added') return 'bg-violet-50 text-violet-700'
  if (type === 'project_created') return 'bg-sky-50 text-sky-700'
  if (type === 'invoice_created') return 'bg-emerald-50 text-emerald-700'
  if (type === 'task_assigned') return 'bg-amber-50 text-amber-700'
  return 'bg-slate-100 text-slate-700'
}

const formatDate = (value) => {
  if (!value) return '-'

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleString('fr-FR')
}

onMounted(loadNotifications)
</script>