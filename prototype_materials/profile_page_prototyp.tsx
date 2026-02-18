<template>
  <main class="min-h-screen py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="font-serif text-3xl font-bold text-dark mb-2">Мой профиль</h1>
        <p class="text-warm-gray">Управление личными данными и участниками</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-lg p-6 text-center mb-6">
            <div class="relative inline-block mb-4">
              <img 
                v-if="ctx.user?.imageUrl"
                :src="ctx.user.imageUrl"
                class="w-32 h-32 rounded-full object-cover border-4 border-gold mx-auto"
              />
              <div v-else class="w-32 h-32 gradient-gold rounded-full flex items-center justify-center mx-auto border-4 border-white">
                <i class="fas fa-user text-4xl text-white"></i>
              </div>
              <button class="absolute bottom-0 right-0 w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center shadow-lg hover:bg-primary-dark transition-colors">
                <i class="fas fa-camera"></i>
              </button>
            </div>
            <h2 class="font-serif text-xl font-semibold text-dark mb-1">{{ ctx.user?.displayName }}</h2>
            <p class="text-warm-gray text-sm mb-4">Конкурсант</p>
            <div class="flex justify-center space-x-2">
              <span v-if="ctx.user?.confirmedEmail" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                <i class="fas fa-check mr-1"></i> Email подтвержден
              </span>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="font-semibold text-dark mb-4">Статистика</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-warm-gray text-sm">Участников</span>
                <span class="font-semibold text-dark">{{ participants.length }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-warm-gray text-sm">Заявок</span>
                <span class="font-semibold text-dark">{{ applicationsCount }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Tabs -->
          <div class="border-b border-primary/20">
            <nav class="flex space-x-8">
              <a href="/demo/contestant~profile" class="py-4 px-1 border-b-2 border-primary text-primary font-medium">
                <i class="fas fa-user mr-2"></i>Личные данные
              </a>
              <a href="/demo/contestant~participants" class="py-4 px-1 text-warm-gray hover:text-primary transition-colors">
                <i class="fas fa-users mr-2"></i>Мои участники
              </a>
              <a href="/demo/contestant~applications" class="py-4 px-1 text-warm-gray hover:text-primary transition-colors">
                <i class="fas fa-clipboard-list mr-2"></i>Мои заявки
              </a>
              <a href="/demo/contestant~awards" class="py-4 px-1 text-warm-gray hover:text-primary transition-colors">
                <i class="fas fa-trophy mr-2"></i>Мои награды
              </a>
            </nav>
          </div>

          <!-- Personal Info -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="font-serif text-xl font-semibold text-dark mb-6">Личные данные</h3>
            
            <form @submit.prevent="saveProfile" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-dark mb-2">Имя</label>
                  <input 
                    v-model="form.firstName"
                    type="text"
                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-dark mb-2">Фамилия</label>
                  <input 
                    v-model="form.lastName"
                    type="text"
                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-dark mb-2">Биография</label>
                <textarea 
                  v-model="form.bio"
                  rows="3"
                  placeholder="Расскажите о себе, своих увлечениях..."
                  class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 resize-none"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-dark mb-2">Город</label>
                  <input 
                    v-model="form.city"
                    type="text"
                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-dark mb-2">Страна</label>
                  <input 
                    v-model="form.country"
                    type="text"
                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-dark mb-2">Образование</label>
                <input 
                  v-model="form.education"
                  type="text"
                  placeholder="Учебное заведение, специальность..."
                  class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
                />
              </div>

              <!-- Messages -->
              <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ error }}
              </div>
              <div v-if="success" class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700">
                <i class="fas fa-check-circle mr-2"></i>{{ success }}
              </div>

              <div class="flex gap-4">
                <button 
                  type="submit"
                  :disabled="saving"
                  class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50"
                >
                  <span v-if="saving"><i class="fas fa-spinner fa-spin mr-2"></i>Сохранение...</span>
                  <span v-else>Сохранить изменения</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Participants Section -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="font-serif text-xl font-semibold text-dark">Мои участники</h3>
                <p class="text-warm-gray text-sm mt-1">Управление детьми и подопечными для участия в конкурсах</p>
              </div>
              <button 
                @click="showAddParticipantModal = true"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors"
              >
                <i class="fas fa-plus mr-2"></i>Добавить
              </button>
            </div>

            <!-- Participants List -->
            <div v-if="participants.length > 0" class="space-y-3">
              <div 
                v-for="participant in participants" 
                :key="participant.id"
                class="flex items-center gap-4 p-4 border border-primary/10 rounded-lg hover:bg-cream/50 transition-colors"
              >
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-semibold text-lg">
                  {{ getInitials(participant) }}
                </div>
                <div class="flex-1">
                  <p class="font-semibold text-dark">
                    {{ participant.lastName }} {{ participant.firstName }} {{ participant.middleName }}
                  </p>
                  <p class="text-sm text-warm-gray">
                    {{ participant.age ? participant.age + ' лет' : '' }}
                    {{ participant.organization ? '• ' + participant.organization : '' }}
                    {{ participant.group ? '• ' + participant.group : '' }}
                    {{ participant.city ? '• ' + participant.city : '' }}
                  </p>
                </div>
                <div class="flex gap-2">
                  <button 
                    @click="editParticipant(participant)"
                    class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors"
                    title="Редактировать"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button 
                    @click="deleteParticipant(participant)"
                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                    title="Удалить"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-2xl text-primary"></i>
              </div>
              <p class="text-dark font-medium mb-2">У вас пока нет добавленных участников</p>
              <p class="text-warm-gray text-sm mb-4">Добавьте детей или подопечных для участия в конкурсах</p>
              <button 
                @click="showAddParticipantModal = true"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors"
              >
                <i class="fas fa-plus mr-2"></i>Добавить участника
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Participant Modal -->
    <div v-if="showAddParticipantModal || editingParticipant" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-primary/10 px-6 py-4 flex items-center justify-between">
          <h3 class="font-serif text-xl font-semibold text-dark">
            {{ editingParticipant ? 'Редактировать участника' : 'Новый участник' }}
          </h3>
          <button @click="closeParticipantModal" class="text-warm-gray hover:text-dark transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <form @submit.prevent="saveParticipant" class="p-6 space-y-4">
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-sm font-medium text-dark mb-1">Фамилия *</label>
              <input 
                v-model="participantForm.lastName"
                type="text"
                required
                class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-dark mb-1">Имя *</label>
              <input 
                v-model="participantForm.firstName"
                type="text"
                required
                class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-dark mb-1">Отчество</label>
              <input 
                v-model="participantForm.middleName"
                type="text"
                class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-dark mb-1">Возраст *</label>
              <input 
                v-model.number="participantForm.age"
                type="number"
                min="1"
                max="120"
                required
                class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-dark mb-1">Дата рождения</label>
              <input 
                v-model="participantForm.birthDate"
                type="date"
                class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-dark mb-1">Организация</label>
            <input 
              v-model="participantForm.organization"
              type="text"
              placeholder="Школа, студия, кружок..."
              class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-dark mb-1">Город</label>
            <input 
              v-model="participantForm.city"
              type="text"
              class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-dark mb-1">Курс/класс/группа</label>
            <input 
              v-model="participantForm.group"
              type="text"
              placeholder="5А, 10 класс, начальный курс..."
              class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30"
            />
          </div>

          <div v-if="participantError" class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ participantError }}
          </div>

          <div class="flex gap-3 pt-4">
            <button 
              type="button"
              @click="closeParticipantModal"
              class="flex-1 py-2 border border-primary text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors"
            >
              Отмена
            </button>
            <button 
              type="submit"
              :disabled="savingParticipant"
              class="flex-1 py-2 gradient-gold text-dark font-medium rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50"
            >
              <span v-if="savingParticipant"><i class="fas fa-spinner fa-spin mr-1"></i>Сохранение...</span>
              <span v-else>Сохранить</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { apiGetMyProfileRoute, apiSaveMyProfileRoute } from "../../api/platform"
