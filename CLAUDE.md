# CLAUDE.md

Контекст проекта для Claude Code. Личное приложение, не коммерческое. Весь UI — на русском.

## Что это

Трекер избавления от зависимостей. Фиксирует импульсы (тяга к порно/никотину/рилсам и т.д.), результат (устоял/сорвался), триггер и комментарий. Считает недельную статистику и тренды.

**Философия, которую держим в коде и текстах UI:**
- Это термометр, а не оценка человека. Никакого осуждающего тона, никаких «вы провалились».
- Метрики процесса важнее метрик исхода. Стрик не обнуляем драматично — смотрим тренд.
- Два числа отдельно, НЕ один общий процент: частота импульсов (меньше = лучше) и resist rate (больше = лучше).
- Каждая зависимость ведётся отдельно — разная тяжесть и физиология. Не сливать в общий процент.

## Стек

- PHP 8.2+, Laravel 11+
- Inertia.js + Vue 3 (`<script setup>`)
- MySQL
- Bootstrap 5 (НЕ Tailwind)
- Nginx + PHP-fpm, Ubuntu VPS

## Архитектура / конвенции

- Монолит Laravel + Inertia. SSR-подобный подход: данные отдаём через `inertia()`, фронт на props.
- Тяжёлые вычисления — на стороне БД (агрегатные запросы), НЕ тащим строки в PHP и не считаем в циклах.
- Статистику считаем одним запросом на период через `selectRaw` + `groupBy`. N+1 недопустим — для топ-триггеров по всем зависимостям использовать `topTriggersAll` (один запрос + `groupBy` коллекции), не цикл.
- `user_id` денормализован в `impulses` намеренно — все выборки идут «по юзеру за период».
- Создание сущностей строго через `auth()->user()->relation()->create()` — `user_id` проставляется сам, в `$fillable` его НЕ держим (защита от mass-assignment).
- Валидация — только через FormRequest. В правилах проверять владение: `Rule::exists(...)->where('user_id', ...)`.
- На destroy всегда `abort_unless($model->user_id === auth()->id(), 403)`.
- `reps`-подобные гибкие поля (если появятся) — varchar, не integer.

## Схема БД

```
dependencies
  id, user_id (fk, cascade), name, is_active (bool, default true), timestamps
  index (user_id, is_active)

impulses
  id, user_id (fk, cascade), dependency_id (fk, cascade),
  resisted (bool), trigger (string null), comment (text null), timestamps
  index (user_id, dependency_id, created_at)   -- закрывает все отчётные запросы
```

`SUM(resisted)` работает напрямую — boolean в MySQL это 0/1.
Недельная группировка: `YEARWEEK(created_at, 3)` (mode 3 = ISO, неделя с понедельника).

## Структура

```
app/
  Models/{Dependency,Impulse}.php
  Http/Controllers/{TrackerController,DependencyController}.php
  Http/Requests/{StoreImpulseRequest,StoreDependencyRequest}.php
  Services/ReportService.php         -- вся агрегатная аналитика
resources/js/Pages/Tracker/Index.vue -- единственная страница, 3 вкладки: Лог / Отчёт / Зависимости
database/seeders/
  DependencySeeder.php  -- стартовые зависимости
  DemoDataSeeder.php    -- демо-данные за 4 недели для проверки графиков
```

## Роуты (auth middleware)

```
GET    /tracker                          tracker.index
POST   /tracker/impulses                 impulses.store
DELETE /tracker/impulses/{impulse}       impulses.destroy
POST   /tracker/dependencies             dependencies.store
DELETE /tracker/dependencies/{dependency} dependencies.destroy
```

## Команды

```bash
# локально
php artisan migrate
php artisan db:seed --class=DemoDataSeeder   # демо-данные с трендом + flatline
php artisan tinker --execute="App\Models\Impulse::truncate();"  # сброс перед повторным сидом
npm run dev

# деплой на VPS
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate
php artisan config:clear
php artisan cache:clear
```

## Стиль кода

- Практичность без воды. Production-ready, не учебные примеры.
- Не объяснять базовые вещи — уровень senior Laravel.
- Архитектурный подход: думать о том, как масштабируется и где словит N+1.
- Vue: `<script setup>`, composition API, минимум зависимостей. Bootstrap-классы, без переусложнённого CSS.
- UI-тексты на русском, в тоне «термометр, не суд».

## На горизонте

- Стрик «дней без срыва» по каждой зависимости (расчёт одним запросом).
- Вечернее напоминание заполнить лог (notification / queue).
- PWA — оффлайн-доступ и установка на телефон.