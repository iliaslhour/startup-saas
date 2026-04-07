<template>
  <aside class="hidden lg:flex fixed left-0 top-0 z-30 h-screen w-72 flex-col bg-slate-900 text-white border-r border-slate-800">
    <div class="px-6 py-6 border-b border-slate-800">
      <div class="flex items-center gap-3">
        <div class="h-11 w-11 rounded-2xl bg-sky-500 flex items-center justify-center font-bold text-lg">
          S
        </div>
        <div>
          <h2 class="text-lg font-bold">Startup SaaS</h2>
          <p class="text-sm text-slate-400">Workspace</p>
        </div>
      </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-2">
      <RouterLink class="nav-link" to="/dashboard">Dashboard</RouterLink>
      <RouterLink class="nav-link" to="/organizations/create">Organisation</RouterLink>
      <RouterLink class="nav-link" to="/organizations/members">Membres</RouterLink>
      <RouterLink class="nav-link" to="/projects">Projets</RouterLink>
      <RouterLink class="nav-link" to="/tasks">Tâches</RouterLink>
      <RouterLink class="nav-link" to="/notifications">Notifications</RouterLink>
      <RouterLink class="nav-link" to="/invoices">Factures</RouterLink>

    </nav>


    <div class="px-4 pb-6">
      <div class="rounded-2xl bg-slate-800 p-4">
        <p class="text-sm font-medium">Version soutenance</p>
        <p class="mt-1 text-xs text-slate-400">
          UI SaaS moderne prête à présenter.
        </p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.nav-link {
  display: block;
  padding: 0.875rem 1rem;
  border-radius: 1rem;
  color: rgb(203 213 225);
  font-weight: 500;
  transition: 0.2s ease;
}

.nav-link:hover {
  background: rgb(30 41 59);
  color: white;
}

.router-link-active {
  background: rgb(14 165 233);
  color: white;
}
</style>