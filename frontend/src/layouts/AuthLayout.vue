<template>
    <div class="min-h-screen bg-slate-100 flex items-center justify-center px-4 py-10">
      <div class="grid w-full max-w-6xl lg:grid-cols-2 overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="hidden lg:flex flex-col justify-between bg-slate-900 text-white p-10">
          <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm">
              Startup SaaS
            </div>
  
            <h1 class="mt-8 text-4xl font-bold leading-tight">
              Gère tes projets,
              ton équipe
              et ta facturation
              depuis un seul espace.
            </h1>
  
            <p class="mt-6 text-slate-300 text-lg">
              Une plateforme SaaS moderne pour organiser les startups marocaines.
            </p>
          </div>
  
          <div class="text-sm text-slate-400">
            Soutenance PFE • Laravel API • Vue SPA
          </div>
        </div>
  
        <div class="p-6 sm:p-10 lg:p-12 flex items-center">
          <div class="w-full max-w-md mx-auto">
            <slot />
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  </script>