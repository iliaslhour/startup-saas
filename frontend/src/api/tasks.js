import api from './axios'

export const getTasks = () => api.get('/tasks')
export const createTask = (payload) => api.post('/tasks', payload)
export const updateTaskStatus = (taskId, payload) => api.patch(`/tasks/${taskId}/status`, payload)
export const getProjectTasks = (projectId) => api.get(`/projects/${projectId}/tasks`)
export const getProjectKanban = (projectId) => api.get(`/projects/${projectId}/kanban`)