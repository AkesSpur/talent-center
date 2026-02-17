<template>
  <header class="bg-cream shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <a href="/demo/" class="flex items-center space-x-3">
          <div class="w-12 h-12 gradient-gold rounded-full flex items-center justify-center">
            <i class="fas fa-award text-white text-xl"></i>
          </div>
          <div>
            <h1 class="font-serif text-xl font-bold text-primary">Талант-центр</h1>
            <p class="text-xs text-warm-gray">Всероссийский центр талантов</p>
          </div>
        </a>

        <!-- Navigation -->
        <nav class="hidden md:flex space-x-8">
          <a href="/demo/" class="text-dark hover:text-primary transition-colors font-medium">
            Главная
          </a>
          <a href="/demo/contests" class="text-dark hover:text-primary transition-colors font-medium">
            Конкурсы
          </a>
          <a href="#news" class="text-dark hover:text-primary transition-colors font-medium">
            Новости
          </a>
        </nav>

        <!-- User Actions -->
        <div class="flex items-center space-x-4">
          <template v-if="ctx.user">
            <div class="relative">
              <button 
                @click="showUserMenu = !showUserMenu"
                class="flex items-center space-x-2 text-dark hover:text-primary transition-colors"
              >
                <img 
                  v-if="ctx.user.imageUrl" 
                  :src="ctx.user.imageUrl" 
                  class="w-8 h-8 rounded-full object-cover"
                />
                <div v-else class="w-8 h-8 gradient-gold rounded-full flex items-center justify-center">
                  <i class="fas fa-user text-white text-sm"></i>
                </div>
                <span class="hidden sm:block font-medium">{{ ctx.user.displayName }}</span>
                <i class="fas fa-chevron-down text-xs"></i>
              </button>
              
              <!-- Dropdown -->
              <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 z-50">
                <a href="/demo/contestant" class="block px-4 py-2 text-sm text-dark hover:bg-cream-dark">
                  <i class="fas fa-user-circle mr-2"></i>Мой профиль
                </a>
                <a href="/demo/contestant~applications" class="block px-4 py-2 text-sm text-dark hover:bg-cream-dark">
                  <i class="fas fa-clipboard-list mr-2"></i>Мои заявки
                </a>
                <a href="/demo/contestant~awards" class="block px-4 py-2 text-sm text-dark hover:bg-cream-dark">
                  <i class="fas fa-trophy mr-2"></i>Мои награды
                </a>
                <a href="/demo/contestant~participants" class="block px-4 py-2 text-sm text-dark hover:bg-cream-dark">
                  <i class="fas fa-users mr-2"></i>Мои участники
                </a>
                
                <!-- Organizer Section -->
                <template v-if="hasOrganizerAccess">
                  <hr class="my-2" />
                  <div class="px-4 py-1 text-xs font-medium text-gray-500 uppercase">Организатор</div>
                  <a href="/demo/organizer" class="block px-4 py-2 text-sm text-dark hover:bg-cream-dark">
                    <i class="fas fa-building mr-2"></i>Личный кабинет
                  </a>
                  <a href="/demo/organizer~contests" class="block px-4 py-2 text-sm text-dark hover:bg-cream-dark">
                    <i class="fas fa-list mr-2"></i>Мои конкурсы
                  </a>
                  <a href="/demo/organizer~organizations" class="block px-4 py-2 text-sm text-dark hover:bg-cream-dark">
                    <i class="fas fa-sitemap mr-2"></i>Управление организацией
                  </a>
                </template>
                
                <!-- Admin Section -->
                <template v-if="isAdmin">
                  <hr class="my-2" />
                  <div class="px-4 py-1 text-xs font-medium text-gray-500 uppercase">Администрирование</div>
                  <a href="/demo/admin" class="block px-4 py-2 text-sm text-dark hover:bg-cream-dark">
                    <i class="fas fa-cog mr-2"></i>Админ-панель
                  </a>
                </template>
                
                <hr class="my-2" />
                <button 
                  @click="handleLogout"
                  class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  <i class="fas fa-sign-out-alt mr-2"></i>Выйти
                </button>
              </div>
            </div>
          </template>
          <template v-else>
            <a 
              :href="`/s/auth/signin?back=${encodeURIComponent(ctx.req?.path || '/demo')}`"
              class="px-4 py-2 text-primary hover:text-primary-dark transition-colors font-medium"
            >
              Войти
            </a>
            <a 
              :href="`/s/auth/signin?back=${encodeURIComponent(ctx.req?.path || '/demo')}`"
              class="px-6 py-2 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity"
            >
              Регистрация
            </a>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiGetAccessibleOrganizersRoute } from '../api/platform'

const showUserMenu = ref(false)
const accessibleOrganizers = ref([])
const loading = ref(false)

const isAdmin = computed(() => {
  return ctx.user?.is('Admin')
})

const hasOrganizerAccess = computed(() => {
  return accessibleOrganizers.value.length > 0
})

const firstOrganizer = computed(() => {
  return accessibleOrganizers.value[0] || null
})

onMounted(async () => {
  if (ctx.user) {
    loading.value = true
    try {
      accessibleOrganizers.value = await apiGetAccessibleOrganizersRoute.run(ctx)
    } catch (err) {
      console.error('Error loading accessible organizers:', err)
    } finally {
      loading.value = false
    }
  }
})

async function handleLogout() {
  await fetch('/s/auth/sign-out', { method: 'POST' })
  window.location.reload()
}
</script>