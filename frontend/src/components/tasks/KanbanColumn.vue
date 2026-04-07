<template>
  <div class="rounded-3xl bg-slate-50 border border-slate-200 p-4 min-h-[500px]">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-slate-800">{{ column.label }}</h3>
      <span class="inline-flex rounded-full bg-white border border-slate-200 px-3 py-1 text-sm font-medium text-slate-600">
        {{ column.count }}
      </span>
    </div>

    <div class="space-y-4">
      <div
        v-for="task in column.tasks"
        :key="task.id"
        class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm hover:shadow-md transition"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <h4 class="font-semibold text-slate-900">{{ task.title }}</h4>
            <p class="mt-1 text-sm text-slate-500">
              {{ task.description || 'Aucune description' }}
            </p>
          </div>

          <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="priorityClass(task.priority)">
            {{ formatPriority(task.priority) }}
          </span>
        </div>

        <div class="mt-4 space-y-2 text-sm text-slate-500">
          <p><span class="font-medium text-slate-700">Assigné à :</span> {{ task.assignee?.name || '-' }}</p>
          <p><span class="font-medium text-slate-700">Date limite :</span> {{ task.due_date || '-' }}</p>
        </div>

        <div class="mt-4">
          <label class="mb-2 block text-sm font-medium text-slate-700">Changer le statut</label>
          <select
            class="w-full rounded-xl border border-slate-300 px-3 py-2"
            :value="task.status"
            @change="$emit('change-status', task.id, $event.target.value)"
          >
            <option value="todo">À faire</option>
            <option value="in_progress">En cours</option>
            <option value="done">Terminée</option>
          </select>
        </div>
      </div>

      <div
        v-if="column.tasks.length === 0"
        class="rounded-2xl border border-dashed border-slate-300 bg-white/60 p-6 text-center text-slate-400"
      >
        Aucune tâche dans cette colonne.
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  column: {
    type: Object,
    required: true,
  },
})

defineEmits(['change-status'])

const formatPriority = (priority) => {
  if (priority === 'low') return 'Faible'
  if (priority === 'medium') return 'Moyenne'
  if (priority === 'high') return 'Élevée'
  return priority
}

const priorityClass = (priority) => {
  if (priority === 'low') return 'bg-emerald-50 text-emerald-700'
  if (priority === 'medium') return 'bg-amber-50 text-amber-700'
  if (priority === 'high') return 'bg-red-50 text-red-700'
  return 'bg-slate-100 text-slate-700'
}
</script>