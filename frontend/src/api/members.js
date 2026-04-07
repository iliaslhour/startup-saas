import api from './axios'

export const getMembers = () => api.get('/members')
export const addMember = (payload) => api.post('/members', payload)