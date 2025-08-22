### NFZ Terminy — Laravel 11 / PHP 8.3

Aplikacja (MVP) do szybkiego wyszukiwania najszybszych terminów świadczeń w NFZ z publicznych źródeł (ITL – Terminy Leczenia, UMW – Umowy).
---

#### Dlaczego?
Informacja o pierwszych wolnych terminach NFZ jest publiczna, ale rozproszona i mało przyjazna mobilnie. Celem jest „gdzie najszybciej” + przejście do e-rejestracji IKP tam, gdzie to możliwe. 
---

#### Funkcje
 - Wyszukiwarka świadczeń: tekst („Jaki problem/świadczenie?”), lokalizacja (województwo/miasto/promień), priorytet (stabilny/pilny), przełącznik „dla dzieci”.
 -  Lista placówek: pierwszy wolny termin, liczba oczekujących, tryb, dystans, kontakt.
 - Karta placówki: „Jak się umówić?”, „Najbliższe terminy”, „Zakresy w NFZ”, link do IKP (jeśli dotyczy).
.
---
 #### Stos technologiczny
Backend: Laravel 11 (PHP 8.3)
Testy: Pest (nad PHPUnit 10)
Code-quality: Laravel Pint (formatter), Larastan (larastan/larastan, analiza statyczna), Rector (automatyczny refactor pod PHP 8.3)
Logi: Monolog w formacie JSON + globalny X-Request-Id (middleware)

Szybki start
# 1) zależności
composer install

# 2) klucz aplikacji
php artisan key:generate

# 3) dev server
php artisan serve

Sprawdź healthcheck: http://127.0.0.1:8000/health → powinno zwrócić ok.

---