import { 
  apiMyParticipantsRoute, 
  apiCreateParticipantRoute, 
  apiUpdateParticipantRoute, 
  apiDeleteParticipantRoute 
} from "../../api/participants"
import { apiMyApplicationsRoute } from "../../api/platform"

const form = reactive({
  firstName: ctx.user?.firstName || '',
  lastName: ctx.user?.lastName || '',
  bio: '',
  city: '',
  country: '',
  education: ''
})

const participantForm = reactive({
  firstName: '',
  lastName: '',
  middleName: '',
  age: null,
  birthDate: '',
  organization: '',
  city: '',
  group: ''
})

const participants = ref([])
const applicationsCount = ref(0)
const saving = ref(false)
const error = ref('')
const success = ref('')

const showAddParticipantModal = ref(false)
const editingParticipant = ref(null)
const savingParticipant = ref(false)
const participantError = ref('')

onMounted(async () => {
  await Promise.all([
    loadProfile(),
    loadParticipants(),
    loadApplicationsCount()
  ])
})

async function loadProfile() {
  try {
    const profile = await apiGetMyProfileRoute.run(ctx)
    if (profile) {
      form.bio = profile.bio || ''
      form.city = profile.city || ''
      form.country = profile.country || ''
      form.education = profile.education || ''
    }
  } catch (err) {
    console.error('Error loading profile:', err)
  }
}

