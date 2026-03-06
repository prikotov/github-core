# GitHub Skills для opencode

Набор skills для получения статистики из GitHub API.

## Установка

### 1. Создайте конфигурационный файл

Создайте файл `.opencode/skills/github-core/config.json`:

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

### 2. Получите GitHub Personal Access Token

1. Перейдите на https://github.com/settings/tokens
2. Нажмите "Generate new token (classic)"
3. Выберите права:
   - `public_repo` — для публичных репозиториев
   - `repo` — для приватных репозиториев
4. Скопируйте токен в `config.json`

### 3. Добавьте в .gitignore

```gitignore
.opencode/skills/github-core/config.json
```

## Доступные skills

### github-traffic
Просмотры и клоны репозиториев за 14 дней.

```bash
php .opencode/skills/github-traffic/traffic.php
php .opencode/skills/github-traffic/traffic.php --repo my-project
php .opencode/skills/github-traffic/traffic.php -t views -f summary
```

### github-referrers
Источники трафика и популярные пути.

```bash
php .opencode/skills/github-referrers/referrers.php
php .opencode/skills/github-referrers/referrers.php -t referrers -l 10
```

### github-paths
Популярные страницы репозитория.

```bash
php .opencode/skills/github-paths/paths.php
php .opencode/skills/github-paths/paths.php -l 50 --sort unique
```

### github-stars
Динамика звёзд и сравнение репозиториев.

```bash
php .opencode/skills/github-stars/stars.php
php .opencode/skills/github-stars/stars.php --days 60
php .opencode/skills/github-stars/stars.php --top 20
php .opencode/skills/github-stars/stars.php -c repo1,repo2
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
