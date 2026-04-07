import api from './axios'

export const getInvoices = () => api.get('/invoices')
export const createInvoice = (payload) => api.post('/invoices', payload)
export const getInvoice = (id) => api.get('/invoices/${id}')
export const updateInvoice = (id, payload) => api.put('/invoices/${id}', payload)
export const deleteInvoice = (id) => api.delete('/invoices/${id}')
export const updateInvoiceStatus = (id, payload) => api.patch('/invoices/${id}'/status, payload)
export const archiveInvoice = (id) => api.patch('/invoices/${id}'/archive)