async function loadParticipants() {
  try {
    const result = await apiMyParticipantsRoute.run(ctx)
    participants.value = result.participants || []
  } catch (err) {
    console.error('Error loading participants:', err)
  }
}

async function loadApplicationsCount() {
  try {
    const apps = await apiMyApplicationsRoute.run(ctx)
    applicationsCount.value = apps?.length || 0
  } catch (err) {
    console.error('Error loading applications:', err)
  }
}

function getInitials(participant) {
  if (!participant) return '?'
  const first = participant.firstName?.[0] || ''
  const last = participant.lastName?.[0] || ''
  return (last + first).toUpperCase() || '?'
}

function editParticipant(participant) {
  editingParticipant.value = participant
  participantForm.firstName = participant.firstName
  participantForm.lastName = participant.lastName
  participantForm.middleName = participant.middleName || ''
  participantForm.age = participant.age
  participantForm.birthDate = participant.birthDate ? new Date(participant.birthDate).toISOString().split('T')[0] : ''
  participantForm.organization = participant.organization || ''
  participantForm.city = participant.city || ''
  participantForm.group = participant.group || ''
}

function closeParticipantModal() {
  showAddParticipantModal.value = false
  editingParticipant.value = null
  participantError.value = ''
  // Reset form
  participantForm.firstName = ''
  participantForm.lastName = ''
  participantForm.middleName = ''
  participantForm.age = null
  participantForm.birthDate = ''
  participantForm.organization = ''
  participantForm.city = ''
  participantForm.group = ''
}

async function saveParticipant() {
  if (!participantForm.firstName || !participantForm.lastName || !participantForm.age) {
    participantError.value = 'Заполните обязательные поля'
    return
  }

  savingParticipant.value = true
  participantError.value = ''

  try {
    const data = {
      firstName: participantForm.firstName,
      lastName: participantForm.lastName,
      middleName: participantForm.middleName,
      age: participantForm.age,
      birthDate: participantForm.birthDate || undefined,
      organization: participantForm.organization,
      city: participantForm.city,
      group: participantForm.group
    }

    if (editingParticipant.value) {
      await apiUpdateParticipantRoute({ id: editingParticipant.value.id }).run(ctx, data)
    } else {
      await apiCreateParticipantRoute.run(ctx, data)
    }

    await loadParticipants()
    closeParticipantModal()
  } catch (err) {
    participantError.value = err.message || 'Ошибка при сохранении'
  } finally {
    savingParticipant.value = false
  }
}

async function deleteParticipant(participant) {
  if (!confirm(`Удалить участника ${participant.firstName} ${participant.lastName}?`)) {
    return
  }

  try {
    await apiDeleteParticipantRoute({ id: participant.id }).run(ctx)
    await loadParticipants()
  } catch (err) {
    alert('Ошибка при удалении: ' + (err.message || 'Неизвестная ошибка'))
  }
}

async function saveProfile() {
  saving.value = true
  error.value = ''
  success.value = ''

  try {
    await apiSaveMyProfileRoute.run(ctx, {
      firstName: form.firstName,
      lastName: form.lastName,
      bio: form.bio,
      city: form.city,
      country: form.country,
      education: form.education
    })

    success.value = 'Профиль успешно обновлен'
    setTimeout(() => success.value = '', 3000)
  } catch (err) {
    error.value = err.message || 'Ошибка при сохранении профиля'
  } finally {
    saving.value = false
  }
}
</script>