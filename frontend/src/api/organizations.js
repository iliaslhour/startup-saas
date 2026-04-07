import api from './axios'

export const getOrganizations = () => api.get('/organizations')
export const createOrganization = (payload) => api.post('/organizations', payload)
export const getOrganization = (id) => api.get('/organizations/${id}')
export const updateOrganization = (id, payload) => api.put('/organizations/${id}', payload)
export const switchOrganization = (id) => api.post('/organizations/${id}/switch')
export const getOrganizationMembers = (id) => api.get('/organizations/${id}'/members)
export const addOrganizationMember = (id, payload) => api.post('/organizations/${id}'/members, payload)
export const updateOrganizationMember = (organizationId, userId, payload) =>
  api.put('/organizations/${organizationId}/members/${userId}', payload)
export const deleteOrganizationMember = (organizationId, userId) =>
  api.delete('/organizations/${organizationId}/members/${userId}')
export const getOrganizationRoles = (id) => api.get('/organizations/${id}/roles')