<template>
  <footer class="bg-dark text-cream">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Logo & Description -->
        <div class="md:col-span-1">
          <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 gradient-gold rounded-full flex items-center justify-center">
              <i class="fas fa-award text-white"></i>
            </div>
            <h3 class="font-serif text-lg font-bold">Талант-центр</h3>
          </div>
          <p class="text-sm text-warm-gray mb-4">
            Всероссийская платформа для организации и проведения творческих конкурсов и олимпиад.
          </p>
 
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="font-serif font-semibold text-gold mb-4">Разделы</h4>
          <ul class="space-y-2 text-sm">
            <li>
              <a href="/demo/" class="text-warm-gray hover:text-gold transition-colors">Главная</a>
            </li>
            <li>
              <a href="/demo/contests" class="text-warm-gray hover:text-gold transition-colors">Конкурсы</a>
            </li>
            <li>
              <a href="#news" class="text-warm-gray hover:text-gold transition-colors">Новости</a>
            </li>

          </ul>
        </div>

        <!-- For Organizers -->
        <div>
          <h4 class="font-serif font-semibold text-gold mb-4">Организаторам</h4>
          <ul class="space-y-2 text-sm">
            <li>
              <a href="/demo/organizer" class="text-warm-gray hover:text-gold transition-colors">Личный кабинет</a>
            </li>
            <li>
              <a href="/demo/organizer~contests/create" class="text-warm-gray hover:text-gold transition-colors">Создать конкурс</a>
            </li>
            <li>
              <a href="#" class="text-warm-gray hover:text-gold transition-colors">Документация</a>
            </li>
            <li>
              <a href="#" class="text-warm-gray hover:text-gold transition-colors">Тарифы</a>
            </li>
          </ul>
        </div>

        <!-- Contacts -->
        <div id="contacts">
          <h4 class="font-serif font-semibold text-gold mb-4">Контакты</h4>
          <ul class="space-y-2 text-sm text-warm-gray">
            <li class="flex items-center space-x-2">
              <i class="fas fa-phone text-gold"></i>
              <span>8 (495) 127-12-92</span>
            </li>
            <li class="flex items-center space-x-2">
              <i class="fas fa-envelope text-gold"></i>
              <span>info@talant-centr.ru</span>
            </li>
            <li class="flex items-center space-x-2">
              <i class="fas fa-map-marker-alt text-gold"></i>
              <span>г. Москва</span>
            </li>
 
          </ul>
        </div>
      </div>

      <div class="border-t border-warm-gray/30 mt-8 pt-8 text-center text-sm text-warm-gray">
        <p>&copy; 2025 Талант-центр. Все права защищены.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
</script>