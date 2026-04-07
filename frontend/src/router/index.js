import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const DashboardPage = () => import('../pages/dashboard/DashboardPage.vue')
const ProjectListPage = () => import('../pages/projects/ProjectListPage.vue')
const ProjectCreatePage = () => import('../pages/projects/ProjectCreatePage.vue')
const OrganizationCreatePage = () => import('../pages/organizations/OrganizationCreatePage.vue')
const OrganizationMembersPage = () => import('../pages/organizations/OrganizationMembersPage.vue')
const TaskListPage = () => import('../pages/tasks/TaskListPage.vue')
const KanbanBoardPage = () => import('../pages/tasks/KanbanBoardPage.vue')
const InvoiceListPage = () => import('../pages/invoices/InvoiceListPage.vue')
const InvoiceCreatePage = () => import('../pages/invoices/InvoiceCreatePage.vue')
const NotificationListPage = () => import('../pages/notifications/NotificationListPage.vue')
const LoginPage = () => import('../pages/auth/LoginPage.vue')
const RegisterPage = () => import('../pages/auth/RegisterPage.vue')
const NotFoundPage = () => import('../pages/errors/NotFoundPage.vue')


const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/dashboard' },

    { path: '/login', name: 'login', component: LoginPage, meta: { guestOnly: true } },
    { path: '/register', name: 'register', component: RegisterPage, meta: { guestOnly: true } },

    { path: '/dashboard', name: 'dashboard', component: DashboardPage, meta: { requiresAuth: true } },
    { path: '/organizations/create', name: 'organization-create', component: OrganizationCreatePage, meta: { requiresAuth: true } },
    { path: '/organizations/members', name: 'organization-members', component: OrganizationMembersPage, meta: { requiresAuth: true } },
    { path: '/projects', name: 'projects', component: ProjectListPage, meta: { requiresAuth: true } },
    { path: '/projects/create', name: 'project-create', component: ProjectCreatePage, meta: { requiresAuth: true } },
    { path: '/projects/:projectId/kanban', name: 'project-kanban', component: KanbanBoardPage, meta: { requiresAuth: true } },
    { path: '/notifications', name: 'notifications', component: NotificationListPage, meta: { requiresAuth: true } },
    { path: '/invoices', name: 'invoices', component: InvoiceListPage, meta: { requiresAuth: true } },
    { path: '/invoices/create', name: 'invoice-create', component: InvoiceCreatePage, meta: { requiresAuth: true } },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundPage },
    { path: '/tasks', name: 'tasks', component: KanbanBoardPage, meta: { requiresAuth: true }, },
    { path: '/tasks', name: 'tasks', component: TaskListPage, meta: { requiresAuth: true }, },
  ],
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()

  if (!authStore.initialized && authStore.token) {
    try {
      await authStore.fetchMe()
    } catch (error) {
    }
  } else if (!authStore.initialized) {
    authStore.initialized = true
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return { name: 'dashboard' }
  }

  return true
})

export default router