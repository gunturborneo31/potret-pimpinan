import { createRouter, createWebHistory } from 'vue-router'
import LandingPage from '@/Pages/LandingPage.vue'
import ShowPublic from '@/Pages/Kegiatan/ShowPublic.vue'

const routes = [
  {
    path: '/',
    name: 'landingpage',
    component: LandingPage,
  },
  {
    path: '/kegiatan/:slug',
    name: 'kegiatan.folders.show',
    component: ShowPublic,
    props: true,
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
