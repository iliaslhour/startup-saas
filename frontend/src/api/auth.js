import api from './axios'

export const registerRequest = (payload) => api.post('/auth/register', payload)
export const loginRequest = (payload) => api.post('/auth/login', payload)
export const meRequest = () => api.get('/auth/me')
export const logoutRequest = () => api.post('/auth/logout')
export const updateProfileRequest = (payload) => api.put('/auth/profile', payload)
export const changePasswordRequest = (payload) => api.put('/auth/change-password', payload)