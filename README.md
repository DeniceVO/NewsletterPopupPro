# Newsletter Popup Pro

**Zaawansowana wtyczka WordPress do subskrypcji newslettera z inteligentnym popup-em**

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 📋 Spis treści

- [Opis](#opis)
- [Funkcjonalności](#-funkcjonalności)
- [Instalacja](#-instalacja)
- [Konfiguracja](#-konfiguracja)
- [Struktura plików](#-struktura-plików)
- [Wymagania](#-wymagania)

## 📝 Opis

Newsletter Popup Pro to zaawansowana wtyczka WordPress, która umożliwia zbieranie adresów email poprzez inteligentny popup. Wtyczka oferuje szereg opcji konfiguracyjnych, które pozwalają na dostosowanie zachowania popup-a do potrzeb każdej witryny.

### ✨ Kluczowe zalety:

- **🎯 Inteligentne wyświetlanie** - popup pojawia się w odpowiednim momencie
- **📱 Responsywność** - opcja wyłączenia na urządzeniach mobilnych
- **⚡ Szybkie ukrywanie** - popup znika po wpisaniu prawidłowego emaila
- **🔧 Pełna konfiguracja** - admin ma kontrolę nad każdym aspektem
- **♿ Dostępność** - zgodność z WCAG 2.1
- **🚀 Wydajność** - minimalne obciążenie strony

## 🎯 Funkcjonalności

### Podstawowe funkcje:
- ✅ Popup subskrypcji newslettera z formularzem email
- ✅ Zarządzanie bazą subskrybentów w panelu WordPress
- ✅ Eksport subskrybentów do pliku CSV
- ✅ Import masowy z plików CSV
- ✅ Walidacja adresów email z weryfikacją DNS
- ✅ Zabezpieczenia CSRF i sanityzacja danych

### Zaawansowane opcje:
- ⏰ **Konfigurowalne opóźnienie** - ustaw kiedy popup ma się pojawić (0-300s)
- ⏳ **Automatyczne ukrywanie** - popup znika po określonym czasie
- 💌 **Ukrywanie po emailu** - popup znika natychmiast po wpisaniu prawidłowego adresu
- 📍 **Wybór stron** - wyświetlaj popup tylko na wybranych typach stron
- 📱 **Kontrola urządzeń** - wyłącz popup na telefonach i tabletach
- 🍪 **Zarządzanie ciasteczkami** - inteligentne pamiętanie preferencji użytkownika

### Panel administracyjny:
- 📊 Lista wszystkich subskrybentów z paginacją
- 🗑️ Usuwanie subskrybentów
- 📈 Statystyki subskrypcji
- ⚙️ Zaawansowany panel ustawień z podglądem na żywo
- 💡 Podpowiedzi kontekstowe i walidacja

## 🚀 Instalacja

### Automatyczna instalacja:
1. Przejdź do **Wtyczki → Dodaj nową** w panelu WordPress
2. Wyszukaj "Newsletter Popup Pro"
3. Kliknij **Zainstaluj teraz**
4. Aktywuj wtyczkę

### Ręczna instalacja:
1. Pobierz pliki wtyczki
2. Umieść folder `newsletter-popup-pro` w katalogu `/wp-content/plugins/`
3. Przejdź do **Wtyczki** w panelu WordPress
4. Aktywuj **Newsletter Popup Pro**

### Po instalacji:
1. Przejdź do **Newsletter** w menu administracyjnym
2. Skonfiguruj ustawienia w zakładce **Ustawienia**
3. Sprawdź działanie popup-a na froncie witryny

## ⚙️ Konfiguracja

### Podstawowe ustawienia:

#### Opóźnienie wyświetlenia
```
Wartość: 0-300 sekund
Domyślnie: 5 sekund
Opis: Czas po załadowaniu strony, po którym pojawi się popup
```

#### Maksymalny czas wyświetlania
```
Wartość: 0-3600 sekund (0 = bez limitu)
Domyślnie: 0 (bez limitu)
Opis: Po tym czasie popup automatycznie zniknie
```

#### Ukrywanie po wpisaniu emaila
```
Wartość: Tak/Nie
Domyślnie: Tak
Opis: Popup znika natychmiast po wpisaniu prawidłowego adresu email
```

#### Strony do wyświetlania
- **Wszystkie strony** - popup pojawia się wszędzie
- **Tylko strona główna** - tylko na stronie głównej
- **Tylko wpisy** - tylko na pojedynczych wpisach
- **Tylko strony** - tylko na stronach statycznych

#### Urządzenia mobilne
```
Opcja: Wyłącz na urządzeniach mobilnych
Opis: Popup nie będzie się pokazywał na telefonach i tabletach
```

### Zaawansowana konfiguracja:

#### Dostosowanie wyglądu (CSS)
```css
/* Własne style popup-a */
.npp-popup {
    max-width: 500px; /* Zmiana szerokości */
}

.npp-popup h2 {
    color: #...; /* Kolor nagłówka */
}
```

## 📁 Struktura plików

```
newsletter-popup-pro/
├── newsletter-popup-pro.php          # Główny plik wtyczki
├── README.md                          # Ten plik
├── includes/                          # Klasy PHP
│   ├── Admin/                         # Panel administracyjny
│   │   ├── MenuManager.php           # Zarządzanie menu
│   │   └── SettingsManager.php       # Ustawienia wtyczki
│   ├── Ajax/                          # Obsługa AJAX
│   │   └── SubscriptionHandler.php   # Przetwarzanie subskrypcji
│   ├── Core/                          # Podstawowe klasy
│   │   ├── AssetsManager.php         # Zarządzanie zasobami
│   │   └── Database.php              # Operacje bazy danych
│   ├── Frontend/                      # Interfejs użytkownika
│   │   └── PopupRenderer.php         # Renderowanie popup-a
│   ├── Models/                        # Modele danych
│   │   └── Subscriber.php            # Model subskrybenta
│   ├── Repositories/                  # Dostęp do danych
│   │   └── SubscriberRepository.php  # Repozytorium subskrybentów
│   └── Services/                      # Serwisy pomocnicze
│       ├── BulkImporter.php          # Import masowy
│       ├── CookieManager.php         # Zarządzanie ciasteczkami
│       ├── CSVExporter.php           # Eksport CSV
│       ├── EmailValidator.php        # Walidacja emaili
│       └── PopupDisplayManager.php   # Logika wyświetlania
└── assets/                            # Zasoby statyczne
    ├── css/
    │   ├── frontend.css              # Style popup-a
    │   └── admin.css                 # Style panelu admin
    └── js/
        ├── frontend.js               # JavaScript popup-a
        └── admin-settings.js         # JavaScript ustawień
```

## 🛠️ API dla deweloperów

### JavaScript API

```javascript
// Wymuś pokazanie popup-a
NPP.show();

// Wymuś ukrycie popup-a
NPP.hide();

// Zmień konfigurację w czasie rzeczywistym
NPP.config({
    delay: 2000,        // 2 sekundy opóźnienia
    hideAfterEmail: false // Wyłącz ukrywanie po emailu
});

// Nasłuchuj eventów
$(document).on('npp_popup_shown', function() {
    console.log('Popup został pokazany');
});

$(document).on('npp_popup_closed', function() {
    console.log('Popup został zamknięty');
});
```

## 📋 Wymagania

### Minimalne wymagania:
- **WordPress:** 5.0 lub nowszy
- **PHP:** 7.4 lub nowszy
- **MySQL:** 5.6 lub nowszy
- **JavaScript:** Włączony w przeglądarce

### Zalecane:
- **WordPress:** 6.0+
- **PHP:** 8.0+
- **MySQL:** 8.0+
- **HTTPS:** Dla lepszego bezpieczeństwa
