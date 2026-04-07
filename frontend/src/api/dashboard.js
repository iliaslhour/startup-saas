import api from './axios'

export const getDashboardSummary = () => api.get('/dashboard/summary')
export const getDashboardProjects = () => api.get('/dashboard/projects')
export const getDashboardTasks = () => api.get('/dashboard/tasks')
export const getDashboardWorkload = () => api.get('/dashboard/workload')
export const getDashboardActivity = () => api.get('/dashboard/activity')