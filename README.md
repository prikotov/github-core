# GitHub Skills для AI-агентов

Набор CLI-инструментов для получения статистики из GitHub API. Работают с любыми AI-агентами: opencode, Cursor, Claude, GPT и др.

## Установка

### 1. Клонирование

```bash
git clone https://github.com/prikotov/github-core.git
git clone https://github.com/prikotov/github-traffic.git
git clone https://github.com/prikotov/github-referrers.git
git clone https://github.com/prikotov/github-paths.git
git clone https://github.com/prikotov/github-stars.git
```

### 2. Конфигурация

Создайте `config.json` в папке `github-core`:

```json
{
  "token": "ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "default_repo": "username/repository",
  "repos": {
    "my-project": "username/my-project",
    "another-repo": "username/another-repo"
  }
}
```

### 3. GitHub Token

1. https://github.com/settings/tokens
2. "Generate new token (classic)"
3. Права: `public_repo` или `repo`

### 4. Требования

- PHP 7.4+ с cURL
- Добавьте `config.json` в `.gitignore`

## Доступные команды

### github-traffic
Просмотры и клоны репозиториев за 14 дней.

```bash
php github-traffic/traffic.php
php github-traffic/traffic.php --repo my-project
php github-traffic/traffic.php -t views -f summary
```

### github-referrers
Источники трафика и популярные пути.

```bash
php github-referrers/referrers.php
php github-referrers/referrers.php -t referrers -l 10
```

### github-paths
Популярные страницы репозитория.

```bash
php github-paths/paths.php
php github-paths/paths.php -l 50 --sort unique
```

### github-stars
Динамика звёзд и сравнение репозиториев.

```bash
php github-stars/stars.php
php github-stars/stars.php --days 60
php github-stars/stars.php --top 20
php github-stars/stars.php -c repo1,repo2
```

## Формат отчётов

Все отчёты сохраняются в `github_reports/YYYY-MM-DD/`:
- `.csv` — для обработки в таблицах
- `.md` — для просмотра

## Ограничения GitHub API

- Traffic API — только за последние 14 дней
- Rate limit: 5000 запросов/час для авторизованных запросов
- Popular paths/referrers — максимум 10 записей

## Сравнение с Яндекс.Метрикой

| Метрика | Яндекс.Метрика | GitHub |
|---------|---------------|--------|
| Глубина данных | До 3 лет | 14 дней |
| Уникальные посетители | ✅ | ✅ |
| Источники трафика | ✅ Детально | ✅ Рефереры |
| Популярные страницы | ✅ Без лимита | ⚠️ До 10 |
| Поисковые фразы | ✅ | ❌ |
