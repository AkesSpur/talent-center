    <footer class="bg-dark text-cream py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Logo & Description -->
                <div class="md:col-span-1">
                    <div class="flex items-center space-x-3 mb-4">
                        @if(!empty($siteSettings[\App\Models\SiteSettings::SITE_LOGO]))
                            <img src="{{ asset('storage/' . $siteSettings[\App\Models\SiteSettings::SITE_LOGO]) }}"
                                 alt="Талант-центр" class="h-10 w-auto max-w-[40px] object-contain">
                        @else
                            <div class="w-10 h-10 gradient-gold rounded-full flex items-center justify-center">
                                <i class="fas fa-award text-white"></i>
                            </div>
                        @endif
                        <div>
                            <h4 class="font-serif font-bold text-cream">Талант-центр</h4>
                        </div>
                    </div>
                    <p class="text-warm-gray text-sm">
                        Всероссийская платформа для проведения онлайн-конкурсов, оценки работ и выдачи дипломов.
                    </p>
                </div>

                <!-- Разделы -->
                <div>
                    <h5 class="font-serif font-semibold text-gold mb-4">Разделы</h5>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="text-warm-gray hover:text-gold transition-colors">Главная</a></li>
                        <li><a href="{{ route('contests.index') }}" class="text-warm-gray hover:text-gold transition-colors">Конкурсы</a></li>
                        <li><a href="{{ route('development-plan') }}" class="text-warm-gray hover:text-gold transition-colors">План развития</a></li>
                        <li><a href="{{ route('diplomvtrifi.search') }}" class="text-warm-gray hover:text-gold transition-colors">Проверить диплом</a></li>
                    </ul>
                </div>

                <!-- Организаторам -->
                <div>
                    <h5 class="font-serif font-semibold text-gold mb-4">Организаторам</h5>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('organizations.create') }}" class="text-warm-gray hover:text-gold transition-colors">Создать организацию</a></li>
                        <li><a href="{{ route('contests.create') }}" class="text-warm-gray hover:text-gold transition-colors">Провести конкурс</a></li>
                        <li><a href="{{ route('development-plan') }}" class="text-warm-gray hover:text-gold transition-colors">Документация</a></li>
                    </ul>
                </div>

                <!-- Контакты -->
                <div>
                    <h5 class="font-serif font-semibold text-gold mb-4">Контакты</h5>
                    <ul class="space-y-2 text-sm">
                        @if(!empty($siteSettings[\App\Models\SiteSettings::CONTACT_EMAIL]))
                            <li class="text-warm-gray">
                                <i class="fas fa-envelope text-gold mr-2"></i>{{ $siteSettings[\App\Models\SiteSettings::CONTACT_EMAIL] }}
                            </li>
                        @endif
                        @if(!empty($siteSettings[\App\Models\SiteSettings::CONTACT_PHONE]))
                            <li class="text-warm-gray">
                                <i class="fas fa-phone text-gold mr-2"></i>{{ $siteSettings[\App\Models\SiteSettings::CONTACT_PHONE] }}
                            </li>
                        @endif
                        @if(!empty($siteSettings[\App\Models\SiteSettings::CONTACT_ADDRESS]))
                            <li class="text-warm-gray">
                                <i class="fas fa-map-marker-alt text-gold mr-2"></i>{{ $siteSettings[\App\Models\SiteSettings::CONTACT_ADDRESS] }}
                            </li>
                        @endif
                        @if(empty($siteSettings[\App\Models\SiteSettings::CONTACT_EMAIL]) && empty($siteSettings[\App\Models\SiteSettings::CONTACT_PHONE]) && empty($siteSettings[\App\Models\SiteSettings::CONTACT_ADDRESS]))
                            <li class="text-warm-gray/50 text-xs italic">Контактные данные не указаны</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Separator -->
            <div class="border-t border-warm-gray/30 pt-6">
                <div class="flex flex-col items-center gap-2">
                    <p class="text-warm-gray text-sm text-center">
                        &copy; {{ date('Y') }} Талант-центр. Все права защищены.
                    </p>
                    @if(!empty($siteSettings[\App\Models\SiteSettings::PRIVACY_POLICY]))
                        <a href="{{ route('privacy-policy') }}" target="_blank"
                            class="text-gold underline underline-offset-4 hover:text-gold/80 transition-colors text-sm font-medium">
                            Политика конфиденциальности
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </footer>
