<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-100 p-6">
      <div class="bg-white rounded-2xl shadow p-8 text-center max-w-md w-full">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">404</h1>
        <p class="text-slate-500 mb-6">Page introuvable</p>
        <RouterLink to="/dashboard" class="inline-block px-5 py-3 rounded-lg bg-slate-900 text-white">
          Retour au dashboard
        </RouterLink>
      </div>
    </div>
  </template>
  
  <script setup>
  import { RouterLink } from 'vue-router'
  </script>