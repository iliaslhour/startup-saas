import api from './axios'

export const getProjects = () => api.get('/projects')
export const createProject = (payload) => api.post('/projects', payload)
export const getProject = (id) => api.get('/projects/${id}')
export const updateProject = (id, payload) => api.put('/projects/${id}, payload')
export const deleteProject = (id) => api.delete('/projects/${id}')
export const getProjectMembers = (id) => api.get('/projects/${id}/members')
export const addProjectMember = (id, payload) => api.post('/projects/${id}'/members, payload)
export const removeProjectMember = (projectId, userId) =>api.delete('/projects/${projectId}','/members/${userId}')