# NFZ Finder

Aplikacja do szybkiego wyszukiwania placówek NFZ z najbliższymi terminami dla wybranego świadczenia.
**Backend:** Laravel 11 (PHP 8.3) • **Frontend:** React + Vite + TypeScript + Tailwind 3 + React Query
**Źródła danych:** NFZ ITL (Terminy Leczenia) + UMW (Umowy). **Bez logowania.**

**Cel:** „Szybko znajdź termin i - jeśli możliwe - przejdź do e-rejestracji IKP”.

---

## Spis treści

* [Funkcje](#funkcje)
* [Architektura – skrót](#architektura--skrót)
* [API](#api)
* [Cache](#cache)
* [Logowanie i X-Request-Id](#logowanie-i-x-request-id)
* [Konfiguracja i środowisko](#konfiguracja-i-środowisko)
* [Uruchomienie - backend](#uruchomienie--backend)
* [Uruchomienie - frontend](#uruchomienie--frontend)
* [Jakość: Pint / PHPStan / Pest / CI](#jakość-pint--phpstan--pest--ci)
* [Struktura katalogów (skrócona)](#struktura-katalogów-skrócona)
* [Roadmapa (skrót)](#roadmapa-skrót)
* [FAQ / Troubleshooting](#faq--troubleshooting)

---

## Funkcje

* Wyszukiwanie po: **świadczenie (`q`)**, **województwo (kod NFZ)**, **priorytet (stabilny/pilny)**, **dla dzieci**.
* Sortowanie: **„najszybciej”** (po dacie najbliższego terminu).
* **Presety dni (≤30/60/90)** jako *miękki filtr* - jeśli dla 30 dni brak wyników, API automatycznie luzuje do 60 → 90 → „all” i w `meta.filters` zwraca, co zastosowano.
* Odpowiedź API zawiera `meta`:

  * `count` - liczba wyników,
  * `lastUpdated` - najświeższa data aktualizacji z NFZ (max z rekordów),
  * *(opcjonalnie)* `filters` - `requested/applied/relaxation` dla presetów dni.
* **Cache wyników** (domyślnie 15 min) — działa dla Fake i HTTP klienta.
* **Pro-level logging:** globalny **X-Request-Id** w nagłówkach i logach (JSON).
* **Frontend (SPA):** formularz + lista wyników, `meta` i komunikaty walidacji.

---

## Architektura – skrót

**Warstwa domenowa (`app/`):**

* **DTO:** `App\Domain\DTO\{ProviderDTO, AppointmentDTO, SearchResultDTO}` - spójne, typowane obiekty przenoszące dane.
* **Value objects:** `App\Domain\Value\{Priority(enum), SearchParams}`.
* **Kontrakt danych:** `App\Contracts\ItlClient::search(SearchParams): array<SearchResultDTO>`.

**Implementacje klienta ITL:**

* `App\Infrastructure\Fake\FakeItlClient` - dane przykładowe (dev/test).
* `App\Infrastructure\Http\HttpItlClient` - realne wywołania `/queues` (timeouts, retry, mapowanie pól).
* `App\Infrastructure\Cache\CachedItlClient` - dekorator cache’ujący `ItlClient`.

**Logika:**

* `App\Application\Search\SearchService` - filtruje (dla dzieci, `maxDays`), sortuje (`fastest`), obsługuje *miękką relaksację* presetów dni.

**API:**

* `App\Http\Controllers\Api\SearchController::__invoke` - orkiestracja wyszukiwania, budowa `meta`.
* `App\Http\Requests\SearchRequest` - walidacja i sanityzacja (czyści puste wartości).
* `App\Http\Resources\SearchResultResource` - kształt JSON.

**Bindowanie (DI) — `App\Providers\AppServiceProvider`:**

* `ItlClient → CachedItlClient( FakeItlClient | HttpItlClient )` wg zmiennej `ITL_DRIVER`.

---

## API

**Endpoint:** `GET /api/search`

**Parametry (query):**

| Parametr   | Typ / wartości              | Wymagany | Opis                                                            |
| ---------- | --------------------------- | -------: | --------------------------------------------------------------- |
| `q`        | `string`                    |      tak | Nazwa świadczenia / specjalizacja (np. `kardiolog`).            |
| `province` | `string` (kod NFZ, 2 znaki) |      tak | Np. `12` = Mazowieckie.                                         |
| `priority` | `stable` \| `urgent`        |      tak | Zgodnie z listami oczekujących.                                 |
| `kids`     | `bool`                      |      nie | Filtr „dla dzieci”.                                             |
| `maxDays`  | `int`                       |      nie | **Twardy** limit dni (wyłącza relaksację).                      |
| `days`     | `30` \| `60` \| `90`        |      nie | **Miękki preset:** przy braku wyników dla 30 → 60 → 90 → „all”. |
| `sort`     | `fastest`                   |      nie | Domyślnie `fastest`.                                            |

**200 OK — przykład:**

```json
{
  "data": [
    {
      "provider": {
        "id": "umw-123",
        "name": "Przychodnia Alfa",
        "address": "Warszawa, ...",
        "phone": "+48 123 456 789",
        "website": null,
        "forChildren": true,
        "location": { "lat": 52.23, "lng": 21.01 }
      },
      "appointment": {
        "firstAvailableDate": "2025-09-05",
        "queueSize": 12,
        "priority": "STABLE",
        "lastUpdated": "2025-08-22T10:00:00+00:00"
      },
      "distanceKm": 0
    }
  ],
  "meta": {
    "count": 1,
    "lastUpdated": "2025-08-22T10:00:00+00:00",
    "filters": {
      "requestedMaxDays": 30,
      "appliedMaxDays": 60,
      "relaxation": ["30", "60"]
    }
  }
}
```

> `meta.filters` pojawia się **tylko wtedy**, gdy użyto `days` i **nie** podano twardego `maxDays`.

**Szybki healthcheck:** `GET /health` (Laravel health endpoint).

---

## Cache

* **Dekorator:** `App\Infrastructure\Cache\CachedItlClient`.
* **Klucz:** hash z `SearchParams` (`q`, `province`, `priority`, `kids`, `maxDays`).

  > Uwaga: `sort` **nie wpływa na cache** — sortowanie wykonuje `SearchService`.
* **TTL:** `ITL_CACHE_TTL` (sekundy), **domyślnie 900** (15 min).
* **Driver:** Laravelowy `CACHE_DRIVER` (dev: `file`, prod: `redis`).

---

## Logowanie i X-Request-Id

* **Middleware:** `App\Http\Middleware\RequestIdMiddleware`

  * przenosi lub nadaje `X-Request-Id`,
  * ustawia `request_id` w kontekście logów.
* Wszystkie odpowiedzi HTTP zawierają nagłówek **`X-Request-Id`**.
* Logi w formacie **JSON** (łatwe do parsowania/ELK).

---

## Konfiguracja i środowisko

**`.env` (istotne fragmenty):**

```dotenv
APP_ENV=local
APP_DEBUG=true

ITL_DRIVER=fake          # fake | http
ITL_CACHE_TTL=900

# CORS: pozwól na front w dev
FRONTEND_URL=http://localhost:5173

# Cache (opcjonalnie Redis)
CACHE_DRIVER=file
```

**CORS — `config/cors.php`:**

* `allowed_origins` ustaw na `FRONTEND_URL` (w dev `http://localhost:5173`).
* Middleware `HandleCors` dodany globalnie w `bootstrap/app.php`.

**Klient HTTP (NFZ):**

* `App\Infrastructure\Http\HttpItlClient` - mapowanie pól z `/queues` → DTO.
* Timeouty, retry (exponential backoff), defensywne parsowanie dat/liczb/flag Y/N.

---

## Uruchomienie – backend

```bash
# instalacja zależności
composer install

# czyszczenie cache configu/route/view
php artisan optimize:clear

# start serwera dev
php artisan serve
# => http://localhost:8000
```

---

## Uruchomienie – frontend

W katalogu `frontend/`:

```bash
# zależności
npm i

# UWAGA: Tailwind v3 (stabilny CLI)
npm remove tailwindcss
npm i -D tailwindcss@3 postcss autoprefixer
npx tailwindcss init -p

# dev
npm run dev
# => http://localhost:5173
```

**Konfiguracja API — `frontend/.env`:**

```dotenv
VITE_API_URL=http://localhost:8000
```

---

## Jakość: Pint / PHPStan / Pest / CI

**Linter (Pint):**

```bash
vendor/bin/pint
```

**Static analysis (PHPStan):**

```bash
php -d memory_limit=512M vendor/bin/phpstan analyse --no-progress --ansi
```

**Testy (Pest + PHPUnit):**

```bash
php artisan test
# lub
vendor/bin/pest
```

**CI (GitHub Actions):**
Workflow `ci.yml`: `composer install → pint --test → phpstan (z limitem pamięci) → php artisan test`.

---

## Struktura katalogów (skrócona)

```
app/
 ├─ Application/Search/SearchService.php
 ├─ Contracts/ItlClient.php
 ├─ Domain/
 │   ├─ DTO/{ProviderDTO, AppointmentDTO, SearchResultDTO}.php
 │   └─ Value/{Priority.php, SearchParams.php}
 ├─ Http/
 │   ├─ Controllers/Api/SearchController.php
 │   ├─ Middleware/RequestIdMiddleware.php
 │   ├─ Requests/SearchRequest.php
 │   └─ Resources/SearchResultResource.php
 ├─ Infrastructure/
 │   ├─ Fake/FakeItlClient.php
 │   ├─ Http/HttpItlClient.php
 │   └─ Cache/CachedItlClient.php
 └─ Providers/AppServiceProvider.php

bootstrap/app.php          # CORS + RequestId middleware
config/cors.php            # konfiguracja CORS
routes/api.php             # GET /api/search
frontend/                  # SPA (React + Vite + TS + Tailwind 3)
```

---

## Roadmapa (skrót)

* **v0.1 (jest):** wyszukiwarka, lista wyników, `meta`, miękkie dni, cache, X-Request-Id, testy, CI.
* **v0.2:** sort „najbliżej” (Haversine + geolokacja), paginacja (`page/limit`), zapis ostatnich wyszukiwań (localStorage).
* **v0.3:** karta placówki (szczegóły, link IKP/telefon, mapa), PDF „notatka do wizyty”.
* **v0.4:** powiadomienia o nowych terminach (email/Push), Docker Compose (nginx+php-fpm+redis).

---

## FAQ / Troubleshooting


**Tailwind nie działa?**
Używamy **Tailwind 3** (stabilne CLI). Po instalacji `@3` uruchom `npx tailwindcss init -p`.
W `tailwind.config.js` `content` musi obejmować `./src/**/*.{ts,tsx}`.

**Cache nie działa?**
Sprawdź `.env` (`CACHE_DRIVER`, `ITL_CACHE_TTL`).
Pamiętaj, że cache dotyczy **wyników ITL** (niezależnie od `sort`).

**Przełączenie źródła danych (Fake/HTTP)?**
Ustaw `ITL_DRIVER=fake` (szybko w dev) lub `http` (realne API NFZ).