<template>
    <DashboardLayout>
      <div class="space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-3xl font-bold text-slate-900">Tâches</h2>
              <p class="mt-2 text-slate-500">Crée et gère les tâches de ton organisation.</p>
            </div>
          </div>
  
          <form class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-8" @submit.prevent="submitTask">
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Projet</label>
              <select v-model="form.project_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3.5">
                <option value="">Choisir un projet</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }}
                </option>
              </select>
            </div>
  
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Titre</label>
              <input v-model="form.title" type="text" class="w-full rounded-2xl border border-slate-300 px-4 py-3.5" />
            </div>
  
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Assigné à</label>
              <select v-model="form.assigned_to" class="w-full rounded-2xl border border-slate-300 px-4 py-3.5">
                <option value="">Aucun</option>
                <option v-for="member in members" :key="member.id" :value="member.id">
                  {{ member.name }}
                </option>
              </select>
            </div>
  
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Statut</label>
              <select v-model="form.status" class="w-full rounded-2xl border border-slate-300 px-4 py-3.5">
                <option value="todo">À faire</option>
                <option value="in_progress">En cours</option>
                <option value="done">Terminée</option>
              </select>
            </div>
  
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Priorité</label>
              <select v-model="form.priority" class="w-full rounded-2xl border border-slate-300 px-4 py-3.5">
                <option value="low">Faible</option>
                <option value="medium">Moyenne</option>
                <option value="high">Élevée</option>
              </select>
            </div>
  
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-700">Date limite</label>
              <input v-model="form.due_date" type="date" class="w-full rounded-2xl border border-slate-300 px-4 py-3.5" />
            </div>
  
            <div class="md:col-span-2 xl:col-span-3">
              <label class="mb-2 block text-sm font-medium text-slate-700">Description</label>
              <textarea v-model="form.description" class="w-full rounded-2xl border border-slate-300 px-4 py-3.5"></textarea>
            </div>
  
            <div class="md:col-span-2 xl:col-span-3">
              <button class="rounded-2xl bg-slate-900 px-6 py-3.5 text-white font-semibold hover:bg-slate-700 transition">
                Créer la tâche
              </button>
            </div>
          </form>
  
          <div v-if="errorMessage" class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ errorMessage }}
          </div>
  
          <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
              <thead>
                <tr class="border-b border-slate-200 text-left">
                  <th class="px-4 py-3">Titre</th>
                  <th class="px-4 py-3">Projet</th>
                  <th class="px-4 py-3">Assigné</th>
                  <th class="px-4 py-3">Statut</th>
                  <th class="px-4 py-3">Priorité</th>
                  <th class="px-4 py-3">Date limite</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="task in tasks" :key="task.id" class="border-b border-slate-100 hover:bg-slate-50">
                  <td class="px-4 py-4 font-medium text-slate-800">{{ task.title }}</td>
                  <td class="px-4 py-4 text-slate-600">{{ task.project?.name || '-' }}</td>
                  <td class="px-4 py-4 text-slate-600">{{ task.assignee?.name || '-' }}</td>
                  <td class="px-4 py-4">
                    <select
                      :value="task.status"
                      class="rounded-xl border border-slate-300 px-3 py-2"
                      @change="changeStatus(task.id, $event.target.value)"
                    >
                      <option value="todo">À faire</option>
                      <option value="in_progress">En cours</option>
                      <option value="done">Terminée</option>
                    </select>
                  </td>
                  <td class="px-4 py-4">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="priorityClass(task.priority)">{{ formatPriority(task.priority) }}</span>
                  </td>
                  <td class="px-4 py-4 text-slate-600">{{ task.due_date || '-' }}</td>
                </tr>
  
                <tr v-if="tasks.length === 0">
                  <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                    Aucune tâche pour le moment.
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
  import { onMounted, reactive, ref } from 'vue'
  import DashboardLayout from '../../layouts/DashboardLayout.vue'
  import { getProjects } from '../../api/projects'
  import { getMembers } from '../../api/members'
  import { createTask, getTasks, updateTaskStatus } from '../../api/tasks'
  
  const tasks = ref([])
  const projects = ref([])
  const members = ref([])
  const errorMessage = ref('')
  
  const form = reactive({
    project_id: '',
    title: '',
    description: '',
    assigned_to: '',
    status: 'todo',
    priority: 'medium',
    due_date: '',
  })
  
  const loadData = async () => {
    try {
      const [tasksResponse, projectsResponse, membersResponse] = await Promise.all([
        getTasks(),
        getProjects(),
        getMembers(),
      ])
  
      tasks.value = tasksResponse.data.tasks || []
      projects.value = projectsResponse.data.projects || []
      members.value = membersResponse.data.members || []
    } catch (error) {
      errorMessage.value = error.response?.data?.message || 'Erreur de chargement.'
    }
  }
  
  const submitTask = async () => {
    errorMessage.value = ''
  
    try {
      await createTask({
        project_id: Number(form.project_id),
        title: form.title,
        description: form.description,
        assigned_to: form.assigned_to ? Number(form.assigned_to) : null,
        status: form.status,
        priority: form.priority,
        due_date: form.due_date || null,
      })
  
      form.project_id = ''
      form.title = ''
      form.description = ''
      form.assigned_to = ''
      form.status = 'todo'
      form.priority = 'medium'
      form.due_date = ''
  
      await loadData()
    } catch (error) {
      errorMessage.value = error.response?.data?.message || 'Erreur lors de la création.'
    }
  }
  
  const changeStatus = async (taskId, status) => {
    try {
      await updateTaskStatus(taskId, { status })
      await loadData()
    } catch (error) {
      errorMessage.value = error.response?.data?.message || 'Erreur lors de la mise à jour.'
    }
  }
  
  const formatPriority = (priority) => {
    if (priority === 'low') return 'Faible'
    if (priority === 'medium') return 'Moyenne'
    if (priority === 'high') return 'Élevée'
    return priority
  }
  
  onMounted(loadData)
  const priorityClass = (priority) => {
    if (priority === 'low') return 'bg-emerald-50 text-emerald-700'
    if (priority === 'medium') return 'bg-amber-50 text-amber-700'
    if (priority === 'high') return 'bg-red-50 text-red-700'
    return 'bg-slate-100 text-slate-700'
  }
  </script>