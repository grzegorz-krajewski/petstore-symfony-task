# Petstore Symfony Task

Zadanie rekrutacyjne polegające na przygotowaniu prostej aplikacji w Symfony, komunikującej się z zewnętrznym REST API Petstore.

## Opis projektu

Aplikacja umożliwia obsługę zasobu pet z wykorzystaniem publicznego API Petstore.
Zakres rozwiązania obejmuje podstawowe operacje CRUD, prosty interfejs formularzy oraz obsługę błędów po stronie użytkownika.

Frontend został celowo ograniczony do prostej formy, ponieważ główny nacisk w zadaniu położony jest na backend, integrację z API i logikę aplikacji.

## Zakres funkcjonalny

Aplikacja umożliwia:

- dodawanie zwierzaka,
- pobieranie zwierzaka po ID,
- edycję zwierzaka,
- usuwanie zwierzaka,
- wyświetlanie listy rekordów po statusie,
- upload obrazu dla istniejącego zwierzaka,
- obsługę podstawowych błędów API.

## Dodatkowe elementy

W aplikacji zostały dodatkowo uwzględnione:

- wybór kategorii na podstawie stałej listy,
- wybór tagów na podstawie stałej listy,
- ochrona CSRF przy usuwaniu,
- podstawowe testy mapowania danych,
- statyczna analiza kodu przy użyciu PHPStan,
- workflow CI w GitHub Actions uruchamiany dla push i pull requestów.

## Technologie

- PHP 8.4+
- Symfony 8
- Twig
- Symfony Form
- Symfony Validator
- Symfony HttpClient
- PHPUnit
- PHPStan
- GitHub Actions

## Uruchomienie projektu

### Wymagania

- PHP 8.4 lub nowsze
- Composer

### Instalacja

    git clone https://github.com/grzegorz-krajewski/petstore-symfony-task.git
    cd petstore-symfony-task
    composer install

### Konfiguracja

W pliku .env należy ustawić:

    PETSTORE_API_BASE_URL=https://petstore.swagger.io/v2

### Uruchomienie aplikacji

Przykładowo:

    symfony server:start

albo:

    php -S 127.0.0.1:8000 -t public

## Uruchomienie testów

    php bin/phpunit

## Uruchomienie analizy statycznej

    vendor/bin/phpstan analyse

## Struktura rozwiązania

Najważniejsze elementy projektu:

- PetController – obsługa flow aplikacji,
- PetstoreClient – komunikacja z API Petstore,
- PetData, CategoryData, TagData – warstwa mapowania danych,
- PetType – formularz create/edit,
- PetImageUploadType – formularz uploadu obrazu.

## Obsługa zdjęć

Dodawanie zdjęć zostało przeniesione na ekran szczegółów zwierzaka.

Powód:
endpoint uploadu obrazu wymaga istniejącego petId, dlatego zdjęcia mogą być dodawane dopiero po zapisaniu rekordu.

Po udanym uploadzie aplikacja aktualizuje listę photoUrls na podstawie ścieżki zwróconej w odpowiedzi API.

## Lista rekordów po statusie

Na stronie głównej dostępna jest lista rekordów filtrowana po statusie:
- available
- pending
- sold

Funkcja korzysta z endpointu findByStatus.

## Ograniczenia zewnętrznego API

Petstore jest publicznym demo API, dlatego zachowanie uploadu obrazów jest ograniczone względem rzeczywistego systemu przechowywania plików.

Aplikacja obsługuje upload obrazu i aktualizuje dane zwierzaka na podstawie odpowiedzi API, jednak zwracana wartość może być ścieżką techniczną, a nie pełnym publicznym adresem URL.

## Obsługa błędów

W aplikacji zostały obsłużone m.in.:

- nieprawidłowe ID,
- brak rekordu,
- błędy komunikacji z API,
- błędy zapisu, aktualizacji i usuwania,
- nieprawidłowy token bezpieczeństwa przy usuwaniu.

## CI

Projekt zawiera prosty workflow CI w GitHub Actions, który uruchamia:
- testy PHPUnit,
- analizę statyczną PHPStan.

Workflow uruchamia się automatycznie dla push oraz pull requestów do gałęzi main.

## Orientacyjny czas wykonania

Orientacyjny czas wykonania zadania: **około 8 godzin**.