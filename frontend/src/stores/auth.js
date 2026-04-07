import { defineStore } from 'pinia'
import { loginRequest, logoutRequest, meRequest, registerRequest } from '../api/auth'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    token: localStorage.getItem('auth_token'),
    loading: false,
    initialized: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    currentOrganizationId: () => localStorage.getItem('current_organization_id'),
  },

  actions: {
    setAuthData(token, user) {
      this.token = token
      this.user = user

      localStorage.setItem('auth_token', token)
      localStorage.setItem('auth_user', JSON.stringify(user))

      const firstOrganizationId =
        user?.current_organization_id ||
        user?.organizations?.[0]?.id ||
        null

      if (firstOrganizationId) {
        localStorage.setItem('current_organization_id', String(firstOrganizationId))
      }
    },

    clearAuthData() {
      this.user = null
      this.token = null

      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      localStorage.removeItem('current_organization_id')
    },

    async register(payload) {
      this.loading = true

      try {
        const response = await registerRequest(payload)
        this.setAuthData(response.data.token, response.data.user)
        return response.data
      } finally {
        this.loading = false
      }
    },

    async login(payload) {
      this.loading = true

      try {
        const response = await loginRequest(payload)
        this.setAuthData(response.data.token, response.data.user)
        return response.data
      } finally {
        this.loading = false
      }
    },

    async fetchMe() {
      if (!this.token) {
        this.initialized = true
        return null
      }

      try {
        const response = await meRequest()
        this.user = response.data.user
        localStorage.setItem('auth_user', JSON.stringify(response.data.user))
        return response.data.user
      } catch (error) {
        this.clearAuthData()
        throw error
      } finally {
        this.initialized = true
      }
    },

    async logout() {
      try {
        if (this.token) {
          await logoutRequest()
        }
      } finally {
        this.clearAuthData()
      }
    },

    setCurrentOrganizationId(organizationId) {
      if (organizationId) {
        localStorage.setItem('current_organization_id', String(organizationId))
      } else {
        localStorage.removeItem('current_organization_id')
      }
    },
  },
})