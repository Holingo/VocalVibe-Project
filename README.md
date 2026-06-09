# VocalVibe — System Rezerwacji Sal Karaoke i Obsługi Zamówień Barowych

Aplikacja internetowa dedykowana dla nowoczesnych lokali rozrywkowych oferujących prywatne sale karaoke. System umożliwia klientom asynchroniczną rezerwację boksów muzycznych, konfigurację liczby gości oraz zamawianie produktów gastronomiczno-barowych w czasie rzeczywistym. Platforma udostępnia ponadto dedykowany panel zarządczy (Manager Dashboard) do monitorowania obłożenia lokalu, analizy finansowej i kontroli statusu czystości sal.

Projekt został zrealizowany w architekturze **MVC (Model-View-Controller)** zgodnie z paradygmatami czystego kodu (Clean Code), pełnego bezpieczeństwa sesji oraz pełnej konteneryzacji środowiska przy użyciu **Docker**.

---

## 📌 Spis Treści
1. [Główne Funkcjonalności](#-główne-funkcjonalności)
2. [Architektura Systemu i Technologie](#-architektura-systemu-i-technologie)
3. [Struktura i Złożoność Bazy Danych (PostgreSQL)](#-struktura-i-złożoność-bazy-danych-postgresql)
4. [Bezpieczeństwo i Ochrona Danych](#-bezpieczeństwo-i-ochrona-danych)
5. [Instrukcja Uruchomienia (Docker & SSL)](#-instrukcja-uruchomienia-docker--ssl)
6. [Konta Testowe](#-konta-testowe)
7. [Diagram ERD i Interfejs Użytkownika](#-diagram-erd-i-interfejs-użytkownika)

---

## ⚡ Główne Funkcjonalności

Aplikacja obsługuje dwa odseparowane poziomy uprawnień (role użytkowników), zapewniając pełną izolację danych i akcji biznesowych:

### 👤 1. Panel Klienta (User Dashboard)
* **Rezerwacja Sal Karaoke (Book Now):** Dynamiczny interfejs wyboru boksów muzycznych. System automatycznie blokuje zajęte terminy na podstawie walidacji bazodanowej.
* **Kalkulator Opłat w Czasie Rzeczywistym:** Interaktywne przeliczanie ceny rezerwacji w zależności od stawki godzinowej danej sali, wybranego czasu trwania sesji oraz doliczanego podatku (8% VAT).
* **Wirtualny Koszyk Barowy:** Możliwość skompletowania zamówienia na przekąski i napoje (menu barowe) bezpośrednio podczas dokonywania rezerwacji lub w trakcie trwania aktywnej sesji boksowej.
* **Zarządzanie Rezerwacjami (My Bookings):** Przejrzysta oś czasu prezentująca historyczne oraz nadchodzące rezerwacje użytkownika wraz ze szczegółowym podsumowaniem zamówionych produktów.
* **Asynchroniczne Anulowanie:** Możliwość bezpiecznej rezygnacji z rezerwacji przed jej rozpoczęciem (wykorzystanie Fetch API).

### 📊 2. Panel Managera (Manager Dashboard)
* **Monitorowanie Obłożenia Lokalu:** Wizualizacja stanu wszystkich sal w czasie rzeczywistym z podziałem na statusy (sala wolna, zajęta, wymagająca sprzątania).
* **Statystyki Finansowe i Operacyjne:** Natychmiastowy podgląd sumarycznych przychodów lokalu, liczby aktywnych gości oraz procentowego obłożenia.
* **Podgląd Zamówień Barowych:** Wgląd w listy produktów zamówionych przez klientów do konkretnych boksów w celu sprawnej realizacji zamówień przez obsługę.

---

## 🏛️ Architektura Systemu i Technologie

Projekt cechuje się ścisłym rozdziałem warstw odpowiedzialności (Separation of Concerns):

* **Backend (PHP 8.x):** Napisany w pełni obiektowo. Wykorzystuje autorski mechanizm routingu (`Routing.php`), który parsuje adresy URL i mapuje je bezpośrednio na akcje odpowiednich kontrolerów. Klasy bazowe (`AppController`, `Repository`) standaryzują obsługę żądań, renderowanie widoków oraz bezpieczne połączenia PDO.
* **Database Access Layer (Repositories):** Komunikacja z bazą danych odseparowana od logiki kontrolerów. Zaimplementowane repozytoria (`UsersRepository`, `BookingRepository`, `RoomsRepository`, `OrderRepository`, `ProductsRepository`) chronią aplikację przed chaosem architektonicznym.
* **Frontend (HTML5, CSS3, JavaScript ES6):** Warstwa wizualna zaprojektowana w nowoczesnym stylu *Glassmorphic Dark Theme* z pełną responsywnością (**RWD** - pełne dostosowanie do smartfonów i tabletów).
* **Asynchroniczność (Fetch API / AJAX):** Kluczowe operacje aplikacji (pobieranie wolnych godzin, aktualizacja koszyka, usuwanie pozycji, anulowanie rezerwacji) odbywają się asynchronicznie bez przeładowania strony, komunikując się z backendem poprzez wymianę paczek danych w formacie JSON.

---

## 🗄️ Struktura i Złożoność Bazy Danych (PostgreSQL)

Relacyjna baza danych została w pełni znormalizowana i wyposażona w zaawansowaną logikę biznesową przeniesioną bezpośrednio na stronę silnika PostgreSQL (`init.sql`).

### 📊 Zaawansowane Obiekty Bazodanowe
1. **Widoki (Views):**
    * `occupied_rooms_status` — Agreguje informacje o aktualnie zajętych salach, danych klienta oraz czasie trwania sesji w celu zasilenia panelu managera.
    * `top_ordered_products` — Statystyczny widok ułatwiający managerowi analizę popularności produktów barowych (sortowanie po wolumenie zamówień).
2. **Funkcje (Stored Functions):**
    * `calculate_room_price(room_id, duration)` — Bezpiecznie kalkuluje koszt bazowy wynajmu boksów muzycznych.
    * `set_room_needs_cleaning()` — Funkcja pomocnicza zmieniająca flagę stanu pomieszczenia.
3. **Wyzwalacze (Triggers):**
    * `trigger_room_cleaning` — Automatyczny wyzwalacz uruchamiający się po zakończeniu lub anulowaniu rezerwacji. Zmienia on status sali w tabeli `rooms` na wymagający interwencji serwisu sprzątającego.
4. **Transakcje bazodanowe (ACID):**
    * Operacje zapisu rezerwacji oraz modyfikacji zamówień barowych objęte są ścisłymi transakcjami PDO (`beginTransaction`, `commit`, `rollBack`). Gwarantuje to spójność danych i eliminuje ryzyko powstania "rekordów sierocych".
5. **Akcje na referencjach:**
    * Wszystkie powiązania relacyjne kluczy obcych (Foreign Keys) zostały zabezpieczone klauzulą `ON DELETE CASCADE`, co zapewnia kaskadowe czyszczenie zależnych rekordów w przypadku usunięcia konta bądź sali.

---

## 🔒 Bezpieczeństwo i Ochrona Danych

Aplikacja implementuje zaawansowane mechanizmy ochrony zgodne ze współczesnymi standardami bezpieczeństwa webowego:

* **Szyfrowanie haseł:** Dane uwierzytelniające są chronione algorytmem kryptograficznym **BCRYPT** za pomocą systemowej funkcji `password_hash()`. Baza danych nigdy nie przetwarza ani nie przechowuje czystego tekstu haseł.
* **Ochrona przed SQL Injection:** Całkowite wyeliminowanie podatności poprzez konsekwentne stosowanie zapytań przygotowanych (**Prepared Statements**) oraz bindowania parametrów w PDO.
* **Bezpieczeństwo Sesji (Session Hijacking & Fixation):** * Po pomyślnym zalogowaniu wywoływana jest funkcja `session_regenerate_id(true)`, uniemożliwiająca przejęcie identyfikatora sesji.
    * Ciasteczka sesyjne konfigurowane są z flagami ochronnymi: `HttpOnly` (blokada dostępu z poziomu skryptów JS/XSS), `Secure` (transmisja wyłącznie przez HTTPS) oraz `SameSite=Strict` (ochrona przed atakami CSRF).
* **Ochrona przed Cross-Site Scripting (XSS):** Wszelkie dane wprowadzane przez użytkowników są filtrowane i neutralizowane w widokach za pomocą funkcji `htmlspecialchars()`.
* **Wymuszony protokół HTTPS (SSL):** Całość ruchu sieciowego przechodzi przez szyfrowany tunel TLS zarządzany przez serwer Nginx.

---

## 🚀 Instrukcja Uruchomienia (Docker & SSL)

Aplikacja jest w pełni skonteneryzowana. Jedyne wymaganie systemowe to zainstalowane środowisko **Docker** oraz **Docker Compose**.

### Krok 1: Klonowanie repozytorium i wejście do katalogu
```bash
git clone [https://github.com/Holingo/VocalVibe-Project.git](https://github.com/Holingo/VocalVibe-Project.git)
cd VocalVibe-Project