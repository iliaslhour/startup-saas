<template>
    <div class="min-h-screen bg-slate-100">
      <AppSidebar />
  
      <div class="min-h-screen lg:ml-72">
        <AppHeader />
  
        <main class="p-4 sm:p-6 lg:p-8">
          <slot />
        </main>
      </div>
    </div>
  </template>
  
  <script setup>
  import AppSidebar from '../components/common/AppSidebar.vue'
  import AppHeader from '../components/common/AppHeader.vue'
  </script>